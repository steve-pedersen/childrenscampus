<?php

/**
 */
class Ccheckin_Admin_Controller extends Ccheckin_Master_Controller
{
    public static function getRouteMap ()
    {
        return array(
            '/admin' => array('callback' => 'index'),
            '/admin/colophon' => array('callback' => 'colophon'),
			'/admin/apc' => array('callback' => 'clearMemoryCache'),
            '/admin/cron' => array('callback' => 'cron'),
            '/admin/settings/siteNotice' => array('callback' => 'siteNotice'),
            '/admin/settings/blockDates' => array('callback' => 'blockDates'),
            '/admin/settings/email' => array('callback' => 'emailSettings'),
            '/admin/kiosk' => array('callback' => 'kioskMode'),
            '/admin/reports/generate' => array('callback' => 'reports'),
            '/admin/migrate' => array('callback' => 'migrate'),
        );
    }
    
    protected function beforeCallback ($callback)
    {
        parent::beforeCallback($callback);
        $this->requirePermission('admin');
        $this->template->clearBreadcrumbs();
        $this->addBreadcrumb('home', 'Home');
        $this->addBreadcrumb('admin', 'Admin');
        // if admin and on admin page, don't display 'Contact' sidebar
        $this->template->adminPage = $this->hasPermission('admin') && (strpos($this->request->getFullRequestedUri(), 'admin') !== false);
    }

    public function migrate ()
    {
        $this->requirePermission('admin');
        $semSchema = $this->schema('Ccheckin_Semesters_Semester');
        $courseSchema = $this->schema('Ccheckin_Courses_Course');
        $facetSchema = $this->schema('Ccheckin_Courses_Facet');

        // Generate Semester 'internal'
        foreach ($semSchema->find($semSchema->internal->isNull()) as $semester)
        {
            $semester->internal = Ccheckin_Semesters_Semester::ConvertToCode($semester->display);
            $semester->save();
        }

        // Convert Facets 'tasks' from serialized to JSON
        foreach ($facetSchema->getAll() as $facet)
        {
            $tasks = ($facet->tasks ? $facet->tasks : array());
            $serialTasks = $facet->getTasks(true);                  // TODO: LEFT OFF HERE **************************** 
            // need to iterate through serialized tasks and make them into JSON
            if ($facet->getTasks(true))
            {
                $task = unserialize($facet->getTasks(true));
                $tasks[] = array_shift($task);
                

                // $json = json_encode($facet->getTasks(true));
                echo "<pre>"; var_dump($arr); die;
            }
        }

        // Generate Course_Enroll_Map 'term' from Semester->internal


        // // Generate Courses 'external_course_key'      
        // $service = new Ccheckin_ClassData_Service($this->getApplication());
        // foreach ($courseSchema->find($courseSchema->externalCourseKey->isNull()) as $course)
        // {
        //     $sem = $semSchema->findOne($semSchema->startDate->equals($course->startDate));
        //     if (!$sem)
        //     {
        //         $term = Ccheckin_Semesters_Semester::guessActiveSemester(true, $course->startDate);
        //     }
        //     else
        //     {
        //         $term = $sem->internal;
        //     }

        //     $teachers = $course->teachers;
        //     if (count($teachers) === 1)
        //     {
        //         $teacher = $course->teachers[0];            
        //     }
        //     elseif (count($teachers) > 1)
        //     {   // might need to iterate through these teachers to find the correct one...
        //         $teacher = $course->teachers[0];
        //     }
        //     else
        //     {
        //         continue;
        //     }

        //     list($status, $courses) = $service->getUserEnrollments($teacher->username, $term);

        //     if ($status < 400)
        //     {
        //         echo "<pre>"; var_dump('status < 400!', $courses); die;
        //         foreach ($courses as $c)
        //         {
        //             if ($c['shortName'] === $course->shortName)
        //             {
        //                 echo "<pre>"; var_dump('success',$c['id']); die;
        //                 $course->externalCourseKey = $c['id'];
        //             }
        //         }
        //     }
        // }
        
    }

