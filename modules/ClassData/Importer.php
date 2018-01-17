<?php

class Ccheckin_ClassData_Importer extends Ccheckin_ClassData_ImporterExtension
{
    const DATASOURCE_ALIAS = 'ccheckin';
    const ACCOUNTS_TABLE = 'ccheckin_users';
    const ENROLLMENTS_TABLE = 'ccheckin_enrollments';
    const COURSES_TABLE = 'ccheckin_courses';
    

    static $ClassDataAccountFieldMap = array(
        'Firstname' => 'firstName',
        'Lastname' => 'lastName',
        'SFSUid' => 'username',
        'Email' => 'emailAddress',
    );
    

    public static function getExtensionName () { return 'classdata'; }
    

    public function findAccount ($uniqueId)
    {
        $account = null;
        $dataSource = $this->getDataSource(self::DATASOURCE_ALIAS);
        $query = $dataSource->createSelectQuery(self::ACCOUNTS_TABLE);
        
        foreach (self::$ClassDataAccountFieldMap as $ccheckinField => $accountField)
        {
            $query->project($ccheckinField);
        }
        
        $condition = $dataSource->createCondition(
            $dataSource->createTypedValue('SFSUid', 'symbol'),
            Bss_DataSource_Condition::OP_EQUALS,
            $dataSource->createTypedValue($uniqueId, 'string')
        );
        
        $query->setCondition($condition);
        $rs = $query->execute(true);
        
        while ($rs->next())
        {
            $account = new stdClass;
            
            foreach (self::$ClassDataAccountFieldMap as $ccheckinField => $accountField)
            {
                $account->$accountField = $rs->getValue($ccheckinField, 'string');
            }
            
            break;
        }
        
        return $account;
    }

    
    public function findEnrollments ($accountId)
    {
        $enrollments = $this->schema('Ccheckin_ClassData_Enrollment');
        $enrollments->setDefaultDataSourceAlias(self::DATASOURCE_ALIAS);
        
        return $enrollments->find($enrollments->sfsuId->lower()->equals(strtolower($accountId)));
    }
    

    public function findEnrollmentsByCourses ($courseKeys)
    {
        $courseKeys = (array)$courseKeys;
        
        $enrollments = $this->schema('Ccheckin_ClassData_Enrollment');
        $enrollments->setDefaultDataSourceAlias(self::DATASOURCE_ALIAS);
        
        return $enrollments->find($enrollments->externalCourseKey->inList($courseKeys));
    }
    

    public function findCourses ($keys, $keyType = 'ssid')
    {
        $courses = $this->schema('Ccheckin_ClassData_Course');
        $courses->setDefaultDataSourceAlias(self::DATASOURCE_ALIAS);
        return $courses->find($courses->externalCourseKey->inList($keys));
    }

