<?php

/**
 * Interface for extensions that wish to execute periodic code.
 * 
 * @author	Charles O'Sullivan (chsoney@sfsu.edu)
 * @copyright	Copyright &copy; San Francisco State University.
 */
class Ccheckin_Rooms_CronJob extends Bss_Cron_Job
{
	const PROCESS_ACTIVE_JOBS_EVERY = 0; // 2 minutes

    public function run ($startTime, $lastRun, $timeDelta)
    {
        if ($timeDelta >= self::PROCESS_ACTIVE_JOBS_EVERY)
        {
        	$this->execute(); // TODO: test if this is reight.
            // $semesters = $this->schema('Ccheckin_Semesters_Semester');
            // $app = $this->getApplication();

            // $semesterCode = $app->semesterManager->getCurrentSemesterCode();

            // $importer = $app->moduleManager->getExtensionByName('at:ccheckin:classdata/importer', 'classdata');
            // $importer->import($semester->code);

            return true;
        }
    }

	/**
	 * Called by a component which needs to have code executed periodically.
	 * 
	 */
	public function execute ()
    {     
		// Get the observations which were never checked out and close them.
        $reservations = $this->getSchema('Ccheckin_Rooms_Reservation');
        $cond = $reservations->allTrue(
        	$reservations->checkedIn->isTrue(),
        	$reservations->startTime->before(new Date(strtotime('now - 12 hours')))
        );        
        $results = $reservations->find($cond);
        
        foreach ($results as $reservation)
        {
            $reservation->observation->duration = 60;
            $reservation->observation->endTime = new Date($reservation->observation->startTime->getTime() + 3600);
            $reservation->observation->save();
			$reservation->delete();
        }
        
		// Get the reservation which are more than a week old and delete them.
        $cond = $reservation->startTime->before(new Date(strtotime('now - 1 month')));       
        $results = $reservations->find($cond);
        
        foreach ($results as $reservation)
        {
            $reservation->observation->delete();
			$reservation->delete();
        }
		
		// get the reservation that were missed today and the person did not show up.
        $cond = $reservations->allTrue(
			$reservations->startTime->before(new Date(strtotime('now - 4 hours'))),
			$reservations->missed->isFalse(),
			$reservations->checkedIn->isFalse()
        );       
        $results = $reservations->find($cond);
        $template = $this->createEmailTemplate('email_reservation_missed.tpl');			// TODO: Fix this **********************
		
        foreach ($results as $reservation)
		{
			$user = $reservation->account;
			
			$reservation->missed = true;
			$reservation->save();
			
			if ($user->missedReservation)
			{
				// delete all of their upcoming reservations.
				$cond = $reservations->allTrue(
					$reservations->accountId->equals($user->id),
					$reservations->startTime->after(new Date(strtotime('now')))
				);
				
				$deleteReservations = $reservations->find($cond);
				
				foreach ($deleteReservations as $dr)
				{
					$dr->observation->delete();
					$dr->delete();
				}
			}
			else
			{	// TODO: Fix mailer stuff ***********************************************
				$mail = new DivaMailer();
				$mail->Subject = 'Children\'s Campus Checkin: Reservation Missed';
				$mail->Body = $template->render();
				$mail->AltBody = strip_tags($mail->Body);
				$mail->AddAddress($user->email);
				$mail->Send();
				$user->missedReservation = true;
				$user->save();
			}
		}
    }
	
	// TODO: Fix mailer stuff ***********************************************
	private function createEmailTemplate($templateName)
    {
        $template = new DivaTemplate;
        $template->setDefaultResourceDirectory(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'resources');
        $template->setTemplateFile($templateName);
        return $template;
    }
}

?>