    public function reports ()
    {
        $viewer = $this->requireLogin();
        $this->requirePermission('reports generate');

        $courseSchema = $this->schema('Ccheckin_Courses_Course');
        $obsSchema = $this->schema('Ccheckin_Rooms_Observation');
        $resSchema = $this->schema('Ccheckin_Rooms_Reservation');
        $roomSchema = $this->schema('Ccheckin_Rooms_Room');
        $semSchema = $this->schema('Ccheckin_Semesters_Semester');
        $userSchema = $this->schema('Bss_AuthN_Account');
        $roleSchema = $this->schema('Ccheckin_AuthN_Role');

        $tomorrow = new DateTime('+1 day');
        $filename = 'CC-Observation-Report-' . date('Y-m-d') . '.csv';
        $obsData = array();
        $orgs = array();

        if ($this->request->wasPostedByUser())
        {
            $from = $this->request->getPostParameter('from', 0);
            $until = $this->request->getPostParameter('until', $tomorrow);

            $observations = $obsSchema->find(
                $obsSchema->startTime->afterOrEquals($from)->andIf(
                $obsSchema->startTime->beforeOrEquals($until)),
                array('orderBy' => 'startTime')
            );

            foreach ($observations as $obs)
            {
                $course = $obs->purpose->object->course;
                if (!in_array($course->shortName, array_keys($orgs)))
                {   // cache API results
                    $orgs[$course->shortName] = array();
                    $orgs[$course->shortName]['college'] = $course->college;
                    $orgs[$course->shortName]['department'] = $course->department;
                }

                $semester = $semSchema->findOne($semSchema->startDate->equals($course->startDate));

                $obsData[$obs->id] = array();
                $obsData[$obs->id]['obsId'] = $obs->id;
                $obsData[$obs->id]['course'] = $course->shortName;
                $obsData[$obs->id]['semester'] = $semester->display;
                $obsData[$obs->id]['college'] = $orgs[$course->shortName]['college'];
                $obsData[$obs->id]['department'] = $orgs[$course->shortName]['department'];
                $obsData[$obs->id]['firstName'] = $obs->account->firstName;
                $obsData[$obs->id]['lastName'] = $obs->account->lastName;
                $obsData[$obs->id]['username'] = $obs->account->username;
                $obsData[$obs->id]['email'] = $obs->account->emailAddress;
                $obsData[$obs->id]['duration'] = $obs->duration;
            }

            header("Content-Type: application/download\n");
            header('Content-Disposition: attachment; filename="' .$filename. '"' . "\n");
            $handle = fopen('php://output', 'w+');

            if ($handle)
            {
                $headers = array(
                    'Semester',
                    'Department',
                    'Course Short Name',
                    'First Name',
                    'Last Name',
                    'Student ID',
                    'Email',
                    'Duration'
                );
                fputcsv($handle, $headers);

                foreach ($obsData as $obs)
                {
                    $row = array(
                        $obs['semester'],
                        $obs['department'],
                        $obs['course'],
                        $obs['firstName'],
                        $obs['lastName'],
                        $obs['username'],
                        $obs['email'],
                        $obs['duration'],
                    );
                    fputcsv($handle, $row);
                }
            }
            
            exit;
        }

        $this->template->tomorrow = $tomorrow;
    }
  
    /**
     * Dashboard.
     */
    public function index ()
    {
        $this->setPageTitle('Administrate');
        $requestSchema = $this->schema('Ccheckin_Courses_Request');
        $courseSchema = $this->schema('Ccheckin_Courses_Course');
        $deletedCourses = $courseSchema->find($courseSchema->deleted->isTrue());
        $dcs = array();
        
        foreach ($deletedCourses as $dc)
        {
            $dcs[] = $dc->id;
        }
        $crs = $requestSchema->find(
            $requestSchema->courseId->notInList($dcs),
            array('orderBy' => 'requestDate')
        );

        $this->template->crs = $crs;
    }