    public function findSemesterCourses ($semesterCode)
    {
        $courses = $this->schema('Ccheckin_ClassData_Course');
        $courses->setDefaultDataSourceAlias(self::DATASOURCE_ALIAS);
        return $courses->find($courses->externalCourseKey->like($semesterCode . '-%'));
    }
  
     
    public function import ($semester)
    {
        set_time_limit(0);
        $result = false;
        
        $schemaManager = $this->getApplication()->schemaManager;
        
        $logs = $schemaManager->getSchema('Ccheckin_ClassData_SyncLog');
        $logs->setDefaultDataSourceAlias(self::DATASOURCE_ALIAS);
        $lastLog = $logs->findOne($logs->status->equals(200), array('orderBy' => array('-dt', '-id')));
        $newLog = $logs->createInstance();
        
        $courses = $schemaManager->getSchema('Ccheckin_ClassData_Course');
        $courses->setDefaultDataSourceAlias(self::DATASOURCE_ALIAS);
        $users = $schemaManager->getSchema('Ccheckin_ClassData_User');
        $users->setDefaultDataSourceAlias(self::DATASOURCE_ALIAS);
        $enrollments = $schemaManager->getSchema('Ccheckin_ClassData_Enrollment');
        $enrollments->setDefaultDataSourceAlias(self::DATASOURCE_ALIAS);
        
        $dataSource = $courses->getDefaultDataSource();
        $tx = $dataSource->createTransaction();
        $now = new DateTime;
        
        if ($lastLog === null)
        {
            // TODO: We need a real strategy for this. Right now, just pick a date long ago.
            $since = '1970-01-01';
        }
        else
        {
            $since = $lastLog->dt->format('c');
        }

        $service = new Ccheckin_ClassData_Service($this->getApplication());
        
        list($status, $data) = $service->getChanges($semester, $since);
        
        if ($status != 200)
        {
            if ($data && isset($data['error']))
            {
                $newLog->errorCode = $data['error'];
                $newLog->errorMessage = $data['message'];
            }
            else
            {
                $newLog->errorCode = 'NoErrorResource';
                $newLog->errorMessage = 'The response contained an error code, but the body was not a JSON-formatted error document.';
            }
        }
        else
        {
            // Keeps track of existing courses and users as we process the batches.
            $existingCourseSet = $courses->findValues(array('externalCourseKey' => 'externalCourseKey'));
            $existingUserSet = $users->findValues(array('sfsuId' => 'sfsuId'));
            
            // Process the courses in batches.
            
            foreach ($this->batches($data['courses'], 1000) as $batch)
            {
                foreach ($batch as $courseId => $actionList)
                {
                    foreach ($actionList as $action)
                    {
                        if (array_key_exists($courseId, $existingCourseSet))
                        {
                            if ($action['t'] == '+' && $existingCourseSet[$courseId])
                            {
                                // If we're trying to add a course that was 
                                // previously marked as deleted, remove all of its
                                // old enrollments. (We kept them before so that we
                                // had a record of the course's instructors. But
                                // now we're expecting new enrollments for the
                                // course -- which might replicate the info we
                                // saved.)
                                
                                $this->deleteCourseEnrollments($tx, $enrollments, $courseId);
                            }
                            
                            if ($action['t'] == '+' || $action['t'] == '!')
                            {
                                $this->updateCourse($tx, $now, $courses, $courseId, $action['d']);
                            }
                            elseif ($action['t'] == '-')
                            {
                                $this->dropCourse($tx, $now, $courses, $courseId);
                                $existingCourseSet[$courseId] = true; // Mark as deleted.
                            }
                        }
                        elseif ($action['t'] == '+' || $action['t'] == '!')
                        {
                            $this->addCourse($tx, $now, $courses, $courseId, $action['d']);
                            $existingCourseSet[$courseId] = false;
                        }
                    }
                }
            }
            
            foreach ($this->batches($data['users'], 1000) as $idx => $batch)
            {
                foreach ($batch as $userId => $actionList)
                {
                    foreach ($actionList as $action)
                    {
                        if (array_key_exists($userId, $existingUserSet))
                        {
                            switch ($action['t'])
                            {
                                case '+':
                                case '!':
                                    $this->updateUser($tx, $now, $users, $userId, $action['d']);
                                    break;
                                case '-':
                                    $this->dropUser($tx, $now, $users, $userId);
                                    unset($existingUserSet[(string)$userId]);
                                    break;
                            }
                        }
                        elseif ($action['t'] == '+' || $action['t'] == '!')
                        {
                            $this->addUser($tx, $now, $users, $userId, $action['d']);
                            $existingUserSet[(string)$userId] = true;
                        }
                    }
                }
            }
            
            $existingEnrollmentSet = $this->loadExistingEnrollments($dataSource, $data['enrollments'], $existingCourseSet, $existingUserSet);
            
            // Enrollments.
            foreach ($data['enrollments'] as $courseId => $courseEnrollList)
            {
                if (array_key_exists($courseId, $existingCourseSet))
                {
                    foreach ($courseEnrollList as $action)
                    {
                        $role = ($action[1] == 's' ? 'student' : 'instructor');
                        $userId = substr($action, 2);
                        
                        if (isset($existingUserSet[(string)$userId]))
                        {
                            switch ($action[0])
                            {
                                case '+':
                                    if (!isset($existingEnrollmentSet[$courseId]) || !isset($existingEnrollmentSet[$courseId][$userId]))
                                    {
                                        $this->addEnrollment($tx, $now, $enrollments, $userId, $courseId, $role);
                                    }
                                    break;
                                case '-':
                                    $this->dropEnrollment($tx, $now, $enrollments, $userId, $courseId, $role);
                                    break;
                            }
                        }
                        else
                        {
                            $this->getApplication()->log('debug', "Enrollment {$action} in {$courseId} for non-existent user: {$userId}");
                        }
                    }
                }
                else
                {
                    $this->getApplication()->log('debug', "Enrollment for non-existent course: {$courseId}");
                }
            }
        }
        
        $newLog->dt = $now;
        $newLog->status = $status;
        $newLog->save();
        
        $tx->commit();
        return $now;
    }
    
    
    protected function batches ($data, $entries)
    {
        $count = count($data);
        $batches = array();
        
        for ($i = 0; $i < $count; $i += $entries)
        {
            $batches[] = array_slice($data, $i, $entries, true);
        }
        
        return $batches;
    }
    
    
    protected function deleteCourseEnrollments ($tx, $enrollments, $courseId)
    {
        $enrollments->delete($enrollments->externalCourseKey->equals($courseId), array('transaction' => $tx));
    }
    
    
    /**
     * Flags a course as having been dropped. We actually keep it around in the
     * cache because we might have resources associated with it in DIVA and we
     * want to keep that stuff.
     */
    protected function dropCourse ($tx, $now, $courses, $courseId)
    {
        $courses->delete($courses->externalCourseKey->equals($courseId), array('transaction' => $tx));
    }
    
    
    /**
     * Add a course to the cache.
     */
    protected function addCourse ($tx, $now, $courses, $courseId, $data)
    {
        if (empty($data['sn']) || empty($data['title']))
        {
            $missing = array();
            if (empty($data['sn'])) $missing[] = 'sn';
            if (empty($data['title'])) $missing[] = 'title';
            $this->getApplication()->log('warning', "Skipping add for course {$courseId}: Missing required field" . (count($missing) > 1 ? 's' : '') . ': ' . implode(', ', $missing));
            return;
        }
        
        $courses->insert(
            array(
                'externalCourseKey' => $courseId,
                'courseId' => $data['sn'],
                'courseName' => $data['title'],
            ),
            array('transaction' => $tx)
        );
    }
    
    
    /**
     */
    protected function updateCourse ($tx, $now, $courses, $courseId, $data)
    {
        if (empty($data['sn']) || empty($data['title']))
        {
            $missing = array();
            if (empty($data['sn'])) $missing[] = 'sn';
            if (empty($data['title'])) $missing[] = 'title';
            $this->getApplication()->log('warning', "Skipping update for course {$courseId}: Missing required field" . (count($missing) > 1 ? 's' : '') . ': ' . implode(', ', $missing));
            return;
        }
        
        $courses->update(
            array(
                'externalCourseKey' => $courseId,
                'courseId' => $data['sn'],
                'courseName' => $data['title'],
            ),
            $courses->externalCourseKey->equals($courseId),
            array('transaction' => $tx)
        );
    }
    
    
    /**
     */
    protected function dropUser ($tx, $now, $users, $userId)
    {
        $enrollments->delete($enrollments->sfsuId->equals($userId), array('transaction' => $tx));
        $users->delete($users->sfsuId->equals($userId), array('transaction' => $tx));
    }
    
    
    /**
     */
    protected function addUser ($tx, $now, $users, $userId, $data)
    {
        $users->insert(
            array(
                'sfsuId' => $userId,
                'firstName' => $data['first'],
                'lastName' => $data['last'],
                'email' => $data['mail'],
            ),
            array('transaction' => $tx)
        );
    }
    
    
    /**
     */
    protected function updateUser ($tx, $now, $users, $userId, $data)
    {
        $users->update(
            array(
                'firstName' => $data['first'],
                'lastName' => $data['last'],
                'email' => $data['mail'],
            ),
            $users->sfsuId->equals($userId),
            array('transaction' => $tx)
        );
    }
    
    
    /**
     */
    protected function addEnrollment ($tx, $now, $enrollments, $userId, $courseId, $role)
    {
        $enrollments->insert(
            array(
                'sfsuId' => $userId,
                'externalCourseKey' => $courseId,
                'role' => $role,
            ),
            array('transaction' => $tx)
        );
    }
    
    
    /**
     */
    protected function dropEnrollment ($tx, $now, $enrollments, $userId, $courseId, $role)
    {
        $enrollments->delete(
            $enrollments->allTrue(
                $enrollments->sfsuId->equals($userId),
                $enrollments->externalCourseKey->equals($courseId),
                $enrollments->role->equals($role)
            ),
            array('transaction' => $tx)
        );
    }
    
    
    /**
     */
    protected function loadExistingEnrollments ($dataSource, $enrollments, $existingCourseSet, $existingUserSet)
    {
        $enrollCourseSet = array();
        $enrollUserSet = array();
        
        foreach ($enrollments as $courseId => $courseEnrollList)
        {
            if (array_key_exists($courseId, $existingCourseSet))
            {
                $enrollCourseSet[$courseId] = true;
                foreach ($courseEnrollList as $action)
                {
                    $userId = substr($action, (($action[0] === '+' || $action[0] === '-') ? 2 : 1));
                    $enrollUserSet[$userId] = true;
                }
            }
        }
        
        // There are no enrollments to check?
        if (empty($enrollCourseSet) || empty($enrollUserSet))
        {
            return array();
        }
        
        $existingEnrollmentSet = array();
        
        $query = $dataSource->createSelectQuery('ccheckin_enrollments');
        $query->project('External_Course_Key');
        $query->project('SFSUid');
        $query->setCondition($dataSource->andConditions(array(
            $dataSource->createCondition(
                $dataSource->createSymbol('External_Course_Key'),
                Bss_DataSource_Condition::OP_IN,
                $dataSource->createTypedValue(array_keys($enrollCourseSet), 'string')
            ),
            $dataSource->createCondition(
                $dataSource->createSymbol('SFSUid'),
                Bss_DataSource_Condition::OP_IN,
                $dataSource->createTypedValue(array_keys($enrollUserSet), 'string')
            ),
        )));
        $query->orderBy('External_Course_Key', SORT_ASC);
        $query->orderBy('SFSUid', SORT_ASC);
        $rs = $query->execute();
        
        while ($rs->next())
        {
            $courseId = $rs->getValue('External_Course_Key', 'string');
            $userId = $rs->getValue('SFSUid', 'string');
            
            if (!isset($existingEnrollmentSet[$courseId]))
            {
                $existingEnrollmentSet[$courseId] = array();
            }
            
            $existingEnrollmentSet[$courseId][$userId] = true;
        }
        
        return $existingEnrollmentSet;
    }
}