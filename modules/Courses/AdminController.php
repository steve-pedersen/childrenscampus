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
            '/admin/courses'            => array('callback' => 'manage'),
            '/admin/courses/queue'      => array('callback' => 'queue'),
            '/admin/courses/types'      => array('callback' => 'types'),
            '/admin/courses/edit/:id'   => array('callback' => 'edit', ':id' => '[0-9]+|new'),
            '/admin/courses/dropstudents/:id' => array('callback' => 'dropStudents', ':id' => '[0-9]+'),
        );
    }

    public function queue ()
    {
        $this->setPageTitle('Courses Queue');
        $courseRequests = $this->schema('Ccheckin_Courses_Request');
        $courseUserRequests = $this->schema('Ccheckin_Courses_UserRequest');

        $allowed = array();
        $denied = array();
        
        if ($command = $this->request->getPostParameter('command'))
        {
            $action = array_shift(array_keys($command));
            $allow = $this->request->getPostParameter('allow');
            $deny = $this->request->getPostParameter('deny');
            switch ($action)
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
                                $this->sendCourseAllowedNotification($cr->course, $cr->requestedBy);
                                $cr->requestedBy->grantPermission('course view', $cr->course);
                                $users = $cr->courseUsers;
                                
                                foreach ($cr->course->facets as $facet)
                                {   
                                    if (isset($users['observe']))
                                    {
                                        $facet->addUsers($users['observe'], true);
                                    }
                                    
                                    if (isset($users['participate']))
                                    {
                                        $facet->addUsers($users['participate'], false);
                                    }
                                    
                                    break;
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
                                $this->sendCourseDeniedNotification($cr->course, $cr->requestedBy);
                                $denied[] = $cr->course->fullName;
                                $cr->course->delete();
                                $cr->delete();
                            }
                        }
                    }
                    break;

                case 'update-users':
                    if (!empty($allow))
                    {
                        foreach ($allow as $id => $nothing)
                        {                            
                            if ($cur = $courseUserRequests->get($id))
                            {
                                $this->sendUsersAllowedNotification($cur->course, $cur->requestedBy);
                                $users = $cur->users;
                                
                                foreach ($cur->course->facets as $facet)
                                {   
                                    if (isset($users['observe']))
                                    {
                                        $facet->addUsers($users['observe'], true);
                                    }
                                    
                                    if (isset($users['participate']))
                                    {
                                        $facet->addUsers($users['participate'], false);
                                    }
                                    
                                    break;
                                }
                                
                                $allowed[] = $cur->course->fullName;
                                $cur->delete();
                            }
                        }
                    }
                    
                    
                    if (!empty($deny))
                    {
                        foreach ($deny as $id => $nothing)
                        {
                            if ($cur = $courseUserRequests->get($id))
                            {
                                $this->sendUsersDeniedNotification($cur->course, $cur->requestedBy);
                                $denied[] = $cur->course->fullName;
                                $cur->delete();
                            }
                        }
                    }
                    break;
            }
        }
        
        // Get the remaining queued records
        $crs = $courseRequests->getAll(array('orderBy' => 'requestDate'));
        $curs = $courseUserRequests->getAll(array('orderBy' => 'requestDate'));

        $this->template->courseUserRequests = $curs;
        $this->template->courserequests = $crs;
        $this->template->allowed = $allowed;
        $this->template->denied = $denied;
    }
    

    public function manage ()
    {
        $courses = $this->schema('Ccheckin_Courses_Course');
		$message = '';
        
        $coursesIndexTabs = array(
            'active' => 'Active Courses',
            'inactive' => 'Inactive Courses',
        );
        
        $tab = $this->request->getQueryParameter('tab', 'active');        // TODO: replace
		
		if ($command = $this->request->getPostParameter('command'))
		{
			switch (array_shift(array_keys($command)))
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
						
						$message = 'The selected courses have been deactivated';
					}
					break;
				case 'remove':
					$courseIds = $this->request->getPostParameter('courses');
					
					foreach ($courseIds as $courseId)
					{												
						if ($course = $courses->get($courseId))
						{
							$course->delete();
						}
						
						$message = 'The selected courses have been deleted';
					}
					break;
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

        $this->template->coursesIndexTabs = $coursesIndexTabs;
        $this->template->tab = $tab;
        $this->template->message = $message;
        $this->template->courses = $coursesFiltered;
    }
    

    public function edit ()
    {
        $id = $this->requireExists($this->getRouteVariable('id'));  // TODO: verify requireExists is ok for routeVar 'id'
        $courses = $this->schema('Ccheckin_Courses_Course');
        // $facets = $this->schema('Ccheckin_Courses_Facets');
        $courseFacetTypes = $this->schema('Ccheckin_Courses_FacetTypes');
        $courseInstructors = $this->schema('Ccheckin_Course_Instructors');
        $accounts = $this->schema('Bss_AuthN_Accounts');
        
        // $instructors = $diva->user->userAccount->findByRoleName('Faculty', array('lastName' => true));
        $roles = $this->schema('Ccheckin_AuthN_Role');
        $facultyRole = $roles->findOne($roles->name->equals('Faculty'));
        $instructors = $facultyRole->accounts;

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
		}
		else
		{
			$new = true;
			$this->setPageTitle('Create a Course');
            $course = $courses->createInstance();
		}
		
        $facetTypes = $courseFacetTypes->getAll(array('orderBy' => 'sortName'));

        if ($this->request->getRequestMethod() == 'post')
        {
            if ($command = $this->request->getPostParameter('command'))
            {
                $action = array_shift(array_keys($command));
                
                switch ($action)
                {
                    case 'save':
                        $studentsObserve = $this->request->getPostParameter('students-observe');
                        $studentsParticipate = $this->request->getPostParameter('students-participate');
                        
                        // was $course->inDatabase
                        if ($course->inDataSource)
                        {
                            $facet = $course->facets->index(0);
                        }
                        // NOTE: Test that this does as intended ***************************************
                        // $facet->absorbArray($this->request->getPostParameter('facet'));
                        $facet->absorbData($this->request->getPostParameter('facet'));
                        // $course->absorbArray($this->request->getPostParameter('course'));
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
							$course->active = true;
                            $course->save();
                            
                            // was $facet->inDatabase
                            if (!$facet->inDataSource)
                            {
                                $facet->courseId = $course->id;
                            }
                            
                            $facet->save();

                            if ($instructorId = $this->request->getPostParameter('instructor'))
                            {
                                if ($instructorAccount = $accounts->get($instructorId))
                                {
                                    if (!$instructorAccount->hasPermission('course view', $course))
                                    {
                                        $instructor = $courseInstructors->createInstance();
                                        $instructor->accountId = $instructorId;
                                        $instructor->courseId = $course->id;
                                        $instructor->save();
                                        $instructor->account->grantPermission('course view', $course);
                                    }
                                }
                            }
                            
                            if ($studentsObserve)
                            {
                                $users = explode("\n", $studentsObserve);
                                $facet->addUsers($users);
                            }
                            
                            if ($studentsParticipate)
                            {
                                $users = explode("\n", $studentsParticipate);
                                $facet->addUsers($users, false);
                            }
                            
							$this->response->redirect('admin/courses');
                        }
                        
                        break;
                }
            }
        }
        
		$this->template->$facetTypes = $facetTypes;
        $this->template->$course = $course;
        $this->template->$facet = $facet;
        $this->template->$instructors = $instructors;
        $this->template->$semesters = $semesters;
        $this->template->$studentsObserve = $studentsObserve;
        $this->template->$studentsParticipate = $studentsParticipate;
        $this->template->$new = $new;
        $this->template->$errors = $errors;
    }
    

    public function dropStudents ()
    {
        $id = $this->getRouteVariable('id');
        $course = $this->requireExists($this->schema('Ccheckin_Courses_Course')->get($id));
        
        if ($this->request->getRequestMethod() == 'post')
        {
            if ($command = $this->request->getPostParameter('command'))
            {
                switch (array_shift(array_keys($command)))
                {
                    case 'drop':
                        foreach ($course->students as $user)
                        {
                            foreach ($course->facets as $facet)
                            {
                                $facet->removeUser($user);
                            }
                        }
                        $this->flash('The students have been removed from the course');
                        $this->response->redirect('admin/courses');
                        break;
                }
            }
        }
        
        $this->template->course = $course;
    }
    

    public function types ()
    {
        $this->setPageTitle('Manage Course Types');
        $courseFacetTypes = $this->schema('Ccheckin_Courses_FacetTypes');
        $errors = array();
        $message = '';
        
        if ($command = $this->request->getPostParameter('command'))
        {
            $action  = array_shift(array_keys($command));
            
            switch ($action)
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
                    }
                    break;
            }
        }
        
        // Get the remaining queued records
        $courseTypes = $courseFacetTypes->getAll(array('sortBy' => 'sortName'));
        
        $this->template->$courseTypes = $courseTypes;
        $this->template->$message = $message;
        $this->template->$errors = $errors;
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
    
    private function createEmailTemplate($templateName)
    {
        $template = new DivaTemplate;
        $template->setDefaultResourceDirectory(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'resources');
        $template->setTemplateFile($templateName);
        return $template;
    }
}

