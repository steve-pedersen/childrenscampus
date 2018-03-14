<?php

class Ccheckin_Admin_EmailManager
{
	private $app;
	private $ctrl;
	private $fromEmail;
	private $fromName;
	private $subjectLine;
	// private $facultyRole;

	private $schemas = array();

	public function __construct (Bss_Core_Application $app, $ctrl)
	{
		$this->app = $app;
		$this->ctrl = $ctrl;
		$this->fromEmail = $app->getConfiguration()->getProperty('email-default-address', 'children@sfsu.edu');
		$this->fromName = "The Children's Campus"; 
		$this->subjectLine = "The Children's Campus";
	}

	public function validEmailTypes ()
	{
		$types = array(
			'sendCourseRequestedAdmin',
			'sendCourseRequestedTeacher',
			'sendCourseAllowedTeacher',
			'sendCourseAllowedStudents',
			'sendCourseDenied',
			'sendReservationDetails',
			'sendReservationReminder',
			'sendReservationMissed'
		);

		return $types;
	}

 /**
	* Determines which email function to call.
	*
	* @param $type string to be used as name of function call (e.g. 'sendCourseRequested') 
	* @param $params array of variables needed by called function
	*/
	public function processEmail ($type, $params, $test=false)
	{	
		if (!in_array($type, $this->validEmailTypes()))
		{
			return false; 
			exit;
		}

		$this->$type($params, $test);

		// TODO: Add some email logging here ******************************************************
	}

	public function sendCourseRequestedAdmin ($data, $test)
	{
		$this->subjectLine = "Children's Campus: Course Requested";

		if (!$test)
		{
			$courseReq = $this->getSchema('Ccheckin_Courses_Request')->get($data['courseRequest']->id);
		}
		
		$params = array(
			'|%FIRST_NAME%|' => $data['user']->firstName,
			'|%LAST_NAME%|' => $data['user']->lastName,
			'|%COURSE_FULL_NAME%|' => (!$test ? $courseReq->course->fullName : $data['courseRequest']->fullName),
			'|%COURSE_SHORT_NAME%|' => (!$test ? $courseReq->course->shortName : $data['courseRequest']->shortName),
			'|%SEMESTER%|' => (!$test ? $courseReq->course->semester->display : $data['courseRequest']->semester),
			'|%REQUEST_LINK%|' => $this->generateLink('/admin/courses/queue/' . $data['courseRequest']->id, true, 'View Course Request'),
			'message_title' => 'Course Requested'
		);

		$body = $this->app->siteSettings->getProperty('email-course-requested-admin');
		if (!$this->hasContent($body))
		{
			$body = $this->defaultEmails[__FUNCTION__];
		}
		
		$this->sendEmail($data['user'], $params, $body);
	}

	public function sendCourseRequestedTeacher ($data, $test)
	{
		$this->subjectLine = "Children's Campus: Course Requested";
		if (!$test)
		{
			$courseReq = $this->getSchema('Ccheckin_Courses_Request')->get($data['courseRequest']->id);
		}

		$params = array(
			'|%FIRST_NAME%|' => $data['user']->firstName,
			'|%LAST_NAME%|' => $data['user']->lastName,
			'|%COURSE_FULL_NAME%|' => (!$test ? $courseReq->course->fullName : $data['courseRequest']->fullName),
			'|%COURSE_SHORT_NAME%|' => (!$test ? $courseReq->course->shortName : $data['courseRequest']->shortName),
			'|%SEMESTER%|' => (!$test ? $courseReq->course->semester->display : $data['courseRequest']->semester),
			'message_title' => 'Course Requested'
		);

		$body = $this->app->siteSettings->getProperty('email-course-requested-teacher');
		if (!$this->hasContent($body))
		{
			$body = $this->defaultEmails[__FUNCTION__];
		}
		
		$this->sendEmail($data['user'], $params, $body);
	}

	public function sendCourseAllowedTeacher ($data, $test)
	{
		$this->subjectLine = "Children's Campus: Course Request Approved";
		if (!$test)
		{
			$course = $this->getSchema('Ccheckin_Courses_Course')->get($data['course']->id);
		}

		$params = array(
			'|%FIRST_NAME%|' => $data['user']->firstName,
			'|%LAST_NAME%|' => $data['user']->lastName,
			'|%COURSE_FULL_NAME%|' => (!$test ? $course->fullName : $data['course']->fullName),
			'|%COURSE_SHORT_NAME%|' => (!$test ? $course->shortName : $data['course']->shortName),
			'|%COURSE_VIEW_LINK%|' => $this->generateLink('/courses/view/'.$data['course']->id, true, 'View Course'),
			'|%OPEN_DATE%|' => (!$test ? $course->semester->openDate : $data['course']->openDate)->format('M j, Y'),
			'|%CLOSE_DATE%|' => (!$test ? $course->semester->closeDate : $data['course']->closeDate)->format('M j, Y'),
			'message_title' => 'Course Request Approved'
		);

		$body = $this->app->siteSettings->getProperty('email-course-allowed-teacher');
		if (!$this->hasContent($body))
		{
			$body = $this->defaultEmails[__FUNCTION__];
		}

		$this->sendEmail($data['user'], $params, $body);
	}


