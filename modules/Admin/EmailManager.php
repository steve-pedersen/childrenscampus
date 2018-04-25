<?php

class Ccheckin_Admin_EmailManager
{
	private $app;
	private $ctrl;
	private $fromEmail;
	private $fromName;
	private $testEmail;
	private $testingOnly;
	private $subjectLine;
	private $attachments;
	private $ccRequest;
	private $emailLogId;
	private $templateInstance;

	private $schemas = array();

	public function __construct (Bss_Core_Application $app, $ctrl=null)
	{
		$this->app = $app;
		$this->ctrl = $ctrl;	// phasing this out...
		$this->fromEmail = $app->getConfiguration()->getProperty('email-default-address', 'children@sfsu.edu');
		$this->fromName = "The Children's Campus";
		$this->testingOnly = $app->getConfiguration()->getProperty('email-testing-only', false);
		$this->testEmail = $app->getConfiguration()->getProperty('email-test-address');
		$this->subjectLine = "The Children's Campus";
		$this->attachments = array();
		$this->ccRequest = false;
	}

	public function validEmailTypes ()
	{
		$types = array(
			'sendNewAccount',
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

	public function setTemplateInstance ($inst)
	{
		$this->templateInstance = $inst;
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

		$fileType = lcfirst(str_replace('send', '', $type));
		$this->attachments = $this->getEmailAttachments($fileType);	
		$this->ccRequest = ($type === 'sendCourseRequestedAdmin');

		$emailLog = $this->getSchema('Ccheckin_Admin_EmailLog')->createInstance();
		$emailLog->type = ($test ? 'TEST: ' : '') . $type;
		$emailLog->creationDate = new DateTime;
		$emailLog->save();
		$this->emailLogId = $emailLog->id;

		// send email based on type
		$this->$type($params, $test);
	}

    public function getEmailAttachments ($emailType)
    {
        $attachments = array();
        $files = $this->getSchema('Ccheckin_Admin_File')->getAll();
        foreach ($files as $file)
        {
            if (in_array($emailType, $file->attachedEmailKeys))
            {
                $attachments[] = $file;
            }
        }

        return $attachments;
    }

	public function sendNewAccount ($data, $test)
	{
		$this->subjectLine = "Children's Campus Check-in: An account has been created for you";

		$params = array(
			'|%FIRST_NAME%|' => $data['user']->firstName,
			'|%LAST_NAME%|' => $data['user']->lastName,
			'|%SITE_LINK%|' => $this->generateLink('', true, "The Children's Campus"),
			'message_title' => 'An account has been created for you.'
		);

		$body = $this->app->siteSettings->getProperty('email-new-account');
		if (!$this->hasContent($body))
		{
			$body = $this->defaultEmails[__FUNCTION__];
		}
		
		$this->sendEmail($data['user'], $params, $body);
	}

	public function sendCourseRequestedAdmin ($data, $test)
	{
		$this->subjectLine = "Children's Campus Check-in: Course Requested";

		if (!$test)
		{
			$courseReq = $this->getSchema('Ccheckin_Courses_Request')->get($data['courseRequest']->id);
		}
		
		$params = array(
			'|%FIRST_NAME%|' => $data['requestingUser']->firstName,
			'|%LAST_NAME%|' => $data['requestingUser']->lastName,
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
		$this->subjectLine = "Children's Campus Check-in: Course Requested";
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
		$this->subjectLine = "Children's Campus Check-in: Course Request Approved";
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
		$this->subjectLine = "Children's Campus Check-in: New Course Available";
		if (!$test)
		{
			$course = $this->getSchema('Ccheckin_Courses_Course')->get($data['course']->id);
		}

		$params = array(
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
		$this->subjectLine = "Children's Campus Check-in: Course Request Denied";
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
		$this->subjectLine = "Children's Campus Check-in: Reservation Details";
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
		$this->subjectLine = "Children's Campus Check-in: Reservation Reminder";
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
		$this->subjectLine = "Children's Campus Check-in: Reservation Missed";
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
			$mail = ($this->templateInstance ? $this->createEmailMessage($templateFileName) : $this->ctrl->createEmailMessage($templateFileName));
			$mail->Subject = $this->subjectLine;

			$mail->set('From', $this->fromEmail);
			$mail->set('FromName', $this->fromName);
			$mail->set('Sender', $this->fromEmail);
			$mail->AddReplyTo($this->fromEmail, $this->fromName);

			$recipients = array();

			if ($this->testingOnly && $this->testEmail)
			{
				// send only to testing address
				$mail->AddAddress($this->testEmail, "Testing Children's Campus");
				$recipients[] = -1;
			}
			elseif (count($user) > 1)
			{
				// send to multiple recipients
				foreach ($user as $recipient)
				{
					$recipient = is_array($recipient) ? array_shift($recipient) : $recipient;
					$mail->AddAddress($recipient->emailAddress, $recipient->fullName);
					$recipients[] = $recipient->id;
				}
			}
			else
			{
				// send to a single specified recipient
				if (is_array($user) && array_shift($user))
				{
					$user = array_shift($user);
				}
				
				if ($user)
				{
					$email = $user->emailAddress ?? '';
					$name = ($user->fullName ?? $user->displayName) ?? (($user->firstName . ' ' . $user->lastName) ?? '');
					$id = $user->id;				
				}
				else
				{
					$email = '';
					$name = '';
					$id = -1;				
				}

				$mail->AddAddress($email, $name);
				$recipients[] = $id;
			}

			foreach ($this->attachments as $attachment)
			{
				$title = isset($attachment->title) ? $attachment->title : $attachment->remoteName;
				$mail->AddAttachment($attachment->getLocalFilename(true), $title);
			}
			if ($this->ccRequest && !($this->testingOnly && $this->testEmail))
			{
				$mail->AddAddress($this->fromEmail, $this->fromName);
			}

			$mail->getTemplate()->message = $preppedText;
			$mail->getTemplate()->messageTitle = $messageTitle;
			$mail->getTemplate()->signature = $this->app->siteSettings->getProperty('email-signature', "<br><p>&nbsp;&nbsp;&mdash;The Children's Campus</p>");
			
			$success = $mail->Send();
        
			// finish email log
			$emailLog = $this->getSchema('Ccheckin_Admin_EmailLog')->get($this->emailLogId);
			$emailLog->recipients = implode(',', $recipients);
			$emailLog->subject = $this->subjectLine;
			$emailLog->body = $preppedText;
			$emailLog->attachments = $this->attachments;
			$emailLog->success = $success;
			$emailLog->save();
		}
	}

    public function createEmailTemplate ()
    {
        $template = $this->templateInstance;
        $template->setMasterTemplate(Bss_Core_PathUtils::path(dirname(__FILE__), 'resources', 'email.html.tpl'));
        return $template;
    }
    
    
    public function createEmailMessage ($contentTemplate = null)
    {
        $message = new Bss_Mailer_Message($this->app);

        if ($contentTemplate)
        {
            $tpl = $this->createEmailTemplate();
            if ($this->ctrl)
            {
            	$message->setTemplate($tpl, $this->ctrl->getModule()->getResource($contentTemplate));
            }
            else
            {
            	$message->setTemplate($tpl, $this->app->moduleManager->getModule('at:ccheckin:master')->getResource($contentTemplate));
            }

        }
        
        return $message;
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

		'sendNewAccount' => '
<p>|%FIRST_NAME%| |%LAST_NAME%|,</p><br>
<p>Children\'s Campus has created an account for you in our Check-In web application.
You can access it at |%SITE_LINK%| using your SFSU ID and password.</p>
<p>If you need any further help, feel free to contact us by responding to this email address.</p>',


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
<p>Dear Student,</p>
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