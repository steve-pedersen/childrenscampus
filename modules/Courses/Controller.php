<?php


class Ccheckin_Courses_Controller extends Ccheckin_Master_Controller
{
    public static function getRouteMap ()
    {
        return array(
            'courses'               => array('callback' => 'index'),
            'courses/view/:id'      => array('callback' => 'view', ':id' => '[0-9]+'),
            'courses/history/:id'   => array('callback' => 'history', ':id' => '[0-9]+'),
            'courses/request'       => array('callback' => 'request'),
            'courses/students/:id'  => array('callback' => 'students', ':id' => '[0-9]+'),
            'courses/drop/:cid/:aid'=> array('callback' => 'drop', ':cid' => '[0-9]+', ':aid' => '[0-9]+'),
        );
    }

    public function index ()
    {
        $viewer = $this->getAccount();
        $authZ = $this->getAuthorizationManager();
        $azids = $authZ->getObjectsForWhich($viewer, 'course view', 'Course');
        $courses = $this->schema('Ccheckin_Courses_Course')->getByAzids($azids);
        
        if (empty($courses))
        {
            $azids = $authZ->getObjectsForWhich($viewer, 'purpose have', 'Purpose');
            $purposes = $this->schema('Ccheckin_Purposes_Purpose')->getByAzids($azids);         
            
            foreach ($purposes as $purpose)
            {
                $object = $purpose->getObject();
                
                if ($object instanceof Ccheckin_Courses_Facet)
                {
                    $course = $object->course;
                    $courses[$course->id] = $course;
                }
            }
        }
        
        $cs = array();
        
        foreach ($courses as $course) 
        {
            if ($course->active)
            {
                $cs[] = $course;
            }
        }
        
        $this->template->canRequest = $this->hasPermission('course request');
        $this->template->courses = $cs;
    }
    
    public function view ()
    {
        $account = $this->requireLogin();
        $id = $this->getRouteVariable('id');
        $course = $this->requireExists($this->schema('Ccheckin_Courses_Course')->get($id));
        
        if (!$this->hasPermission('course view', $course) || (!$this->hasPermission('admin') && !$course->active))
        {
            if ($course->students->inList($account)) // TODO: Check this...... *******************
            {
                $facetids = array();
                foreach ($course->facets as $facet)
                {
                    $facetids[] = $facet->purpose->id;
                }
                
                if (!empty($facetids))
                {
                    $obs = $this->schema('Ccheckin_Rooms_Observation');
                    // TODO: Check if this should be OR or AND ************************************
                    $cond =
                        $obs->allTrue(
                            $obs->accountId->equals($account->id),
                            $obs->purposeId->inList($facetids),
                            $obs->endTime->afterOrEquals(new Date(0))                          
                        );
                    $observations = $obs->find($cond);

                    $totalTime = 0;
                    foreach ($observations as $observation)
                    {
                        $totalTime += $observation->duration;
                    }
                    $this->template->totalTime = $totalTime;
                    $this->template->observations = $observations;
                }
                
                $this->template->students = array($account);
            }
            else
            {
                // $this->raiseError(403, 'Forbidden');
                $this->triggerError('Ccheckin_Master_PermissionErrorHandler');
                exit;
            }
        }
        else
        {
            $this->template->pView = true;
            $this->template->students = $course->students;
        }
        
        $this->template->course = $course;
    }
    
    public function history ()
    {
        $this->requireLogin();
        $id = $this->getRouteVariable('id');
        $course = $this->requireExists($this->schema('Ccheckin_Courses_Course')->get($id));
        $this->setPageTitle("Course History for: {$course->shortName}");   
        
        if (!$this->hasPermission('course view', $course) || (!$this->hasPermission('admin') && !$course->active))
        {
            // $this->raiseError(403, 'Forbidden');
            $this->triggerError('Ccheckin_Master_PermissionErrorHandler');
            exit;
        }
        
        $facets = array();
        
        foreach ($course->facets as $facet) {
            $facetArray = array('users' => array(), 'facet' => $facet);
            
            foreach ($facet->purpose->observations as $observation)
            {
                if ($observation->duration > 0)
                {
                    if (!isset($facetArray['users'][$observation->accountId]))
                    {
                        $facetArray['users'][$observation->accountId] = array('num' => 0, 'time' => 0, 'user' => $observation->account);
                    }
                    
                    $facetArray['users'][$observation->accountId]['num']++;
                    $facetArray['users'][$observation->accountId]['time'] += $observation->duration;
                }
            }
            
            $facets[] = $facetArray;
        }
               
        $this->template->course = $course;
        $this->template->facets = $facets;
    }
    