    public function kioskMode ()
    {
        $cookieName = 'cc-kiosk';
        $cookieValue = 'kiosk';
        $isKiosk = false;
        
        if (isset($_COOKIE[$cookieName]) && $_COOKIE[$cookieName] == $cookieValue)
        {
            $isKiosk = true;
        }
        
        if ($command = $this->request->getPostParameter('command'))
        {
            $temp = array_keys($command);
            $action = array_shift($temp);

            switch ($action)
            {
                case 'set':
                    if (!$isKiosk) setCookie($cookieName, $cookieValue, time()+60*60*24*30*12, '/');
                    $this->response->redirect('admin/kiosk?message=set');
                    break;
                case 'unset':
                    if ($isKiosk) setCookie($cookieName, false, time()+60*60*24*30*12, '/');
                    $this->response->redirect('admin/kiosk?message=unset');
                    break;
            }
        }
        
        $this->setPageTitle('Manage Kiosk Mode');
        $this->template->message = $this->request->getQueryParameter('message');
        $this->template->isKiosk = $isKiosk;
    }

    public function emailSettings ()
    {
        $siteSettings = $this->getApplication()->siteSettings;
        $files = $this->schema('Ccheckin_Admin_File');
        $removedFiles = array();

        if ($this->request->wasPostedByUser())
        {
            if ($removedFiles = $this->request->getPostParameter('removed-files', array()))
            {
                $removedFiles = $files->find($files->id->inList($removedFiles), array('arrayKey' => 'id'));
            }

            if ($attachments = $this->request->getPostParameter('attachments'))
            {
                $attRecords = $files->find($files->id->inList($attachments));
                
                foreach ($attRecords as $record)
                {
                    if (empty($removedFiles[$record->id]))
                    {
                        $attachments[$record->id] = $record;
                    }
                }
            }

            switch ($this->getPostCommand()) {
                case 'upload':
                    $file = $files->createInstance();
                    $file->createFromRequest($this->request, 'attachment');
                    
                    if ($file->isValid())
                    {
                        $file->uploadedBy = $this->getAccount();
                        $file->save();

                        $this->flash('The file has been uploaded to the server.');
                        $this->response->redirect('admin/settings/email');
                    }
                    
                    $this->template->errors = $file->getValidationMessages();
                    break;

                case 'remove-attachment':
                    $command = $this->request->getPostParameter('command');
                    $tmpArray = array_keys($command['remove-attachment']);
                    $id = array_shift($tmpArray);
                    if ($fileToRemove = $files->get($id))
                    {
                        $removedFiles[$fileToRemove->id] = $fileToRemove;
                        $fileToRemove->delete();
                    }

                    $this->flash("This file has been removed from the server.");
                    break;

                case 'save':
                    $testing = $this->request->getPostParameter('testingOnly');
                    $testingOnly = ((is_null($testing) || $testing === 0) ? 0 : 1);
                    $siteSettings->setProperty('email-testing-only', $testingOnly);
                    $siteSettings->setProperty('email-test-address', $this->request->getPostParameter('testAddress'));
                    $siteSettings->setProperty('email-default-address', $this->request->getPostParameter('defaultAddress'));
                    $siteSettings->setProperty('email-signature', $this->request->getPostParameter('signature'));
                    $siteSettings->setProperty('email-course-allowed-teacher', $this->request->getPostParameter('courseAllowedTeacher'));
                    $siteSettings->setProperty('email-course-allowed-students', $this->request->getPostParameter('courseAllowedStudents'));
                    $siteSettings->setProperty('email-course-denied', $this->request->getPostParameter('courseDenied'));
                    $siteSettings->setProperty('email-course-requested-admin', $this->request->getPostParameter('courseRequestedAdmin'));
                    $siteSettings->setProperty('email-course-requested-teacher', $this->request->getPostParameter('courseRequestedTeacher'));
                    $siteSettings->setProperty('email-reservation-details', $this->request->getPostParameter('reservationDetails'));
                    $siteSettings->setProperty('email-reservation-reminder-time', $this->request->getPostParameter('reservationReminderTime'));
                    $siteSettings->setProperty('email-reservation-reminder', $this->request->getPostParameter('reservationReminder'));
                    $siteSettings->setProperty('email-reservation-missed', $this->request->getPostParameter('reservationMissed'));

                    $attachedFiles = array();
                    $attachmentData = $this->request->getPostParameter('attachment');
                    
                    foreach ($attachmentData as $emailKey => $fileIds)
                    {
                        foreach ($fileIds as $fileId)
                        {
                            if (!isset($attachedFiles[$fileId]))
                            {
                                $attachedFiles[$fileId] = array();
                            }
                            if (!in_array($emailKey, $attachedFiles[$fileId]))
                            {
                                $attachedFiles[$fileId][] = $emailKey;
                            }
                        }
                    }
                    foreach ($attachedFiles as $fileId => $emailKeys)
                    {
                        $file = $files->get($fileId);
                        $file->attachedEmailKeys = $emailKeys;
                        $file->save();
                    }

                    $this->flash("Children's Campus email settings and content have been saved.");
                    $this->response->redirect('admin/settings/email');
                    exit;
                    
                case 'sendtest':
                    $viewer = $this->getAccount();
                    $command = $this->request->getPostParameter('command');
                    $which = array_keys($command['sendtest']);
                    $which = array_pop($which);

                    if ($which)
                    {
                        $emailData = array();
                        $emailData['user'] = $viewer;
                        $emailManager = new Ccheckin_Admin_EmailManager($this->getApplication(), $this);                   

                        switch ($which) 
                        {
                            case 'courseRequestedAdmin':
                                $emailData['requestingUser'] = $viewer;
                                $emailData['courseRequest'] = new stdClass();
                                $emailData['courseRequest']->id = 0;
                                $emailData['courseRequest']->fullName = 'TEST: Introduction to Childhood Development';
                                $emailData['courseRequest']->shortName = 'TEST-CAD-0101-01-Spring-2025';
                                $emailData['courseRequest']->semester = 'TEST Spring 2025';
                                $emailManager->processEmail('send' . ucfirst($which), $emailData, true);
                                
                                $this->template->sendSuccess = 'You should receive a test email momentarily for Course-Requested-Admin template.';
                                break;

                            case 'courseRequestedTeacher':
                                $emailData['courseRequest'] = new stdClass();
                                $emailData['courseRequest']->fullName = 'TEST: Introduction to Childhood Development';
                                $emailData['courseRequest']->shortName = 'TEST-CAD-0101-01-Spring-2025';
                                $emailData['courseRequest']->semester = 'TEST Spring 2025';
                                $emailManager->processEmail('send' . ucfirst($which), $emailData, true);

                                $this->template->sendSuccess = 'You should receive a test email momentarily for Course-Requested-Teacher template.';                                
                                break;

                            case 'courseAllowedTeacher':
                                $emailData['course'] = new stdClass();
                                $emailData['course']->id = 0;
                                $emailData['course']->fullName = 'TEST: Introduction to Childhood Development';
                                $emailData['course']->shortName = 'TEST-CAD-0101-01-Spring-2025';
                                $emailData['course']->openDate = new DateTime;
                                $emailData['course']->closeDate = new DateTime('now + 1 month');
                                $emailManager->processEmail('send' . ucfirst($which), $emailData, true);

                                $this->template->sendSuccess = 'You should receive a test email momentarily for Course-Allowed-Teacher template.';
                                break;

                            case 'courseAllowedStudents':
                                $emailData['course'] = new stdClass();
                                $emailData['course']->fullName = 'TEST: Introduction to Childhood Development';
                                $emailData['course']->shortName = 'TEST-CAD-0101-01-Spring-2025';
                                $emailData['course']->openDate = new DateTime;
                                $emailData['course']->closeDate = new DateTime('now + 1 month');
                                $emailManager->processEmail('send' . ucfirst($which), $emailData, true);

                                $this->template->sendSuccess = 'You should receive a test email momentarily for Course-Allowed-Students template.';
                                break;

                            case 'courseDenied':
                                $emailData['course'] = new stdClass();
                                $emailData['course']->fullName = 'TEST: Introduction to Childhood Development';
                                $emailData['course']->shortName = 'TEST-CAD-0101-01-Spring-2025';
                                $emailData['course']->semester = 'TEST Spring 2025';
                                $emailManager->processEmail('send' . ucfirst($which), $emailData, true);

                                $this->template->sendSuccess = 'You should receive a test email momentarily for Course-Denied template.';                       
                                break;

                            case 'reservationDetails':
                                $emailData['reservation'] = new stdClass();
                                $emailData['reservation']->id = 0;
                                $emailData['reservation']->startTime = new DateTime;
                                $emailData['reservation']->purpose = 'TEST Observation only course - TEST-CAD-0101-01-Spring-2025';
                                $emailData['reservation']->room = 'TEST CC-221';
                                $emailManager->processEmail('send' . ucfirst($which), $emailData, true);

                                $this->template->sendSuccess = 'You should receive a test email momentarily for Reservation-Details template.';  
                                break;
                            
                            case 'reservationReminder':
                                $emailData['reservation'] = new stdClass();
                                $emailData['reservation']->id = 0;
                                $emailData['reservation']->startTime = new DateTime;
                                $emailData['reservation']->purpose = 'TEST Observation only course - TEST-CAD-0101-01-Spring-2025';
                                $emailData['reservation']->room = 'TEST CC-221';
                                $emailManager->processEmail('send' . ucfirst($which), $emailData, true);

                                $this->template->sendSuccess = 'You should receive a test email momentarily for Reservation-Reminder template.';  
                                break;

                            case 'reservationMissed':
                                $emailData['reservation'] = new stdClass();
                                $emailData['reservation']->startTime = new DateTime;
                                $emailData['reservation']->purpose = 'TEST Observation only course - TEST-CAD-0101-01-Spring-2025';
                                $emailManager->processEmail('send' . ucfirst($which), $emailData, true);

                                $this->template->sendSuccess = 'You should receive a test email momentarily for Reservation-Reminder template.';  
                                break;
                        }
                    }
            }
        }

        $accounts = $this->schema('Bss_AuthN_Account');
        $this->template->systemNotificationRecipients = $accounts->find($accounts->receiveAdminNotifications->isTrue());
        $this->template->authZ = $this->getApplication()->authorizationManager;
        $this->template->removedFiles = $removedFiles;
        $this->template->attachments = $files->getAll();
        $this->template->testingOnly = $siteSettings->getProperty('email-testing-only', 0);
        $this->template->testAddress = $siteSettings->getProperty('email-test-address');
        $this->template->defaultAddress = $siteSettings->getProperty('email-default-address');
        $this->template->signature = $siteSettings->getProperty('email-signature');
        $this->template->courseRequestedAdmin = $siteSettings->getProperty('email-course-requested-admin');
        $this->template->courseRequestedTeacher = $siteSettings->getProperty('email-course-requested-teacher');
        $this->template->courseAllowedTeacher = $siteSettings->getProperty('email-course-allowed-teacher');
        $this->template->courseAllowedStudents = $siteSettings->getProperty('email-course-allowed-students');
        $this->template->courseDenied = $siteSettings->getProperty('email-course-denied');       
        $this->template->reservationDetails = $siteSettings->getProperty('email-reservation-details');
        $this->template->reservationReminder = $siteSettings->getProperty('email-reservation-reminder');
        $this->template->reservationReminderTime = $siteSettings->getProperty('email-reservation-reminder-time');
        $this->template->reservationMissed = $siteSettings->getProperty('email-reservation-missed');
    }
    
