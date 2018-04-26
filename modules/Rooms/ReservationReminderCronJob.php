<?php

/**
 * Interface for extensions that wish to execute periodic code.
 * 
 * @author	Steve Pedersen (pedersen@sfsu.edu)
 * @copyright	Copyright &copy; San Francisco State University.
 */
class Ccheckin_Rooms_ReservationReminderCronJob extends Bss_Cron_Job
{
	const PROCESS_ACTIVE_JOBS_EVERY = 60; // once an hour

    private $userContext = null;

    public function run ($startTime, $lastRun, $timeDelta)
    {
        if ($timeDelta >= self::PROCESS_ACTIVE_JOBS_EVERY)
        {
        	set_time_limit(0);
            $this->sendReservationReminderNotifications();
            
            return true;
        }
    }

    public function sendReservationReminderNotifications ()
    {
        $app = $this->getApplication();
        $schemaManager = $app->schemaManager;
        $emailManager = new Ccheckin_Admin_EmailManager($app);
    	$emailManager->setTemplateInstance($this->createTemplateInstance());
    	$reservations = $schemaManager->getSchema('Ccheckin_Rooms_Reservation');
    	$timeDelta = $app->siteSettings->getProperty('email-reservation-reminder-time', '1 day');

        $cond = $reservations->allTrue(
            $reservations->startTime->before(new DateTime('+' . $timeDelta)),
            $reservations->reminderSent->isFalse()->orIf($reservations->reminderSent->isNull()),
            $reservations->checkedIn->isFalse()->orIf($reservations->checkedIn->isNull()),
            $reservations->missed->isFalse()->orIf($reservations->missed->isNull())
        );
        $upcoming = $reservations->find($cond);
        
        foreach ($upcoming as $reservation)
        {
            $emailData = array();        
            $emailData['reservation'] = $reservation;
            $emailData['user'] = $reservation->account;
            $emailManager->processEmail('sendReservationReminder', $emailData);
            $reservation->reminderSent = true;
            $reservation->save();
        }
    }

    public function getUserContext()
    {
        if ($this->userContext === null)
        {
            $request = @(new Bss_Core_Request($this->getApplication()));
            $response = new Bss_Core_Response($request);
            $this->userContext = new Ccheckin_Master_UserContext($request, $response);
        }

        return $this->userContext;
    }

    public function createTemplateInstance ()
    {
        $tplClass = $this->getTemplateClass();
        $request = @(new Bss_Core_Request($this->getApplication()));
        $response = new Bss_Core_Response($request);

        $inst = new $tplClass ($this, $request, $response);

        return $inst;
    }

    protected function getTemplateClass ()
    {
        return 'Ccheckin_Master_Template';
    }

}