	public function sendCourseAllowedStudents ($data, $test)
	{
		$this->subjectLine = "Children's Campus: New Course Available";
		if (!$test)
		{
			$course = $this->getSchema('Ccheckin_Courses_Course')->get($data['course']->id);
		}

		$params = array(
			'|%FIRST_NAME%|' => $data['user']->firstName,
			'|%LAST_NAME%|' => $data['user']->lastName,
			'|%COURSE_FULL_NAME%|' => (!$test ? $course->fullName : $data['course']->fullName),
			'|%COURSE_SHORT_NAME%|' => (!$test ? $course->shortName : $data['course']->shortName),
			'|%OPEN_DATE%|' => (!$test ? $course->semester->openDate : $data['course']->openDate)->format('M j, Y'),
			'|%CLOSE_DATE%|' => (!$test ? $course->semester->closeDate : $data['course']->closeDate)->format('M j, Y'),
			'|%SITE_LINK%|' => $this->generateLink('', true, "The Children's Campus"),
			'message_title' => 'Course Available'
		);

		$body = $this->app->siteSettings->getProperty('email-course-allowed-students');
		if (!$this->hasContent($body))
		{
			$body = $this->defaultEmails[__FUNCTION__];
		}

		$this->sendEmail($data['user'], $params, $body);
	}


	public function sendCourseDenied ($data, $test)
	{
		$this->subjectLine = "Children's Campus: Course Request Denied";
		if (!$test)
		{
			$course = $this->getSchema('Ccheckin_Courses_Course')->get($data['course']->id);
		}

		$params = array(
			'|%FIRST_NAME%|' => $data['user']->firstName,
			'|%LAST_NAME%|' => $data['user']->lastName,
			'|%COURSE_FULL_NAME%|' => (!$test ? $course->fullName : $data['course']->fullName),
			'|%COURSE_SHORT_NAME%|' => (!$test ? $course->shortName : $data['course']->shortName),
			'|%SEMESTER%|' => (!$test ? $course->semester->display : $data['course']->semester),
			'message_title' => 'Course Request Denied'
		);

		$body = $this->app->siteSettings->getProperty('email-course-denied');
		if (!$this->hasContent($body))
		{
			$body = $this->defaultEmails[__FUNCTION__];
		}

		$this->sendEmail($data['user'], $params, $body);
	}

	public function sendReservationDetails ($data, $test)
	{
		$this->subjectLine = "Children's Campus: Reservation Details";
		if (!$test)
		{
			$reservation = $this->getSchema('Ccheckin_Rooms_Reservation')->get($data['reservation']->id);
		}

		$params = array(
			'|%FIRST_NAME%|' => $data['user']->firstName,
			'|%LAST_NAME%|' => $data['user']->lastName,
			'|%RESERVE_DATE%|' => (!$test ? $reservation->startTime : $data['reservation']->startTime)->format('M j, Y g:ia'),
			'|%RESERVE_VIEW_LINK%|' => $this->generateLink('/reservations/view/'.$data['reservation']->id, true, 'View Reservation'),
			'|%RESERVE_CANCEL_LINK%|' => $this->generateLink('/reservations/delete/'.$data['reservation']->id, true, 'Cancel Reservation'),
			'|%PURPOSE_INFO%|' => (!$test ? $reservation->observation->purpose->shortDescription : $data['reservation']->purpose),
			'|%ROOM_NAME%|' => (!$test ? $reservation->room->name : $data['reservation']->room),
			'message_title' => 'Reservation Details'
		);

		$body = $this->app->siteSettings->getProperty('email-reservation-details');
		if (!$this->hasContent($body))
		{
			$body = $this->defaultEmails[__FUNCTION__];
		}
		
		$this->sendEmail($data['user'], $params, $body);
	}