    /**
     */
    public function colophon ()
    {
        $moduleManager = $this->getApplication()->moduleManager;
        $this->template->moduleList = $moduleManager->getModules();
    }

    public function blockDates ()
    {      
        $siteSettings = $this->getApplication()->siteSettings;
        $storedDates = json_decode($siteSettings->getProperty('blocked-dates'), true);
        $blockDates = $this->convertToDateTimes($storedDates);

        if ($this->request->wasPostedByUser())
        {
            if ($command = $this->getPostCommand())
            {
                switch ($command)
                {
                    case 'remove':
                        if ($datesToRemove = $this->request->getPostParameter('blockDates'))
                        {
                            foreach ($datesToRemove as $i => $date)
                            {   
                                unset($storedDates[$i]);
                                $updatedBlockDates = array_values($storedDates);
                                $blockDates = $this->convertToDateTimes($updatedBlockDates);
                            }

                            $siteSettings->setProperty('blocked-dates', json_encode($updatedBlockDates));
                            $this->flash('The specified dates have been removed.');
                        }
                        break;

                    case 'add':
                        $newDate = $this->request->getPostParameter('blockeddatenew');                       
                        $storedDates[] = $newDate;
                        $blockDates[] = new DateTime($newDate);
                        $siteSettings->setProperty('blocked-dates', json_encode($storedDates));
                        $this->flash('Blocked off date created.');
                        break;
                }
            }
        }

        $this->template->blockDates = $blockDates;
    }
   
