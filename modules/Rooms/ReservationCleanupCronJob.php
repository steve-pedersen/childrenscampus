<?php

/**
 * Interface for extensions that wish to execute periodic code.
 * 
 * @author	Steve Pedersen (pedersen@sfsu.edu)
 * @copyright	Copyright &copy; San Francisco State University.
 */
class Ccheckin_Rooms_ReservationCleanupCronJob extends Bss_Cron_Job
{
	const PROCESS_ACTIVE_JOBS_EVERY = 60 * 24; // once a day

    private $userContext = null;

    public function run ($startTime, $lastRun, $timeDelta)
    {
        if ($timeDelta >= self::PROCESS_ACTIVE_JOBS_EVERY)
        {
        	set_time_limit(0);
        	$this->sendReservationMissedNotification();
        	$this->cleanupOldReservations();
            
            return true;
        }
    }

    public function sendReservationMissedNotification ()
    {
        $app = $this->getApplication();
        $schemaManager = $app->schemaManager;
        $emailManager = new Ccheckin_Admin_EmailManager($app);
        $emailManager->setTemplateInstance($this->createTemplateInstance());
        $reservations = $schemaManager->getSchema('Ccheckin_Rooms_Reservation');

        $cond = $reservations->allTrue(
            $reservations->startTime->before(new DateTime('-4 hours')),
            $reservations->missed->isNull()->orIf($reservations->missed->isFalse()),
            $reservations->checkedIn->isNull()->orIf($reservations->checkedIn->isFalse())

        );
        $missed = $reservations->find($cond);
        
        foreach ($missed as $reservation)
        {
            $user = $reservation->account;
            
            $reservation->missed = true;
            $reservation->save();
            
            if ($user->missedReservation)
            {
                // delete all of their upcoming reservations.
                $cond = $reservations->allTrue(
                    $reservations->accountId->equals($user->id),
                    $reservations->startTime->after(new DateTime)
                );
                
                $deleteReservations = $reservations->find($cond);
                
                foreach ($deleteReservations as $dr)
                {
                    $dr->observation->delete();
                    $dr->delete();
                }
            }

            $user->missedReservation = true;
            $user->save();

            $emailData = array();        
            $emailData['reservation'] = $reservation;
            $emailData['user'] = $user;
            $emailManager->processEmail('sendReservationMissed', $emailData);
        }
    }

    public function cleanupOldReservations ()
    {
        $app = $this->getApplication();
        $schemaManager = $app->schemaManager;
        $emailManager = new Ccheckin_Admin_EmailManager($app);
        $emailManager->setTemplateInstance($this->createTemplateInstance());
        $reservations = $schemaManager->getSchema('Ccheckin_Rooms_Reservation');

        // Get the observations which were never checked out and close them.
        $cond = $reservations->allTrue(
        	$reservations->checkedIn->isTrue(),
        	$reservations->startTime->before(new DateTime('-12 hours'))
        );        
        $results = $reservations->find($cond);

        foreach ($results as $reservation)
        {
        	$duration = (int) ($reservation->endTime->format('G') - $reservation->startTime->format('G'));
        	$endTime = (clone ($reservation->observation->startTime))->modify('+' . $duration . 'hours');
            $reservation->observation->duration = (int) $duration; 
            $reservation->observation->endTime = $endTime;
            $reservation->observation->save();
			$reservation->delete();
        }       

        // Get the reservations which are more than a month old and delete them.
        $cond = $reservations->startTime->before(new DateTime('-1 month'));   
        $results = $reservations->find($cond);
        
        foreach ($results as $reservation)
        {
            $observation = $reservation->observation;
			$reservation->delete();
            $observation->delete();
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
