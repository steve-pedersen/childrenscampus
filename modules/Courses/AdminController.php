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
        $this->template->adminPage = $this->hasPermission('admin') && (strpos($this->request->getFullRequestedUri(), 'admin') !== false);
    }

    public function manage ()
    {
        $courses = $this->schema('Ccheckin_Courses_Course');
        $requestSchema = $this->schema('Ccheckin_Courses_Request');
        $facets = $this->schema('Ccheckin_Courses_Facet');
        $purposes = $this->schema('Ccheckin_Purposes_Purpose');
        $message = '';
        
        $coursesIndexTabs = array(
            'active' => 'Active Courses',
            'inactive' => 'Archived Courses',
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
                            
                            $message = 'The selected courses have been archived.';
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
                            
                            $message = 'The selected courses have been activated.';
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
                                // remove an associated course-request, if it exists
                                if ($cr = $requestSchema->findOne($requestSchema->courseId->equals($course->id)))
                                {
                                    $cr->delete();
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
            $cond = $courses->allTrue(
                $courses->active->isTrue(),
                $courses->deleted->isNull()->orIf($courses->deleted->isFalse())
            );
        }
        else
        {
            $crs = $requestSchema->getAll();
            $notDeletedCourseIds = array();
            foreach ($crs as $cr)
            {
                if ($cr->course->deleted === null || !$cr->course->deleted)
                {
                    $notDeletedCourseIds[] = $cr->courseId;
                }
            }
            $cond = $courses->allTrue(
                $courses->active->isFalse(),
                $courses->deleted->isNull()->orIf($courses->deleted->isFalse()),
                $courses->id->notInList($notDeletedCourseIds)
            );
        }

        $this->template->now = new DateTime;
        $this->template->requests = $requestSchema;
        $this->template->coursesIndexTabs = $coursesIndexTabs;
        $this->template->tab = $tab;
        $this->template->message = $message;
        $this->template->courses = $courses->find($cond, array('orderBy' => '-startDate'));
    }


    public function queue ()
    {
        $this->setPageTitle('Courses Queue');
        $courseRequests = $this->schema('Ccheckin_Courses_Request');
        $courseSchema = $this->schema('Ccheckin_Courses_Course');
        $facets = $this->schema('Ccheckin_Courses_Facet');
        $purposes = $this->schema('Ccheckin_Purposes_Purpose');
        $accounts = $this->schema('Bss_AuthN_Account');
        $semesters = $this->schema('Ccheckin_Semesters_Semester');

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

                                    $roles = $this->schema('Ccheckin_AuthN_Role');
                                    $teacherRole = $roles->findOne($roles->name->equals('Teacher'));
                                  
                                    $semester = $semesters->findOne($semesters->startDate->equals($cr->course->startDate));
                                    $authZ = $this->getAuthorizationManager();
                                    $authZ->grantPermission($cr->requestedBy, 'course view', $cr->course);
                                    $courseData = $this->getCourse($cr->course->externalCourseKey);
                                    
                                    foreach ($courseData['instructors'] as $teacher)
                                    {
                                        if ($teacher['id'] != $cr->requestedBy->username)
                                        {   // this is a teacher other than the one who requested the course
                                            $account = $accounts->findOne($accounts->username->equals($teacher['id']));
                                            if (!$account)
                                            {
                                                $account = $accounts->createInstance();
                                                $account->username = $teacher['id'];
                                                $account->firstName = $teacher['first'];
                                                $account->lastName = $teacher['last'];
                                                $account->emailAddress = $teacher['mail'];
                                                $account->roles->add($teacherRole);
                                                $account->save();
                                                $account->roles->save();
                                            }

                                            $authZ->grantPermission($account, 'course view', $cr->course);

                                            $cr->course->enrollments->add($account);
                                            $cr->course->enrollments->setProperty($account, 'term', $semester->internal);
                                            $cr->course->enrollments->setProperty($account, 'role', 'Teacher');
                                            $cr->course->enrollments->setProperty($account, 'enrollment_method', 'Class Data');
                                            $cr->course->enrollments->setProperty($account, 'drop_date', null);
                                        }
                                    }
                                    $cr->course->enrollments->save();

                                    $facet = $cr->course->facets->index(0);                           
                                    $type = strtolower($facet->type->name);

                                    if (!$facet->purpose)
                                    {
                                        $purpose = $this->schema('Ccheckin_Purposes_Purpose')->createInstance();
                                        $purpose->object = $facet;
                                        $purpose->save();
                                    } 

                                    // FacetType is a user editable property... still not the best way to differentiate
                                    if ((strpos($type, 'participation') !== false) || (strpos($type, 'participate') !== false))
                                    {
                                        $facet->addUsers($cr->course->students, false);
                                    }
                                    elseif ((strpos($type, 'observation') !== false) || (strpos($type, 'observe') !== false))
                                    {
                                        $facet->addUsers($cr->course->students, true);
                                    }

                                    $this->sendCourseAllowedTeacherNotification($cr->course, $cr->requestedBy);
                                    $this->sendCourseAllowedStudentsNotification($cr->course);    

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

                                    $this->sendCourseDeniedNotification($cr->course, $cr->requestedBy);

                                    $denied[] = $cr->course->fullName;
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

        // Get the remaining queued records that aren't deleted and check for duplicates
        $deletedCourses = $courseSchema->find($courseSchema->deleted->isTrue());
        $dcs = array();       
        foreach ($deletedCourses as $dc)
        {
            $dcs[] = $dc->id;
        }
        $crs = $courseRequests->find($courseRequests->courseId->notInList($dcs), array('orderBy' => 'requestDate'));     
        $duplicates = array();
        // add all courses that have already been approved first.
        $cs = $courseSchema->find($courseSchema->active->isTrue());
        foreach ($cs as $c)
        {
            $temp = $c->shortName . $c->facetType->name;
            if (!in_array($temp, $duplicates))
            {
                $duplicates[] = $temp;
            }
        }
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
        $this->addBreadcrumb('admin/courses', 'Manage Courses');
        $id = $this->requireExists($this->getRouteVariable('id'));
        $courses = $this->schema('Ccheckin_Courses_Course');
        $courseFacets = $this->schema('Ccheckin_Courses_Facet');
        $courseFacetTypes = $this->schema('Ccheckin_Courses_FacetType');
        $accounts = $this->schema('Bss_AuthN_Account');
        $authZ = $this->getAuthorizationManager();

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
                            $course->active = ($course->active || $new) ? true : false;
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

    protected function sendCourseAllowedTeacherNotification ($course, $account)
    {
        $emailManager = new Ccheckin_Admin_EmailManager($this->getApplication(), $this);
        $emailData = array();        
        $emailData['course'] = $course;
        $emailData['user'] = $account;
        $emailManager->processEmail('sendCourseAllowedTeacher', $emailData);
    }

    protected function sendCourseAllowedStudentsNotification ($course)
    {
        $emailManager = new Ccheckin_Admin_EmailManager($this->getApplication(), $this);
        $emailData = array();        
        $emailData['course'] = $course;
        $emailData['user'] = $course->students;
        $emailManager->processEmail('sendCourseAllowedStudents', $emailData);
    }
    
    protected function sendCourseDeniedNotification ($course, $account)
    {
        $emailManager = new Ccheckin_Admin_EmailManager($this->getApplication(), $this);
        $emailData = array();        
        $emailData['course'] = $course;
        $emailData['user'] = $account;
        $emailManager->processEmail('sendCourseDenied', $emailData);
    }

    public function createEmailTemplate ()
    {
        $template = $this->createTemplateInstance();
        $template->setMasterTemplate(Bss_Core_PathUtils::path(dirname(__FILE__), 'resources', 'email.html.tpl'));
        return $template;
    }

    public function createEmailMessage ($contentTemplate = null)
    {
        $message = new Bss_Mailer_Message($this->getApplication());
        
        if ($contentTemplate)
        {
            $tpl = $this->createEmailTemplate();
            $message->setTemplate($tpl, $this->getModule()->getResource($contentTemplate));
        }
        
        return $message;
    }

    protected function getCourse ($id)
    {
        $service = new Ccheckin_ClassData_Service($this->getApplication());
        list($status, $course) = $service->getCourse($id);
        
        if ($status < 400)
        {
            return $course;
        }
        return false;
    }

}