    /**
     * Set the site notice.
     */
    public function siteNotice ()
    {
        $this->addBreadcrumb('admin', 'Administrate');
        $this->setPageTitle('Site notice');
        $settings = $this->getApplication()->siteSettings;
        
        if ($this->request->wasPostedByUser())
        {
            $sanitizer = new Bss_RichText_HtmlSanitizer;
            $settings->siteNotice = $sanitizer->sanitize($this->request->getPostParameter('siteNotice'));
            $this->response->redirect('admin');
        }
        
        $this->template->siteNotice = $settings->siteNotice;
    }

	/**
	 */
	public function clearMemoryCache ()
	{
		if (function_exists('apc_clear_cache'))
		{
			$this->template->cacheExists = true;
			
			if ($this->request->wasPostedByUser())
			{
                set_time_limit(0);
                $this->request->getSession()->release();
                
				$this->userMessage('Cleared op-code and user cache.');
				apc_clear_cache();
				apc_clear_cache('user');
                
                // Force the permission cache to rebuild.
                $this->getAuthorizationManager()->updateCache();
			}
		}
	}
    
    public function cron ()
    {
        $moduleManager = $this->application->moduleManager;
        $xp = $moduleManager->getExtensionPoint('bss:core:cron/jobs');
        $lastRunDates = $xp->getLastRunDates();
        $cronJobMap = array();
        
        if ($this->request->wasPostedByUser() && $this->getPostCommand() === 'invoke')
        {
            $data = $this->getPostCommandData();
            $now = new DateTime;
            
            foreach ($data as $name => $nonce)
            {
                if (($job = $xp->getExtensionByName($name)))
                {
                    $xp->runJob($name);
                    $lastRunDates[$name] = $now;
                }
            }
        }
        
        foreach ($xp->getExtensionDefinitions() as $jobName => $jobInfo)
        {
            $cronJobMap[$jobName] = array(
                'name' => $jobName,
                'instanceOf' => $jobInfo[0],
                'module' => $jobInfo[1],
                'lastRun' => (isset($lastRunDates[$jobName]) ? $lastRunDates[$jobName]->format('c') : 'never'),
            );
        }
        
        $this->template->cronJobs = $cronJobMap;
    }
}
