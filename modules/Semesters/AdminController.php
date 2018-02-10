<?php

class Ccheckin_Semesters_AdminController extends At_Admin_Controller
{

    public static function getRouteMap ()
    {
        return array(
            'admin/semester/configure' => array('callback' => 'configure'),
        );
    }

    protected function beforeCallback ($callback)
    {
        parent::beforeCallback($callback);
        $this->template->clearBreadcrumbs();
        $this->addBreadcrumb('home', 'Home');
        $this->addBreadcrumb('admin', 'Admin');
        // // if admin and on admin page, don't display 'Contact' sidebar
        // $adminPage = false;
        // $path = $this->request->getFullRequestedUri();
        // if ($this->hasPermission('admin') && (strpos($path, 'admin') !== false))
        // {
        //     $adminPage = true;
        // }
        // $this->template->adminPage = $adminPage; 
    }

    public function configure ()
    {
        $this->setPageTitle('Configure Semesters');
        $semesters = $this->schema('Ccheckin_Semesters_Semester');
        $errors = array();
        $message = '';
        
        if ($this->request->wasPostedByUser())
        {
            if ($command = $this->getPostCommand())
            {
                switch ($command)
                {
                    case 'remove':
                        if ($sems = $this->request->getPostParameter('semesters'))
                        {
                            foreach ($sems as $sem)
                            {                            
                                if ($semester = $semesters->get($sem))
                                {
                                    $semester->delete();
                                }
                            }

                            $message = 'The semesters have been deleted';
                        }
                        break;

                    case 'add':
                        $semester = $semesters->createInstance();
                        
                        $startDate = $this->request->getPostParameter('startDate');
                        if ($startDate)
                        {
                            $semester->startDate = new DateTime($startDate);
                        }
                        
                        $endDate = $this->request->getPostParameter('endDate');
                        if ($endDate)
                        {
                            $semester->endDate = new DateTime($endDate);
                        }
                        
                        $term = $this->request->getPostParameter('term');
                        $semester->display = $term . ' ' . $semester->startDate->format('Y');
                        $codes = array('Spring'=>3, 'Summer'=>5, 'Fall'=>7, 'Winter'=>1);
                        $m = $semester->startDate->format('m');
                        $y = $semester->startDate->format('Y');
                        $y = $y[0] . substr($y, 2);
                        if ($term === 'Winter' && $m = '12') { $y++; }
                        $semester->internal = $y . $codes[$term];

                        $errors = $semester->validate();
                        
                        if (empty($errors))
                        {
                            $semester->save();
                            $message = 'Semester created';
                        }
                        break;
                }
            }
        }
        
        // Get the remaining queued records
        $semesters = $semesters->getAll(array('orderBy' => 'startDate'));
        
        $this->template->semesters = $semesters;
        $this->template->terms = Ccheckin_Semesters_Semester::GetTerms();
        // $this->template->years = Ccheckin_Semesters_Semester::GetYears();
        $this->template->message = $message;
        $this->template->errors = $errors;
    }

    // NOTE: Moved the following functions here from the Welcome_Controller

    protected function setCourses ($user, $term)
    {
        $service = new Ccheckin_ClassData_Service($this->getApplication());
        list($status, $courses) = $service->getUserEnrollments($user->username, $term);

        if ($status < 400)
        {
            $this->template->courses = $courses;
        }
    }

    public function guessActiveSemester ($returnTermCode = true)
    {
        $y = date('Y');
        $m = date('n');
        $d = date('d');

        if ($m < 5)
        {
            $s = 3; // Spring
        }
        elseif ($m < 8)
        {
            $s = 5; // Summer
        }
        else
        {
            $s = 7; // Fall
        }

        $y = '$y';
        $y = $y[0] . substr($y, 2);

        return ($returnTermCode ? '$y$s' : array($y, $s));
    }

    // Creates an array containing the current, previous, and next semesters' term codes & display name
    protected function guessRelevantSemesters ()
    {
        $year = '2' . date('y');
        $month = date('n');
        $day = date('d');
        $semesters = array();

        
        if ($month < 5)
        {   // previous fall, current spring, next summer
            $prevYear = $year - 1;
            $semesters['previous']['termCode'] = $prevYear . '7';
            $semesters['current']['termCode'] = $year . '3';
            $semesters['next']['termCode'] = $year . '5';

            $semesters['previous']['displayName'] = 'Fall ' . $year[0] . '0' . substr($prevYear, 1, 2);
            $semesters['current']['displayName'] = 'Spring ' . $year[0] . '0' . substr($year, 1, 2);
            $semesters['next']['displayName'] = 'Summer ' . $year[0] . '0' . substr($year, 1, 2);
        }
        elseif ($month < 8)
        {   // previous spring, current summer, next fall
            $semesters['previous']['termCode'] = $year . '3';
            $semesters['current']['termCode'] = $year . '5';
            $semesters['next']['termCode'] = $year . '7';

            $semesters['previous']['displayName'] = 'Spring ' . $year[0] . '0' . substr($year, 1, 2);
            $semesters['current']['displayName'] = 'Summer ' . $year[0] . '0' . substr($year, 1, 2);
            $semesters['next']['displayName'] = 'Fall ' . $year[0] . '0' . substr($year, 1, 2);
        }
        else
        {   // previous summer, current fall, next spring
            $nextYear = $year + 1;

            $semesters['previous']['termCode'] = $year . '5';
            $semesters['current']['termCode'] = $year . '7';
            $semesters['next']['termCode'] = $nextYear . '3';

            $semesters['previous']['displayName'] = 'Summer ' . $year[0] . '0' . substr($year, 1, 2);
            $semesters['current']['displayName'] = 'Fall ' . $year[0] . '0' . substr($year, 1, 2);
            $semesters['next']['displayName'] = 'Spring ' . $year[0] . '0' . substr($nextYear, 1, 2);
        }

        return $semesters;
    }

}