    public function request ()
    {       
        $requester = $this->requireLogin();
        $this->requirePermission('course request');
       
        $course = $this->schema('Ccheckin_Courses_Course')->createInstance();
        $facet = $this->schema('Ccheckin_Courses_Facet')->createInstance();
        $request = $this->schema('Ccheckin_Courses_Request')->createInstance();
        $sems = $this->schema('Ccheckin_Semesters_Semester')->getAll(array('orderBy' => 'startDate'));
        $facetTypes = $this->schema('Ccheckin_Courses_FacetType')->getAll(array('orderBy' => 'sortName'));
        $errors = array();
        $success = false;
        $studentsObserve = '';
        $studentsParticipate = '';
        $semesters = array();        
        foreach ($sems as $sem)
        {
            $semesters[$sem->id] = $sem;
        }
        
        if ($command = $this->request->getPostParameter('command'))
        {
            
            switch (array_shift(array_keys($command)))
            {
                case 'request':
                    $facet->absorbData($this->request->getPostParameter('facet'));
                    $course->absorbData($this->request->getPostParameter('course'));
                    $studentsObserve = $this->request->getPostParameter('students-observe');
                    $studentsParticipate = $this->request->getPostParameter('students-participate');
                    
                    if ($semesterId = $this->request->getPostParameter('semester'))
                    {
                        $semester = $semesters[$semesterId];
                        $course->startDate = $semester->startDate;
                        $course->endDate = $semester->endDate;
                    }
                    
                    $errors += $course->validate();
                    $errors += $facet->validate();

                    if (empty($errors))
                    {
                        $course->active = false;
                        $course->save();
                        $facet->courseId = $course->id;
                        $facet->save();
                        $instructor = $this->schema('Ccheckin_Courses_Instructor')->createInstance();
                        $instructor->accountId = $requester->id;
                        $instructor->courseId = $course->id;
                        $instructor->save();
                        
                        $request->courseId = $course->id;
                        $request->requestedById = $requester->id;
                        $request->requestDate = date('c');
                        $users = array();
                        
                        if ($studentsObserve)
                        {
                            $users['observe'] = array_filter(explode("\n", $studentsObserve));
                        }
                        
                        if ($studentsParticipate)
                        {
                            $users['participate'] = array_filter(explode("\n", $studentsParticipate));
                        }
                        
                        if (!empty($users))
                        {
                            $request->courseUsers = $users;
                        }
                        
                        $request->save();
                        $this->sendCourseRequestedNotification($course, $requester);
                        $this->flash('You course request is now pending.  You will be notified when a decision has been made.');
                        $this->response->redirect('courses');
                    }
                    
                    break;
            }
        }
        
        $this->template->facetTypes = $facetTypes;
        $this->template->course = $course;
        $this->template->facet = $facet;
        $this->template->errors = $errors;
        $this->template->success = $success;
        $this->template->studentsObserve = $studentsObserve;
        $this->template->studentsParticipate = $studentsParticipate;
        $this->template->semesters = $semesters;
    }
    