	public function sendReservationReminder ($data, $test)
	{
		$this->subjectLine = "Children's Campus: Reservation Reminder";
		if (!$test)
		{
			$reservation = $this->getSchema('Ccheckin_Rooms_Reservation')->get($data['reservation']->id);
		}

		$params = array(
			'|%FIRST_NAME%|' => $data['user']->firstName,
			'|%LAST_NAME%|' => $data['user']->lastName,
			'|%RESERVE_DATE%|' => (!$test ? $reservation->startTime : $data['reservation']->startTime)->format('M j, Y g:ia'),
			'|%RESERVE_VIEW_LINK%|' => $this->generateLink('/reservations/view/'.$data['reservation']->id, true, 'View Reservation'),
			'|%RESERVE_CANCEL_LINK%|' => $this->generateLink('/reservations/delete/'.$data['reservation']->id, true, 'Cancel Reservation'),
			'|%PURPOSE_INFO%|' => (!$test ? $reservation->observation->purpose->shortDescription : $data['reservation']->purpose),
			'|%ROOM_NAME%|' => (!$test ? $reservation->room->name : $data['reservation']->room),
			'message_title' => 'Reservation Reminder'
		);

		$body = $this->app->siteSettings->getProperty('email-reservation-reminder');
		if (!$this->hasContent($body))
		{
			$body = $this->defaultEmails[__FUNCTION__];
		}

		$this->sendEmail($data['user'], $params, $body);
	}

	public function sendReservationMissed ($data, $test)
	{
		$this->subjectLine = "Children's Campus: Reservation Missed";
		if (!$test)
		{
			$reservation = $this->getSchema('Ccheckin_Rooms_Reservation')->get($data['reservation']->id);
		}

		$params = array(
			'|%FIRST_NAME%|' => $data['user']->firstName,
			'|%LAST_NAME%|' => $data['user']->lastName,
			'|%RESERVE_DATE%|' => (!$test ? $reservation->startTime : $data['reservation']->startTime)->format('M j, Y g:ia'),
			'|%RESERVE_MISSED_LINK%|' => $this->generateLink('/reservations/missed', true, 'Missed Reservations'),
			'|%PURPOSE_INFO%|' => (!$test ? $reservation->observation->purpose->shortDescription : $data['reservation']->purpose),
			'message_title' => 'Reservation Missed'
		);

		$body = $this->app->siteSettings->getProperty('email-reservation-missed');
		if (!$this->hasContent($body))
		{
			$body = $this->defaultEmails[__FUNCTION__];
		}

		$this->sendEmail($data['user'], $params, $body);
	}

	public function sendEmail($user, $params, $templateText, $templateFile=null)
	{
		if ($this->hasContent($templateText))
		{
			$messageTitle = $params['message_title'];
			$preppedText = strtr($templateText, $params);			
			$templateFileName = $templateFile ?? 'emailBody.email.tpl';
			$mail = $this->ctrl->createEmailMessage($templateFileName);
			$mail->Subject = $this->subjectLine;

			$mail->set('From', $this->fromEmail);
			$mail->set('FromName', $this->fromName);
			$mail->set('Sender', $this->fromEmail);
			$mail->AddAddress($user->emailAddress, $user->fullName);
			$mail->AddReplyTo($this->fromEmail, $this->fromName);

			// AddAttachment($path, $name = '')
			// $mail->AddAttachment(
			// 	glue_path(
			// 		dirname(__FILE__), 'resources', 'Guidelines_for_SF_State_Student_Participants_2016-17.pdf'), 
			// 	'Guidelines for SF State Student Participants 2016-2017.pdf'
			// );
			
			$mail->getTemplate()->message = $preppedText;
			$mail->getTemplate()->messageTitle = $messageTitle;
			$mail->getTemplate()->signature = $this->app->siteSettings->getProperty('email-signature', "<br><p>&nbsp;&nbsp;&mdash;The Children's Campus</p>");
			
			$mail->Send();
		}
	}

	private function hasContent ($text)
	{
		return (strlen(strip_tags(trim($text))) > 1);
	}

	private function generateLink ($url='', $asAnchor=true, $linkText='')
	{
		$href = $this->app->baseUrl($url);
		if ($asAnchor)
		{
			$text = $linkText ?? $href;
			return '<a href="' . $href . '">' . $text . '</a>';
		}
		
		return $href;
	}

	private function getSchema($schemaName)
	{
		if (!isset($this->schemas[$schemaName]))
		{
			$schemaManager = $this->app->schemaManager;
			$this->schemas[$schemaName] = $schemaManager->getSchema($schemaName);
		}

		return $this->schemas[$schemaName];
	}

