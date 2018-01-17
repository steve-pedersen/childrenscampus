<?php

/**
 * Provides the welcome page.
 * 
 * @author 		Daniel A. Koepke (dkoepke@sfsu.edu)
 * @author      Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; California State University Trustees.
 */
class Ccheckin_Welcome_Controller extends Ccheckin_Master_Controller
{
	public static function getRouteMap ()
	{
		return array(
				'home' => array('callback' => 'index'),
		);
	}
	
	public function index ()
	{
		$app = $this->getApplication();

		if ($user = $this->getUserContext()->getAccount())
		{
			// redirect to /home or is that not needed anymore?
		}

		$siteSettings = $this->getApplication()->siteSettings;

		if ($welcomeText = $siteSettings->getProperty('welcome-text'))
		{
			$this->template->welcomeText = $welcomeText;
		}
	}

	protected function setCourses ($user, $term)
	{
		$service = new Ccheckin_ClassData_Service($this->getApplication());
		list($status, $courses) = $service->getUserEnrollments($user->username, $term);

		if ($status < 400)
		{
			$this->template->courses = $courses;
		}
		//echo '<pre>'; var_dump($term['displayName']); die;
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
		// echo '<pre>'; var_dump($y); die;
		$y = $y[0] . substr($y, 2);

		//echo '<pre>'; var_dump($y); die;

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
		{ 	// previous spring, current summer, next fall
			$semesters['previous']['termCode'] = $year . '3';
			$semesters['current']['termCode'] = $year . '5';
			$semesters['next']['termCode'] = $year . '7';

			$semesters['previous']['displayName'] = 'Spring ' . $year[0] . '0' . substr($year, 1, 2);
			$semesters['current']['displayName'] = 'Summer ' . $year[0] . '0' . substr($year, 1, 2);
			$semesters['next']['displayName'] = 'Fall ' . $year[0] . '0' . substr($year, 1, 2);
		}
		else
		{ 	// previous summer, current fall, next spring
			$nextYear = $year + 1;

			$semesters['previous']['termCode'] = $year . '5';
			$semesters['current']['termCode'] = $year . '7';
			$semesters['next']['termCode'] = $nextYear . '3';

			$semesters['previous']['displayName'] = 'Summer ' . $year[0] . '0' . substr($year, 1, 2);
			$semesters['current']['displayName'] = 'Fall ' . $year[0] . '0' . substr($year, 1, 2);
			$semesters['next']['displayName'] = 'Spring ' . $year[0] . '0' . substr($nextYear, 1, 2);
		}
		//die($semesters['current']['displayName']);
		return $semesters;
	}

}
