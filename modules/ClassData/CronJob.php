<?php

/**
 * TODO: Remove the EarlyStart stuff and translate it to Childrens Campus semesters.
 * 
 * @author  Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Ccheckin_ClassData_CronJob extends Bss_Cron_Job
{
    const PROCESS_ACTIVE_JOBS_EVERY = 0; // 2 minutes
    
    public function run ($startTime, $lastRun, $timeDelta)
    {
        if ($timeDelta >= self::PROCESS_ACTIVE_JOBS_EVERY)
        {
            $semesters = $this->schema('Ccheckin_Semesters_Semester');
            $app = $this->getApplication();

            $semesterCode = $app->semesterManager->getCurrentSemesterCode();
            if (!($semester = $semesters->findOne($semesters->code->equals($semesterCode))))
            {
                $semester = $app->semesterManager->newChildCampusSemester($semesterCode);
            }

            $importer = $app->moduleManager->getExtensionByName('at:ccheckin:profiles/importer', 'classdata');
            $importer->import($semester->code);
            $this->syncChildCampusCourses($importer);
            $this->syncChildCampusEnrollments($importer);

            return true;
        }
    }

    private function syncChildCampusCourses ($importer)
    {
        $app = $this->getApplication();
        $semesters = $this->schema('Ccheckin_Semesters_Semester');

        $prefix = $this->getApplication()->configuration->earlyStart["courseNamePrefix"];
        $currentSemesterCode = $app->semesterManager->getCurrentSemesterCode();
        $semester = $semesters->findOne($semesters->code->equals($currentSemesterCode));

        // get SIMS courses based on semester & EarlyStart prefix 
        $classdataCourses = array();
        foreach ($importer->findSemesterCourses($currentSemesterCode) as $course)     // current semester
        {
            if (strncasecmp($course->courseId, $prefix, strlen($prefix)) == 0) // ESE-0099 prefix only courses
            {
                $classdataCourses[$course->externalCourseKey] = $course;
            }
        }      
        
        $currentSemesterCourses = array();      
        $changes = array('adds' => 0, 'removals' => 0);  

        // remove from semester -- CCheckIn courses that aren't in SIMS
        foreach ($semester->courses as $course)
        {
            if (!isset($classdataCourses[$course->courseKey]))
            {
                $changes['removals']++;
                $semester->courses->remove($course);
            }
            else
            {
                $currentSemesterCourses[$course->courseKey] = true;
            }     

            $app->log('debug', "Course {$course->name}:\t{$changes['adds']}\t{$changes['removals']}");  
        }

        // add to semester -- SIMS courses that aren't in CCheckIn
        foreach ($classdataCourses as $classdataCourse)
        {
            if (!isset($currentSemesterCourses[$classdataCourse->externalCourseKey]))
            {
                $changes['adds']++;
                $newCourse = $this->schema('Ccheckin_Courses_Course')->createInstance();
                $newCourse->courseKey = $classdataCourse->externalCourseKey;
                $newCourse->shortName = $classdataCourse->courseId;
                $newCourse->name = $classdataCourse->courseName;
                $newCourse->save();
                $semester->courses->add($newCourse);

                $app->log('debug', "Course {$newCourse->name}:\t{$changes['adds']}\t{$changes['removals']}");
            }
        }
        $semester->courses->save();

    }

    private function syncChildCampusEnrollments ($importer) {
        $app = $this->getApplication();
        $semesters = $this->schema('Ccheckin_Semesters_Semester');
//echo "<pre>"; var_dump($student->firstName); die;
        if ($semesterId = $app->semesterManager->getCurrentSemesterCode())
        {
            if ($semester = $semesters->findOne($semesters->code->equals($semesterId)))
            {
                $accounts = $this->schema('Bss_AuthN_Account');
                $roles = $this->schema('Ccheckin_AuthN_Role');
                
                $role = $roles->findOne($roles->name->equals('Early Start'));
                $facultyRole = $roles->findOne($roles->name->equals('CSU Faculty/Staff'));
                        
                foreach ($semester->courses as $course)
                {
                    $changes = array('adds' => 0, 'removals' => 0);
                    
                    $existingStudents = array();
                    foreach ($course->students as $student)
                    {
                        $existingStudents[$student->account->username] = $student;
                    }
                    
                    if (($enrollments = $importer->findEnrollmentsByCourses($course->courseKey)))
                    {
                        foreach ($enrollments as $enrollment)
                        {//echo "<pre>"; var_dump($enrollment->sfsuId); die;
                            switch ($enrollment->role)
                            {
                                case 'student':
                                    if (!empty($existingStudents[$enrollment->sfsuId]))
                                    {
                                        unset($existingStudents[$enrollment->sfsuId]);
                                    }
                                    elseif (($account = $accounts->findOne($accounts->username->lower()->equals(strtolower($enrollment->sfsuId)))))
                                    {
                                        $course->enrollStudent($account);
                                        $changes['adds']++;
                                        if ($role)
                                        {
                                            $account->roles->add($role);
                                            $account->roles->save();
                                        }
                                    }
                                break;

                                case 'instructor':
                                    if (($account = $accounts->findOne($accounts->username->lower()->equals(strtolower($enrollment->sfsuId)))))
                                    {
                                        if ($facultyRole && !$account->roles->has($facultyRole))
                                        {
                                            $account->roles->add($facultyRole);
                                            $account->roles->save();
                                        }
                                    }
                                break;
                            }
                        }
                        
                        if (!empty($existingStudents))
                        {
                            foreach ($existingStudents as $student)
                            {
                                $changes['removals']++;
                                $course->students->remove($student);
                            }
                        }
                    }
                    
                    $course->students->save();

                    $app->log('debug', "Course {$course->name}:\t{$changes['adds']}\t{$changes['removals']}");
                }
            }
        }
    }


    
    private function schema ($recordClass)
    {
        return $this->getApplication()->schemaManager->getSchema($recordClass);
    }
}