	public $defaultEmails = array(
		'sendCourseRequestedAdmin' => '
				<p>A new course has been requested by |%FIRST_NAME%| |%LAST_NAME%|.</p>
				<p>Click here to view your requested course: |%REQUEST_LINK%|</p>
				<p>You can go to the manage course requests page by clicking the provided link or copying the above URL into your browser.</p>',
		'sendCourseRequestedTeacher' => '
				<p>Dear |%FIRST_NAME%| |%LAST_NAME%|,</p>
				<br>
				<p>You have requested a new course:</p>
				<ul>
					<li>|%COURSE_FULL_NAME%|</li>
					<li>|%COURSE_SHORT_NAME%|</li>
					<li>|%SEMESTER%|</li>
				</ul>
				<br>
				<p>Your request will be reviewed and you will be notified of our decision.</p>',
		'sendCourseAllowedTeacher' => '
				<p>Dear |%FIRST_NAME%| |%LAST_NAME%|,</p>
				<br>
				<p>Thank you for choosing the Children’s Campus for your students to conduct observations. The following course has been approved:</p>
				<ul>
					<li>|%COURSE_FULL_NAME%|</li>
					<li>|%COURSE_SHORT_NAME%|</li>
				</ul>
				<br>
				<p>Attached to this email you’ll find our guidelines for student observers, both in the classroom and the observation rooms.  Please see that your students receive a copy of this prior to their first observation at the center.  If you or the students have any questions about their observations they should contact us.</p>
				<br>
				<p>You can access it here |%COURSE_VIEW_LINK%|, using your SFSU ID and password.</p>
				<br>
				<p>Your students have already been automatically enrolled and will be able to make reservations from |%OPEN_DATE%| to |%CLOSE_DATE%|.</p>',
		'sendCourseAllowedStudents' => '
				<p>Dear |%FIRST_NAME%| |%LAST_NAME%|,</p>
				<br>
				<p>You have been invited to conduct observations at Children’s Campus –- SF State’s quality Early Care and Education Center. Here are the details of the course:</p>
				<ul>
					<li>|%COURSE_FULL_NAME%|</li>
					<li>|%COURSE_SHORT_NAME%|</li>
				</ul>
				<br>
				<p>Attached to this email you’ll find our guidelines for student observers, both in the classroom and the observation rooms. Please see that you read this documentation prior to your first observation at the center. If you have any questions about your observations they should contact me.</p>
				<br>
				<p>You can begin making reservations from |%OPEN_DATE%| until |%CLOSE_DATE%|, which can be done here |%SITE_LINK%|, using your SFSU ID and password to login.</p>',
		'sendCourseDenied' => '
				<p>Dear |%FIRST_NAME%| |%LAST_NAME%|,</p>
				<br>
				<p>Children’s Campus has denied the following course you requested:</p>
				<ul>
					<li>|%COURSE_FULL_NAME%|</li>
					<li>|%COURSE_SHORT_NAME%|</li>
					<li>|%SEMESTER%|</li>
				</ul>
				<br>
				<p>If you need any further help, feel free to contact us by responding to this email address.</p>',
		'sendReservationDetails' => '
				<p>Dear |%FIRST_NAME%| |%LAST_NAME%|,</p>
				<br>
				<p>Thank you for choosing the Children’s Campus for your course observation requirement. Here are the details of your reservation:</p>				
				<ul>
					<li>|%RESERVE_DATE%|</li>
					<li>|%PURPOSE_INFO%|</li>
					<li>|%ROOM_NAME%|</li>
					<li>|%RESERVE_VIEW_LINK%|</li>
				</ul>
				<br>
				<p>If you made this reservation by mistake, please cancel your reservation here |%RESERVE_CANCEL_LINK%|. Observation at the Children’s Campus is a privilege and should not be taken for granted. Thank you for understanding.</p>',
		'sendReservationReminder' => '
				<p>Dear |%FIRST_NAME%| |%LAST_NAME%|,</p>
				<br>
				<p>This is a reminder about your upcoming reservation at Children’s Campus for your course observation requirement. Here are the details of your reservation:</p>
				<ul>
					<li>|%RESERVE_DATE%|</li>
					<li>|%PURPOSE_INFO%|</li>
					<li>|%ROOM_NAME%|</li>
					<li>|%RESERVE_VIEW_LINK%|</li>
				</ul>
				<br>
				<p>If you need to cancel this reservation, please do so immediately here |%RESERVE_CANCEL_LINK%|. Observation at the Children’s Campus is a privilege and should not be taken for granted. Thank you for understanding.</p>',
		'sendReservationMissed' => '
				<p>Dear |%FIRST_NAME%| |%LAST_NAME%|,</p>
				<br>
				<p>Thank you for choosing the Children’s Campus for your course observation requirement.  Our online reservation system is showing that you missed your last observation appointment.  <u>If you miss one more</u>, the system will remove all future reservations that you’ve created.  If the system removes your reservations, you will be allowed to re-reserve rooms but the same rules will apply.  Please respect that other students need to do observations and are affected by your decision to miss your appointment.</p>
				<p>Here are the details of your missed reservation:</p>
				<ul>
					<li>|%RESERVE_DATE%|</li>
					<li>|%PURPOSE_INFO%|</li>
					<li>|%RESERVE_MISSED_LINK%|</li>
				</ul>
				<br>
				<p>In the future if you’re going to miss your observation appointment, you should cancel your reservation in advance.  Many students want to observe at the CC but cannot due to our full reservation system.</p>
				<p>Observation at the Children’s Campus is a privilege and should not be taken for granted.  Thank you for understanding.</p>'
	);
}