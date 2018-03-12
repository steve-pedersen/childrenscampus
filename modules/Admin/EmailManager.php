<?php

class Ccheckin_Admin_EmailManager
{
	private $app;
	private $ctrl;
	private $fixedEmail;
	private $fixedName;
	private $subjectLine;
	// private $facultyRole;

	private $schemas = array();

	public function __construct (Bss_Core_Application $app, $ctrl)
	{
		$this->app = $app;
		$this->ctrl = $ctrl;
		$this->fixedEmail = 'pedersen@sfsu.edu'; // $app->getConfiguration()->getProperty('email-default-address', 'children@sfsu.edu');
		$this->fixedName = "The Children's Campus"; // $app->getConfiguration()->getProperty("The Children's Campus");
		$this->subjectLine = "The Children's Campus"; // $app->getConfiguration()->getProperty('facultyRequest.communications.subjectLine', 'Workstation Selection Faculty Select');
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
	public function processEmail ($type, $params)
	{	
		if (!in_array($type, $this->validEmailTypes()))
		{
			return false; 
			exit;
		}

		$this->$type($params);

		// TODO: Add some email logging here ******************************************************
	}

	public function sendCourseRequestedAdmin ($data)
	{
		$this->subjectLine = "Children's Campus: Course Requested";

		$params = array(
			'|%FIRST_NAME%|' => $data['user']->firstName,
			'|%LAST_NAME%|' => $data['user']->lastName,
			'|%COURSE_FULL_NAME%|' => $data['courseRequest']->course->fullName,
			'|%COURSE_SHORT_NAME%|' => $data['courseRequest']->course->shortName,
			'|%SEMESTER%|' => $data['courseRequest']->course->semester->display,
			'|%REQUEST_LINK%|' => $this->generateLink('/admin/courses/queue/' . $data['courseRequest']->id),
			'message_title' => 'Course Requested'
		);

		$body = $this->app->siteSettings->getProperty('email-course-requested-admin');
		if (!$this->hasContent($body))
		{
			$body = '
				<p>A new course has been requested by |%FIRST_NAME%| |%LAST_NAME%|.</p>
				<p>You can go to the <a href="|%REQUEST_LINK%|">manage course requests page</a> 
				by clicking the provided link or copying the url ({$adminLink}) into your browser.</p>
			';
		}

		$this->sendEmail($data['admin'], $params, $body);
	}

	public function sendCourseRequestedTeacher ($data)
	{
		$this->subjectLine = "Children's Campus: Course Requested";

		$params = array(
			'|%FIRST_NAME%|' => $data['user']->firstName,
			'|%LAST_NAME%|' => $data['user']->lastName,
			'|%COURSE_FULL_NAME%|' => $data['courseRequest']->course->fullName,
			'|%COURSE_SHORT_NAME%|' => $data['courseRequest']->course->shortName,
			'|%SEMESTER%|' => $data['courseRequest']->course->semester->display,
			'message_title' => 'Course Requested'
		);

		$body = $this->app->siteSettings->getProperty('email-course-requested-teacher');
		if (!$this->hasContent($body))
		{
			$body = '
				<p>Dear |%FIRST_NAME%| |%LAST_NAME%|,</p>
				<br>
				<p>You have requested a new course:</p>
				<p>|%COURSE_FULL_NAME%|</p>
				<p>|%COURSE_SHORT_NAME%|</p>
				<p>|%SEMESTER%|</p>
				<br>
				<p>Your request will be reviewed and you will be notified of our decision.</p>
			';
		}

		$this->sendEmail($data['user'], $params, $body);
	}

	public function sendCourseAllowedTeacher ($data)
	{
		$this->subjectLine = "Children's Campus: Course Request Approved";

		$params = array(
			'|%FIRST_NAME%|' => $data['user']->firstName,
			'|%LAST_NAME%|' => $data['user']->lastName,
			'|%COURSE_FULL_NAME%|' => $data['course']->fullName,
			'|%COURSE_SHORT_NAME%|' => $data['course']->shortName,
			'|%COURSE_VIEW_LINK%|' => $this->generateLink('/courses/view/' . $data['course']->id),
			'|%OPEN_DATE%|' => $data['course']->semester->openDate,
			'|%CLOSE_DATE%|' => $data['course']->semester->closeDate,
			'message_title' => 'Course Request Approved'
		);

		$body = $this->app->siteSettings->getProperty('email-course-allowed-teacher');
		if (!$this->hasContent($body))
		{
			$body = '
				<p>Dear |%FIRST_NAME%| |%LAST_NAME%|,</p>
				<br>
				<p>Thank you for choosing the Children’s Campus as a quality Early Care and Education Center for your students to conduct observations. The following course has been approved:</p>
					<p>|%COURSE_FULL_NAME%|</p>
					<p>|%COURSE_SHORT_NAME%|</p>
				<br>
				<p>Attached to this email you’ll find our guidelines for student observers, both in the classroom and the observation rooms.  Please see that your students receive a copy of this prior to their first observation at the center.  If you or the students have any questions about their observations they should contact me.</p>
				<br>
				<p>You can access it at <a href="|%COURSE_VIEW_LINK%|">this location</a> using your SFSU ID and password.</p>
				<br>
				<p>Your students have already been automatically enrolled and will be able to make reservations from |%OPEN_DATE%| to |%CLOSE_DATE%|.</p>
			';
		}

		$this->sendEmail($data['user'], $params, $body);
	}


	public function sendCourseAllowedStudents ($data)
	{
		$this->subjectLine = "Children's Campus: New Course Available";

		$params = array(
			'|%FIRST_NAME%|' => $data['user']->firstName,
			'|%LAST_NAME%|' => $data['user']->lastName,
			'|%COURSE_FULL_NAME%|' => $data['course']->fullName,
			'|%COURSE_SHORT_NAME%|' => $data['course']->shortName,
			'|%OPEN_DATE%|' => $data['course']->semester->openDate,
			'|%CLOSE_DATE%|' => $data['course']->semester->closeDate,
			'|%SITE_LINK%|' => $this->generateLink(),
			'message_title' => 'Course Available'
		);

		$body = $this->app->siteSettings->getProperty('email-course-allowed-students');
		if (!$this->hasContent($body))
		{
			$body = '
				<p>Dear |%FIRST_NAME%| |%LAST_NAME%|,</p>
				<br>
				<p>You have been invited to conduct observations at Children’s Campus –- SF State’s quality Early Care and Education Center. Here are the details of the course:</p>
					<p>|%COURSE_FULL_NAME%|</p>
					<p>|%COURSE_SHORT_NAME%|</p>
				<br>
				<p>Attached to this email you’ll find our guidelines for student observers, both in the classroom and the observation rooms. Please see that you read this documentation prior to your first observation at the center. If you have any questions about your observations they should contact me.</p>
				<br>
				<p>You can begin making reservations from |%OPEN_DATE%| until |%CLOSE_DATE%|, which can be done <a href="|%SITE_LINK%|">on the website</a> using your SFSU ID and password to login.</p>
			';
		}

		$this->sendEmail($data['user'], $params, $body);
	}


	public function sendCourseDenied ($data)
	{
		$this->subjectLine = "Children's Campus: Course Request Denied";

		$params = array(
			'|%FIRST_NAME%|' => $data['user']->firstName,
			'|%LAST_NAME%|' => $data['user']->lastName,
			'|%COURSE_FULL_NAME%|' => $data['courseRequest']->course->fullName,
			'|%COURSE_SHORT_NAME%|' => $data['courseRequest']->course->shortName,
			'|%SEMESTER%|' => $data['courseRequest']->course->semester->display,
			'message_title' => 'Course Request Denied'
		);

		$body = $this->app->siteSettings->getProperty('email-course-denied');
		if (!$this->hasContent($body))
		{
			$body = '
				<p>Dear |%FIRST_NAME%| |%LAST_NAME%|,</p>
				<br>
				<p>Children’s Campus has denied the following course you requested:</p>
					<p>|%COURSE_FULL_NAME%|</p>
					<p>|%COURSE_SHORT_NAME%|</p>
					<p>|%SEMESTER%|</p>
				<br>
				<p>If you need any further help, feel free to contact us by responding to this email address</p>
			';
		}

		$this->sendEmail($data['user'], $params, $body);
	}

	public function sendReservationDetails ($data)
	{
		$this->subjectLine = "Children's Campus: Reservation Details";

		$params = array(
			'|%FIRST_NAME%|' => $data['user']->firstName,
			'|%LAST_NAME%|' => $data['user']->lastName,
			'|%RESERVE_DATE%|' => $data['reservation']->startTime->format('M j, Y g:ia'),
			'|%RESERVE_VIEW_LINK%|' => $this->generateLink('/reservations/view/' . $data['reservation']->id),
			'|%RESERVE_CANCEL_LINK%|' => $this->generateLink('/reservations/delete/' . $data['reservation']->id),
			'|%PURPOSE_INFO%|' => $data['purpose_info'],
			'|%ROOM_NAME%|' => $data['room_name'],
			'message_title' => 'Reservation Details'
		);

		$body = $this->app->siteSettings->getProperty('email-reservation-details');
		if (!$this->hasContent($body))
		{
			$body = '
				<p>Dear |%FIRST_NAME%| |%LAST_NAME%|,</p>
				<br>
				<p>Thank you for choosing the Children’s Campus, a quality Early Care and Education Center, for your course observation requirement. Here are the details of your reservation:</p>
					<p>|%RESERVE_DATE%|</p>
					<p>|%PURPOSE_INFO%|</p>
					<p>|%ROOM_NAME%|</p>
					<p><a href="|%RESERVE_VIEW_LINK%|">Full details</a></p>
				<br>
				<p>If you made this reservation by mistake, please <a href="|%RESERVE_CANCEL_LINK%|">cancel your reservation</a>. Observation at the Children’s Campus is a privilege and should not be taken for granted. Thank you for understanding.</p>
			';
		}

		$this->sendEmail($data['user'], $params, $body);
	}

	public function sendReservationReminder ($data)
	{
		$this->subjectLine = "Children's Campus: Reservation Reminder";

		$params = array(
			'|%FIRST_NAME%|' => $data['user']->firstName,
			'|%LAST_NAME%|' => $data['user']->lastName,
			'|%RESERVE_DATE%|' => $data['reservation']->startTime->format('M j, Y g:ia'),
			'|%RESERVE_VIEW_LINK%|' => $this->generateLink('/reservations/view/' . $data['reservation']->id),
			'|%RESERVE_CANCEL_LINK%|' => $this->generateLink('/reservations/delete/' . $data['reservation']->id),
			'|%PURPOSE_INFO%|' => $data['reservation']->observation->purpose->shortDescription,
			'|%ROOM_NAME%|' => $data['reservation']->room->name,
			'message_title' => 'Reservation Reminder'
		);

		$body = $this->app->siteSettings->getProperty('email-reservation-reminder');
		if (!$this->hasContent($body))
		{
			$body = '
				<p>Dear |%FIRST_NAME%| |%LAST_NAME%|,</p>
				<br>
				<p>This is a reminder about your upcoming reservation at Children’s Campus for your course observation requirement. Here are the details of your reservation:</p>
					<p>|%RESERVE_DATE%|</p>
					<p>|%PURPOSE_INFO%|</p>
					<p>|%ROOM_NAME%|</p>
					<p><a href="|%RESERVE_VIEW_LINK%|">Full details</a></p>
				<br>
				<p>If you need to cancel this reservation, please do so immediately <a href="|%RESERVE_CANCEL_LINK%|">by following this link</a>. Observation at the Children’s Campus is a privilege and should not be taken for granted. Thank you for understanding.</p>
			';
		}

		$this->sendEmail($data['user'], $params, $body);
	}

	public function sendReservationMissed ($data)
	{
		$this->subjectLine = "Children's Campus: Reservation Missed";

		$params = array(
			'|%FIRST_NAME%|' => $data['user']->firstName,
			'|%LAST_NAME%|' => $data['user']->lastName,
			'|%RESERVE_DATE%|' => $data['reservation']->startTime->format('M j, Y g:ia'),
			'|%RESERVE_MISSED_LINK%|' => $this->generateLink('/reservations/missed'),
			'|%PURPOSE_INFO%|' => $data['reservation']->observation->purpose->shortDescription,
			'message_title' => 'Reservation Missed'
		);

		$body = $this->app->siteSettings->getProperty('email-reservation-missed');
		if (!$this->hasContent($body))
		{
			$body = '
				<p>Dear |%FIRST_NAME%| |%LAST_NAME%|,</p>
				<br>
				<p>Thank you for choosing the Children’s Campus for your course observation requirement.  Our online reservation system is showing that you missed your last observation appointment.  <u>If you miss one more</u>, the system will remove all future reservations that you’ve created.  If the system removes your reservations, you will be allowed to re-reserve rooms but the same rules will apply.  Please respect that other students need to do observations and are affected by your decision to miss your appointment.</p>
				<p>Here are the details of your missed reservation:</p>
					<p>|%RESERVE_DATE%|</p>
					<p>|%PURPOSE_INFO%|</p>
					<p><a href="|%RESERVE_MISSED_LINK%|">My missed reservations</a></p>
				<br>
				<p>In the future if you’re going to miss your observation appointment, you should cancel your reservation in advance.  Many students want to observe at the CC but cannot due to our full reservation system.</p>
				<br>
				<p>Observation at the Children’s Campus is a privilege and should not be taken for granted.  Thank you for understanding.</p>
			';
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

			// if ($this->fixedEmail)
			// {
			// 	$mail->AddAddress($this->fixedEmail, $this->fixedName);
			// }
			// else
			// {
			// 	$mail->AddAddress($user->emailAddress, $user->fullName);
			// }
			$mail->AddAddress($user->emailAddress, $user->fullName);

			$mail->getTemplate()->message = $preppedText;
			$mail->getTemplate()->messageTitle = $messageTitle;
			$mail->Send();
		}
	}


	private function hasContent ($text)
	{
		return (strlen(strip_tags(trim($text))) > 1);
	}

	private function generateLink ($url='')
	{
		return $this->app->baseUrl($url);
	}

	private function getRequestLink ()
	{
		$template = $this->ctrl->createTemplateInstance();
		$template->disableMasterTemplate();
		$template->linkUrl = $this->app->baseUrl('fr/request');
		return $template->fetch($this->ctrl->getModule()->getResource('link.email.tpl'));
	}


	private function getChoiceWidget ($request)
	{
		$template = $this->ctrl->createTemplateInstance();
		$template->disableMasterTemplate();
		$template->request = $request;
		return $template->fetch($this->ctrl->getModule()->getResource('standard.email.tpl'));
	}


	private function getApprovedWidget ($request)
	{
		$template = $this->ctrl->createTemplateInstance();
		$template->disableMasterTemplate();
		$template->request = $request;
		return $template->fetch($this->ctrl->getModule()->getResource('approved.email.tpl'));
	}


	private function getLastSystem($faculty)
	{
		$lastSystem = 'Not Found';

		if ($faculty && $faculty->systems->count() > 0)
		{
			$lastSystem = $faculty->systems->index(0);
		}

		return $lastSystem;
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
}