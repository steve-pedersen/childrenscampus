<?php

/**
 * 
 * @author  Charles O'Sullivan (chsoney@sfsu.edu)
 * @author  Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Ccheckin_Courses_AdminController extends At_Admin_Controller
{
    public static function getRouteMap ()
    {
        return array(
            'admin/courses'            => array('callback' => 'manage'),
            'admin/courses/queue'      => array('callback' => 'queue'),
            'admin/courses/queue/:id'  => array('callback' => 'queue', ':id' => '[0-9]+'),
            'admin/courses/types'      => array('callback' => 'types'),
            'admin/courses/tasks'      => array('callback' => 'tasks'),
            'admin/courses/edit/:id'   => array('callback' => 'edit', ':id' => '[0-9]+|new'),
            'admin/courses/instructions'     => array('callback' => 'editInstructions'),
            // 'admin/courses/dropstudents/:id' => array('callback' => 'dropStudents', ':id' => '[0-9]+'),
        );
    }

    protected function beforeCallback ($callback)
    {
        parent::beforeCallback($callback);
        $this->template->clearBreadcrumbs();
        $this->addBreadcrumb('home', 'Home');
        $this->addBreadcrumb('admin', 'Admin');
        // if admin and on admin page, don't display 'Contact' sidebar
        $adminPage = false;
        $path = $this->request->getFullRequestedUri();
        if ($this->hasPermission('admin') && (strpos($path, 'admin') !== false))
        {
            $adminPage = true;
        }
        $this->template->adminPage = $adminPage; 
    }

    public function manage ()
    {
        $courses = $this->schema('Ccheckin_Courses_Course');
        $facets = $this->schema('Ccheckin_Courses_Facet');
        $purposes = $this->schema('Ccheckin_Purposes_Purpose');
        $message = '';
        
        $coursesIndexTabs = array(
            'active' => 'Active Courses',
            'inactive' => 'Inactive Courses',
        );
        
        $tab = $this->request->getQueryParameter('tab', 'active');
        
        if ($this->request->wasPostedByUser())
        {
            if ($command = $this->getPostCommand())
            {
                switch ($command)
                {
                    case 'inactive':
                        $courseIds = $this->request->getPostParameter('courses', array());
                        
                        foreach ($courseIds as $courseId)
                        {
                            if ($course = $courses->get($courseId))
                            {
                                $course->active = false;
                                $course->save();
                            }
                            
                            $message = 'The selected courses have been deactivated';
                        }
                        break;
                    case 'active':
                        $courseIds = $this->request->getPostParameter('courses', array());
                        
                        foreach ($courseIds as $courseId)
                        {                   
                            if ($course = $courses->get($courseId))
                            {
                                $course->active = true;
                                $course->save();
                            }
                            
                            $message = 'The selected courses have been activated';
                        }
                        break;
                    case 'remove':
                        $courseIds = $this->request->getPostParameter('courses');
                        
                        foreach ($courseIds as $courseId)
                        {                                               
                            if ($course = $courses->get($courseId))
                            {
                                $course->deleted = true;
                                $course->active = false;
                                $course->save();

                                $facet = $facets->findOne($facets->typeId->equals($course->facetType->id));
                                // $facetPurpose = $purposes->findOne($purposes->objectId->equals($facet->id));

                                if ($facet->purpose)
                                {
                                    foreach ($course->students as $student)
                                    {
                                        $facet->removeUser($student);
                                    }
                                }
                            }
                            
                            $message = 'The selected courses have been deleted';
                        }
                        break;
                }
            }
        }
        
        if ($tab == 'active')
        {
            $coursesFiltered = $courses->find($courses->active->isTrue(), array('orderBy' => 'shortName'));
        }
        else
        {
            $coursesFiltered = $courses->find($courses->active->isFalse(), array('orderBy' => 'shortName'));
        }

        $this->template->requests = $this->schema('Ccheckin_Courses_Request');
        $this->template->coursesIndexTabs = $coursesIndexTabs;
        $this->template->tab = $tab;
        $this->template->message = $message;
        $this->template->courses = $coursesFiltered;
    }

    public function queue ()
    {
        $this->setPageTitle('Courses Queue');
        $courseRequests = $this->schema('Ccheckin_Courses_Request');
        $facets = $this->schema('Ccheckin_Courses_Facet');
        $purposes = $this->schema('Ccheckin_Purposes_Purpose');

        $allowed = array();
        $denied = array();
        $moreInfo = false;

        if ($reqid = $this->getRouteVariable('id'))
        {
            $courseRequest = $this->requireExists($courseRequests->get($reqid));
            $moreInfo = true;
            $this->addBreadcrumb('admin/courses/queue', 'Manage course requests');
            $facet = $facets->findOne($facets->courseId->equals($courseRequest->course->id));
            $this->template->courseRequest = $courseRequest;
            $this->template->courseFacet = $facet;
            $this->template->courseEnrollments = $courseRequest->courseEnrollments;
        }
        
        if ($this->request->wasPostedByUser())
        {
            if (($command = $this->getPostCommand()) || $reqid)
            {
                $allow = $this->request->getPostParameter('allow');
                $deny = $this->request->getPostParameter('deny');
                if ($reqid)
                {
                    // this is bad, but I wanted to allow for update-users functionality to still be used at some point
                    $command = 'update-creation';
                }

                switch ($command)
                {
                    case 'update-creation':
                        
                        if (!empty($allow))
                        {
                            foreach ($allow as $id => $nothing)
                            {                           
                                if ($cr = $courseRequests->get($id))
                                {
                                    $cr->course->active = true;
                                    $cr->course->save();
                                    
                                    // $this->sendCourseAllowedNotification($cr->course, $cr->requestedBy);    // TODO: Fix email notifications!!!! *****************
                                    // TODO: Send an email to the users as well..
                                    
                                    $authZ = $this->getAuthorizationManager();
                                    $authZ->grantPermission($cr->requestedBy, 'course view', $cr->course);
                                  
                                    // $facet = $cr->course->facets->index(0); // lazy approach
                                    $facet = $facets->findOne(
                                        $facets->typeId->equals($cr->course->facetType->id)->andIf(
                                            $facets->courseId->equals($cr->course->id)
                                        )
                                    );  // fail-safe approach                                   
                                    $type = strtolower($facet->type->name);

                                    if (!$facet->purpose)
                                    {
                                        $purpose = $this->schema('Ccheckin_Purposes_Purpose')->createInstance();
                                        $purpose->object = $facet;
                                        $purpose->save();
                                    } 

                                    // this isn't the best way to differentiate, but is the best thing for now since FacetType is a user editable property
                                    if ((strpos($type, 'participation') !== false) || (strpos($type, 'participate') !== false))
                                    {
                                        $facet->addUsers($cr->course->students, false);
                                    }
                                    elseif ((strpos($type, 'observation') !== false) || (strpos($type, 'observe') !== false))
                                    {
                                        $facet->addUsers($cr->course->students, true);
                                    }
                                    
                                    $allowed[] = $cr->course->fullName;
                                    $cr->delete();
                                }
                            }                           
                        }
                        
                        if (!empty($deny))
                        {
                            foreach ($deny as $id => $nothing)
                            {                           
                                if ($cr = $courseRequests->get($id))
                                {
                                    // $this->sendCourseDeniedNotification($cr->course, $cr->requestedBy);     // TODO: Fix email notifications!!!! *****************
                                    $denied[] = $cr->course->fullName;
                                    $cr->course->active = false;
                                    $cr->course->deleted = true;
                                    $cr->course->save();
                                    
                                    $facet = $facets->findOne($facets->typeId->equals($cr->course->facetType->id));
                                    if ($facet->purpose)
                                    {
                                        foreach ($cr->course->students as $student)
                                        {
                                            $facet->removeUser($student);
                                        }
                                    }

                                    $cr->delete();
                                }
                            }
                        }
                        break;

                    case 'update-users':
                        // removed this functionality
                        break;
                }
                $this->flash('Course requests have been updated.');
                $this->response->redirect('admin/courses/queue');
            }
        }
        
        // Get the remaining queued records
        $crs = $courseRequests->getAll(array('orderBy' => 'requestDate'));
        $duplicates = array();
        foreach ($crs as $cr)
        {
            $temp = $cr->course->shortName . $cr->course->facetType->name;
            if (in_array($temp, $duplicates))
            {
                $duplicates['found'][] = $cr->id;
            }
            else
            {
                $duplicates[] = $temp;
            }
        }
        
        $this->template->duplicates = isset($duplicates['found']) ? $duplicates['found'] : array();
        $this->template->moreInfo = $moreInfo;
        $this->template->courserequests = $crs;
        $this->template->allowed = $allowed;
        $this->template->denied = $denied;
    }
   
    // TODO: Finish this method.
    public function edit ()
    {
        $id = $this->requireExists($this->getRouteVariable('id'));
        $courses = $this->schema('Ccheckin_Courses_Course');
        $courseFacets = $this->schema('Ccheckin_Courses_Facet');
        $courseFacetTypes = $this->schema('Ccheckin_Courses_FacetType');
        $accounts = $this->schema('Bss_AuthN_Account');
        $authZ = $this->getAuthorizationManager();
        
        // $instructors = $diva->user->userAccount->findByRoleName('Faculty', array('lastName' => true));
        $roles = $this->schema('Ccheckin_AuthN_Role');
        $facultyRole = $roles->findOne($roles->name->equals('Teacher'));     

        $students = '';
        $studentsObserve = '';
        $studentsParticipate = '';

        $semData = $this->schema('Ccheckin_Semesters_Semester');
        $sems = $semData->getAll(array('orderBy' => 'startDate'));
        $semesters = array();
        foreach ($sems as $sem)
        {
            $semesters[$sem->id] = $sem;
        }
        $errors = array();
		$new = false;
               
        if (is_numeric($id))
		{
            $course = $this->requireExists($courses->get($id));
            $facet = $course->facets->index(0);
            $this->setPageTitle('Edit Course: ' . $course->shortName);
            $instructors = $course->teachers;
		}
		else
		{
			$new = true;
			$this->setPageTitle('Create a Course');
            $course = $courses->createInstance();
            $facet = $courseFacets->createInstance();
            $instructors = $facultyRole->accounts;
		}
		
        $facetTypes = $courseFacetTypes->getAll(array('orderBy' => 'sortName'));

        if ($this->request->wasPostedByUser())
        {
            if ($command = $this->getPostCommand())
            {               
                switch ($command)
                {
                    case 'save':
                        
                        if ($course->inDataSource)
                        {
                            $facet = $course->facets->index(0);
                        }

                        $facetData = $this->request->getPostParameter('facet');
                        $facetData['tasks'] = json_encode($facetData['tasks']);
                        $facet->absorbData($facetData);

                        $course->absorbData($this->request->getPostParameter('course'));
                        
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
							$course->active = $course->active ? true : false;    // TODO: update to accept courseData value
                            $course->save();

                            if (!$facet->inDataSource)
                            {
                                $facet->courseId = $course->id;
                            }

                            $facet->save();

                            if (!$facet->purpose)
                            {
                                $purpose = $this->schema('Ccheckin_Purposes_Purpose')->createInstance();
                                $purpose->object = $facet;
                                $purpose->save();
                            }

                            if ($instructorId = $this->request->getPostParameter('instructor'))
                            {
                                if ($instructorAccount = $accounts->get($instructorId))
                                {
                                    if (!$authZ->hasPermission($instructorAccount, 'course view', $course))
                                    {
                                        $authZ->grantPermission($instructorAccount, 'course view', $course);
                                    }
                                }
                            }
                            
                            $type = strtolower($course->facetType->name);
                            // this isn't the best way to differentiate, but is the best thing for now since FacetType is a user editable property
                            if ((strpos($type, 'participation') !== false) || (strpos($type, 'participate') !== false))
                            {
                                $facet->addUsers($course->students, false);
                            }
                            elseif ((strpos($type, 'observation') !== false) || (strpos($type, 'observe') !== false))
                            {
                                $facet->addUsers($course->students, true);
                            }
                            
                            $this->flash('Course has been updated');
							$this->response->redirect('admin/courses');
                        }
                        
                        break;
                }
            }
        }
        
		$this->template->facetTypes = $facetTypes;
        $this->template->course = $course;
        $this->template->facet = $facet;
        $this->template->instructors = $instructors;
        $this->template->semesters = $semesters;
        $this->template->studentsObserve = $studentsObserve;
        $this->template->studentsParticipate = $studentsParticipate;
        $this->template->new = $new;
        $this->template->errors = $errors;
    }
    

    // // NOTE: Deprecated functionality
    // public function dropStudents ()
    // {
    //     $id = $this->getRouteVariable('id');
    //     $course = $this->requireExists($this->schema('Ccheckin_Courses_Course')->get($id));
        
    //     if ($this->request->wasPostedByUser())
    //     {
    //         if ($command = $this->getPostCommand())
    //         {
    //             switch ($command)
    //             {
    //                 case 'drop':
    //                     foreach ($course->students as $user)
    //                     {
    //                         foreach ($course->facets as $facet)
    //                         {
    //                             $facet->removeUser($user);
    //                         }
    //                     }
    //                     $this->flash('The students have been removed from the course');
    //                     $this->response->redirect('admin/courses');
    //                     break;
    //             }
    //         }
    //     }
        
    //     $this->template->course = $course;
    // }
    

    public function types ()
    {
        $this->setPageTitle('Manage Course Types');
        $courseFacetTypes = $this->schema('Ccheckin_Courses_FacetType');
        $errors = array();
        $message = '';
        
        if ($this->request->wasPostedByUser())
        {
            if ($command = $this->getPostCommand())
            {       
                switch ($command)
                {
                    case 'remove':
                        if ($courseTypes = $this->request->getPostParameter('courseTypes'))
                        {
                            foreach ($courseTypes as $type)
                            {                           
                                if ($courseType = $courseFacetTypes->get($type))
                                {
                                    $courseType->delete();
                                }
                            }

                            $message = 'The course types have been deleted';
                        }
                        break;

                    case 'add':
                        $courseType = $courseFacetTypes->createInstance();
                        $courseType->name = $this->request->getPostParameter('name');
                        
                        $errors = $courseType->validate();

                        if (empty($errors))
                        {
                            $courseType->save();
                            $message = 'Course type created';
                            $this->flash($message);
                        }
                        break;
                }
            }
        }
        
        // Get the remaining queued records
        $courseTypes = $courseFacetTypes->getAll(array('sortBy' => 'sortName'));
        
        $this->template->courseTypes = $courseTypes;
        $this->template->message = $message;
        $this->template->errors = $errors;
    }

    public function tasks ()
    {
        $this->setPageTitle('Manage Course Tasks');
        $siteSettings = $this->getApplication()->siteSettings;
        $courseTasks = json_decode($siteSettings->getProperty('course-tasks'), true);
        $courseFacets = $this->schema('Ccheckin_Courses_Facet');
        $errors = array();
        $message = '';

        if ($this->request->wasPostedByUser())
        {
            if ($command = $this->getPostCommand())
            {            
                switch ($command)
                {
                    case 'remove':
                        if ($tasks = $this->request->getPostParameter('courseTasks'))
                        {
                            foreach ($tasks as $taskkey => $task)
                            {   
                                unset($courseTasks[$taskkey]);
                                $updatedCourseTasks = array_values($courseTasks);
                            }

                            $siteSettings->setProperty('course-tasks', json_encode($updatedCourseTasks));
                            $this->flash('The course tasks have been deleted');
                        }
                        break;

                    case 'add':                                       
                        $courseTasks[] = $this->request->getPostParameter('name');
                        $siteSettings->setProperty('course-tasks', json_encode($courseTasks));
                        $this->flash('Course task created');

                        break;
                }
            }
        }
        
        $this->template->courseTasks = $courseTasks;
        $this->template->errors = $errors;
    }

    public function editInstructions ()
    {
        $this->setPageTitle('Edit Instructions');
        $siteSettings = $this->getApplication()->siteSettings;
        $instructions = $siteSettings->getProperty('course-request-text');

        if ($this->request->wasPostedByUser())
        {
            if ($command = $this->getPostCommand())
            {            
                switch ($command)
                {
                    case 'save':                                       
                        $newInstructions = $this->request->getPostParameter('instructions');
                        $siteSettings->setProperty('course-request-text', $newInstructions);
                        $this->flash('Course request instructions have been updated.');
                        break;
                }
            }
        }
        
        $this->template->instructions = $instructions;
    }

    // TODO: Convert functions below -- they use DivaMailer, which inherits from PHPMailer

	protected function sendCourseAllowedNotification ($course, $account)
	{
		$diva = Diva::GetInstance();
		
        $template = $this->createEmailTemplate('email_course_allowed.tpl');
        $template->assign('courseViewLink', $diva->Link('courses/view/' . $course->id));
        $template->assign('courseStudentsLink', $diva->Link('courses/students/' . $course->id));
		// And send them an e-mail so they can use it:
		$mail = new DivaMailer();
		$mail->Subject = 'Children\'s Campus Checkin: Course request has been approved';
		$mail->Body = $template->render();
		$mail->AltBody = strip_tags($mail->Body);
		$mail->AddAddress($account->email);
		//$mail->AddAttachment(glue_path(dirname(__FILE__), 'resources', 'Guidelines_Observing_2014_2015.pdf'), 'Guidelines for Observing 2014 2015.pdf');
		$mail->AddAttachment(glue_path(dirname(__FILE__), 'resources', 'Guidelines_for_SF_State_Student_Participants_2016-17.pdf'), 'Guidelines for SF State Student Participants 2016-2017.pdf');
		$mail->AddAttachment(glue_path(dirname(__FILE__), 'resources', 'Guidelines_for_SF_State_Student_Observers_2016-17.pdf'), 'Guidelines for SF State Student Observers 2016-2017.pdf');
		$mail->Send();
	}
	
	protected function sendCourseDeniedNotification ($course, $account)
	{
		$diva = Diva::GetInstance();
		$template = $this->createEmailTemplate('email_course_denied.tpl');
		// And send them an e-mail so they can use it:
		$mail = new DivaMailer();
		$mail->Subject = 'Children\'s Campus Checkin: Course request has been denied';
		$mail->Body = $template->render();
		$mail->AltBody = strip_tags($mail->Body);
		$mail->AddAddress($account->email);
		$mail->Send();
	}
    
    protected function sendUsersAllowedNotification ($course, $account)
	{
		$diva = Diva::GetInstance();
		
		$template = $this->createEmailTemplate('email_students_allowed.tpl');
        $template->assign('course', $course);
		// And send them an e-mail so they can use it:
		$mail = new DivaMailer();
		$mail->Subject = 'Children\'s Campus Checkin: Course students request has been approved';
		$mail->Body = $template->render();
		$mail->AltBody = strip_tags($mail->Body);
		$mail->AddAddress($account->email);
		$mail->Send();
	}
	
	protected function sendUsersDeniedNotification ($course, $account)
	{
		$diva = Diva::GetInstance();
		
        $template = $this->createEmailTemplate('email_students_denied.tpl');
        $template->assign('course', $course);
		// And send them an e-mail so they can use it:
		$mail = new DivaMailer();
		$mail->Subject = 'Children\'s Campus Checkin: Course students request has been denied';
		$mail->Body = 
			'<p>Children\'s Campus has denied the request to add students to your course.' . "{$course->fullName}.\n" . 
			"\n" .
			'<p>If you need any further help, feel free to contact us by responding to this' . "\n" .
			'e-mail address</p>' . "\n";
		$mail->AltBody = strip_tags($mail->Body);
		$mail->AddAddress($account->email);
		$mail->Send();
	}
    
    // private function createEmailTemplate($templateName)
    // {
    //     $template = new DivaTemplate;
    //     $template->setDefaultResourceDirectory(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'resources');
    //     $template->setTemplateFile($templateName);
    //     return $template;
    // }

    protected function createEmailTemplate ()
    {
        $template = $this->createTemplateInstance();
        $template->setMasterTemplate(Bss_Core_PathUtils::path(dirname(__FILE__), 'resources', 'email.html.tpl'));
        return $template;
    }

    protected function createEmailMessage ($contentTemplate = null)
    {
        $message = new Bss_Mailer_Message($this->getApplication());
        
        if ($contentTemplate)
        {
            $tpl = $this->createEmailTemplate();
            $message->setTemplate($tpl, $this->getModule()->getResource($contentTemplate));
        }
        
        return $message;
    }
}