    public function students ()
    {
        $viewer = $this->requireLogin();
        $id = $this->getRouteVariable('id');   
        $course = $this->requireExists($this->schema('Ccheckin_Courses_Course')->get($id));
        
        if (!$this->hasPermission('course view', $course) || (!$this->hasPermission('admin') && !$course->active))
        {
            $this->triggerError('Ccheckin_Master_PermissionErrorHandler');
            exit;
        }
        
        if ($this->request->getRequestMethod() == 'post')
        {
            if ($command = $this->request->getPostParameter('command'))
            {
                $action = array_shift(array_keys($command));
                
                switch ($action)
                {
                    case 'request':
                        $userRequest = $this->schema('Ccheckin_Courses_UserRequest')->createInstance();
                        $users = array();
                        
                        if ($studentsObserve = $this->request->getPostParameter('students-observe'))
                        {
                            $users['observe'] = array_filter(explode("\n", $studentsObserve));
                        }
                        
                        if ($studentsParticipate = $this->request->getPostParameter('students-participate'))
                        {
                            $users['participate'] = array_filter(explode("\n", $studentsParticipate));
                        }
                        
                        if (!empty($users))
                        {
                            $userRequest->users = $users;
                            $userRequest->course = $course;
                            $userRequest->requestedBy = $viewer;
                            $userRequest->requestDate = date('c');
                            $userRequest->save();
                            $this->sendStudentRequestedNotification($course, $viewer);
                            $this->flash('Your student requests have been added to system.');
                        }
                        break;
                }
            }
        }
        
        $this->template->course = $course;
        $this->template->students = $course->students;
    }
    
    public function drop ()
    {
        $this->requireLogin();
        $courseId = $this->getRouteVariable('cid');    // TODO: Verify these are the correct route var names
        $accountId = $this->getRouteVariable('aid'); 
        $course = $this->requireExists($this->schema('Ccheckin_Courses_Course')->get($id));       
        $account = $this->requireExists($this->schema('Bss_AuthN_Account')->get($accountId));
        $this->requirePermission('course view', $course);
        
        if ($this->request->getRequestMethod() == 'post')
        {
            if ($command = $this->request->getPostParameter('command'))
            {
                switch (array_shift(array_keys($command)))
                {
                    case 'drop':
                        foreach ($course->facets as $facet)
                        {
                            $facet->removeUser($account);       // TODO: Verify removeUser() functionality
                        }
                        $this->flash('The student has been removed from the course');
                        $this->response->redirect('courses/students/' . $course->id);
                        break;
                }
            }
        }
        
        $this->template->student = $account;
        $this->template->course = $course;
    }

    // TODO: Sort out these three functions below ******************************************************
    
    protected function sendStudentRequestedNotification ($course, $account)
    {
        $diva = Diva::GetInstance();
        $template = $this->createEmailTemplateTEMPORARYFUNCTIONNAME('email_students_requested.tpl');
        $template->assign('adminLink', $diva->Link('admin/courses/queue'));
        $template->assign('account', $account);
        // And send them an e-mail so they can use it:
        $mail = new DivaMailer();
        $mail->Subject = 'Children\'s Campus Checkin: A new request for students to be added to course';
        $mail->Body = $template->render();
        $mail->AltBody = strip_tags($mail->Body);
        $mail->AddAddress(CC_REQUEST_NOTIFIEE_EMAIL);
        $mail->Send();
    }


    protected function sendCourseRequestedNotification ($course, $account)
    {
        $diva = Diva::GetInstance();
        $template = $this->createEmailTemplateTEMPORARYFUNCTIONNAME('email_course_requested.tpl');
        $template->assign('adminLink', $diva->Link('admin/courses/queue'));
        $template->assign('account', $account);
        // And send them an e-mail so they can use it:
        $mail = new DivaMailer();
        $mail->Subject = 'Children\'s Campus Checkin: A new course has been requested';
        $mail->Body = $template->render();
        $mail->AltBody = strip_tags($mail->Body);
        $mail->AddAddress(CC_REQUEST_NOTIFIEE_EMAIL);
        $mail->Send();
    }

    protected function createEmailTemplateTEMPORARYFUNCTIONNAME($templateName)
    {
        $template = new DivaTemplate;
        $template->setDefaultResourceDirectory(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'resources');
        $template->setTemplateFile($templateName);
        return $template;
    }
}
