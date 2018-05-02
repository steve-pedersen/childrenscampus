<?php

class Ccheckin_Rooms_Controller extends Ccheckin_Master_Controller
{

    const START_HOUR = 8;
    const END_HOUR = 19;

    public static function getRouteMap ()
    {
        return array(
            'reservations'              => array('callback' => 'index'),
            'reservations/view/:id'     => array('callback' => 'view', ':id' => '[0-9]+'),
            'reservations/upcoming'     => array('callback' => 'upcoming'),
            'reservations/missed'       => array('callback' => 'missed'),
            'reservations/observations' => array('callback' => 'observations'),
            'reservations/override/:id' => array('callback' => 'override', ':id' => '[0-9]+'),
            'reservations/delete/:id'   => array('callback' => 'delete', ':id' => '[0-9]+'),
            'reservations/schedule'     => array('callback' => 'schedule'),
            'reservations/schedule/:id' => array('callback' => 'schedule', ':id' => '[0-9]+'),
            'reservations/schedule/:id/:year/:month/:day'       => array('callback' => 'schedule',':id'=>'[0-9]+',':year'=>'[0-9]+',':month'=>'[0-9]+',':day'=>'[0-9]+'),
            'reservations/week/:id'     => array('callback' => 'week',':id' => '[0-9]+'),
            'reservations/week/:id/:year/:month/:day'           => array('callback' => 'week',':id'=>'[0-9]+',':year'=>'[0-9]+',':month'=>'[0-9]+',':day'=>'[0-9]+'),
            'reservations/reserve/:id/:year/:month/:day/:hour'  => array('callback' => 'reserve',':id'=>'[0-9]+',':year'=>'[0-9]+',':month'=>'[0-9]+',':day'=>'[0-9]+',':hour'=>'[0-9]+'),

        );
    }

    protected function beforeCallback ($callback)
    {
        parent::beforeCallback($callback);
        $this->template->clearBreadcrumbs();
        $this->addBreadcrumb('home', 'Home');
    }
  
    public function index ()
    {
        $viewer = $this->getAccount();
        $authZ = $this->getAuthorizationManager();
        $azids = $authZ->getObjectsForWhich($viewer, 'purpose have');
        $purposes = $this->schema('Ccheckin_Purposes_Purpose')->getByAzids($azids);
        $rooms = $this->schema('Ccheckin_Rooms_Room');
        
        if (empty($purposes))
        {
            $this->template->nopurpose = true;
        }
        else
        {
            $observe = false;
            $participate = false;
            
            foreach ($purposes as $purpose)
            {
                $object = $purpose->getObject();
                
                if (!($object instanceof Ccheckin_Courses_Facet) || $object->course->active) 
                {
                    if ($this->hasPermission('purpose observe', $purpose))
                    {
                        $observe = true;
                    }
                    
                    if ($this->hasPermission('purpose participate', $purpose))
                    {
                        $participate = true;
                    }
                }
            }
            
            if ($observe || $participate)
            {
                $availableRooms = array();

                if ($observe)
                {
                    $availableRooms['observe'] = $rooms->find($rooms->observationType->equals('observe')->andIf(
                        $rooms->deleted->isNull()->orIf($rooms->deleted->isFalse())));
                }
                
                if ($participate)
                {
                    $availableRooms['participate'] = $rooms->find($rooms->observationType->equals('participate')->andIf(
                        $rooms->deleted->isNull()->orIf($rooms->deleted->isFalse())));
                }
                
                $this->template->rooms = $availableRooms;
            }
            else
            {
                $this->template->nopurpose = true;
            }
        }
    }
    
    public function schedule ()
    {
        $this->requirePermission('room view schedule');
        $rooms = $this->schema('Ccheckin_Rooms_Room');
        
        $roomId = $this->getRouteVariable('id');
        $year = $this->getRouteVariable('year');
        $month = $this->getRouteVariable('month');
        $day = $this->getRouteVariable('day');
        
        if ($roomId)
        {
            $room = $this->requireExists(
                $rooms->findOne($rooms->id->equals($roomId)->andIf(
                    $rooms->deleted->isNull()->orIf($rooms->deleted->isFalse())))
            );
            
            if ($year)
            {
                $date = new DateTime($year .'-'. $month  .'-'. $day);
            }
            else
            {
                $date = new DateTime;
                $year = $date->format('Y');
                $month = $date->format('m');
                $day = $date->format('j');
            }
            
            $prevDate = (clone $date)->modify('-1 week');
            $nextDate = (clone $date)->modify('+1 week');
            $currDate = (clone $date);
            
            $calendar = array();
            
            $calendar['month'] = $date->format('F');
            $calendar['year'] = $date->format('Y');
            $calendar['date'] = "$year/$month/$day";
            $calendar['previous'] = 'reservations/schedule/' . $room->id . '/' . $prevDate->format('Y') . '/' . $prevDate->format('m') . '/' . $prevDate->format('d');
            $calendar['next'] = 'reservations/schedule/' . $room->id . '/'  . $nextDate->format('Y') . '/' . $nextDate->format('m') . '/' . $nextDate->format('d');

            $weekDayOfFirst = $date->format('N');
                  
            while ($weekDayOfFirst--)
            {
                $date->modify('-1 day');
            }

            $calendar['weekofdate'] = (clone $date);
            $calendar['week'] =  $this->buildWeek($room, $date, null, true);
            $calendar['times'] = array();
            
            for ($i = self::START_HOUR; $i <= self::END_HOUR; $i++)
            {
                $calendar['times'][$i] = ($i < 13 ? $i . ' AM' : ($i % 12) . ' PM');
            }
            
            $siteSettings = $this->getApplication()->siteSettings;
            $storedDates = json_decode($siteSettings->getProperty('blocked-dates'), true);
            $this->template->blockDates = $this->convertToDateTimes($storedDates);            
            $this->template->calendar = $calendar;
            $this->template->room = $room;
        }
        else
        {
            $this->template->rooms = $rooms->find(
                $rooms->deleted->isNull()->orIf($rooms->deleted->isFalse()), array('orderBy' => 'name'));
        }
    }
    
    public function week ()
    {
        $viewer = $this->requireLogin();
        $authZ = $this->getAuthorizationManager();
        $azids = $authZ->getObjectsForWhich($viewer, 'purpose have');
        $purposes = $this->schema('Ccheckin_Purposes_Purpose')->getByAzids($azids);
        $courses = array();
        
        foreach ($purposes as $purpose)
        {
            $courses[] = $purpose->object->course;
        }

        $roomId = $this->getRouteVariable('id');
        $year = $this->getRouteVariable('year');
        $month = $this->getRouteVariable('month');
        $day = $this->getRouteVariable('day');
        
        $rooms = $this->schema('Ccheckin_Rooms_Room');
        $room = $this->requireExists(
            $rooms->findOne($rooms->id->equals($roomId)->andIf(
                $rooms->deleted->isNull()->orIf($rooms->deleted->isFalse())))
        );
    
        if ($year)
        {
            $date = new DateTime($year .'-'. $month  .'-'. $day);
        }
        else
        {
            $date = new DateTime;
            $year = $date->format('Y');
            $month = $date->format('m');
            $day = $date->format('j');
        }
        
        $prevDate = (clone $date)->modify('-1 week');
        $nextDate = (clone $date)->modify('+1 week');
        $currDate = (clone $date);
        
        $calendar = array();
        
        $calendar['month'] = $date->format('F');
        $calendar['year'] = $date->format('Y');
		$calendar['date'] = "$year/$month/$day";
        $calendar['previous'] = 'reservations/week/' . $room->id . '/' . $prevDate->format('Y') . '/' . $prevDate->format('m') . '/' . $prevDate->format('d');
        $calendar['next'] = 'reservations/week/' . $room->id . '/'  . $nextDate->format('Y') . '/' . $nextDate->format('m') . '/' . $nextDate->format('d');
        
        $weekDayOfFirst = $date->format('N');
              
        while ($weekDayOfFirst--)
        {
            $date->modify('-1 day');
        }

        $currentSemester = $this->getSemesterWithinRange(new DateTime);
        $courseInSemester = false;

        foreach ($courses as $course)
        {
            if ($currentSemester && ($currentSemester->id === $course->semester->id))
            {
                $courseInSemester = true;
            }
        }

        $calendar['week'] = ($courseInSemester ? $this->buildWeek($room, $date) : array());     
        $calendar['times'] = array();
        
        for ($i = self::START_HOUR; $i <= self::END_HOUR; $i++)
        {
            $calendar['times'][$i] = ($i < 13 ? $i . ' AM' : ($i % 12) . ' PM');
        }

        $siteSettings = $this->getApplication()->siteSettings;
        $storedDates = json_decode($siteSettings->getProperty('blocked-dates'), true);
        $this->template->blockDates = $this->convertToDateTimes($storedDates);          
        $this->template->calendar = $calendar;
        $this->template->room = $room;
        $this->template->currSemester = $this->getSemesterWithinRange(new DateTime);
    }
    
    public function view ()
    {
        $reservationId = $this->getRouteVariable('id');
        $reservation = $this->requireExists($this->schema('Ccheckin_Rooms_Reservation')->get($reservationId));
        
        $this->template->ismissed = $reservation->missed || (!$reservation->checkedIn && ($reservation->endTime < new DateTime));
        $this->template->reservation = $reservation;
        $this->template->dateFormat = "%b %e, %Y from %l%p";
    }
    
    public function upcoming ()
    {
        $viewer = $this->requireLogin();
        $reservations = $this->schema('Ccheckin_Rooms_Reservation');
        $now = new DateTime;
        
        if (!$this->hasPermission('admin'))
        {
			$cond = $reservations->allTrue(
                $reservations->accountId->equals($viewer->id),
                $reservations->checkedIn->isFalse()->orIf($reservations->checkedIn->isNull()),
                $reservations->missed->isFalse()->orIf($reservations->missed->isNull()),
                $reservations->startTime->afterOrEquals($now)        
            );
            $upcomingReservations = $reservations->find($cond, array('orderBy' => '+startTime'));
        }
        else
        {
            $upcomingReservations = $reservations->find(
                ($reservations->checkedIn->isFalse()->orIf($reservations->checkedIn->isNull()))->andIf(
                $reservations->startTime->afterOrEquals($now)),
                array('orderBy' => '+startTime')
            );
        }
        
		$this->template->pAdmin = $this->hasPermission('admin');
        $this->template->reservations = $upcomingReservations;
    }
    
    public function missed ()
    {
        $viewer = $this->requireLogin();               
        $reservations = $this->schema('Ccheckin_Rooms_Reservation');
        
        if (!$this->hasPermission('admin'))
        {
            $cond = $reservations->allTrue(
                $reservations->accountId->equals($viewer->id),
                $reservations->missed->isTrue()
            );
            $reservations = $reservations->find($cond);
        }
        else
        {
            $reservations = $reservations->find(
                $reservations->missed->isTrue()->orIf(
                    ($reservations->checkedIn->isFalse()->orIf($reservations->checkedIn->isNull()))->andIf(
                    $reservations->endTime->beforeOrEquals(new DateTime('now - 30 minutes')))
                ), array('orderBy' => '-startTime')
            );
            // $reservations = $reservations->find($reservations->missed->isTrue());
        }
        
		$this->template->pAdmin = $this->hasPermission('admin');
        $this->template->reservations = $reservations;
    }
	
	public function override ()
	{
		$viewer = $this->requireLogin();       
        $id = $this->getRouteVariable('id');
        $reservation = $this->requireExists($this->schema('Ccheckin_Rooms_Reservation')->get($id));
        
        if (!$this->hasPermission('admin') && $reservation->accountId != $viewer->id)
        {
            $this->triggerError('Ccheckin_Master_PermissionErrorHandler');
            exit;
        }
		
		if ($this->request->wasPostedByUser())
        {
            if ($command = $this->getPostCommand())
            {
                switch ($command)
                {
                    case 'override':
                        $checkinDate = $this->request->getPostParameter('checkinDate');
                        $checkinTime = $this->request->getPostParameter('checkinTime');
                        
                        try {
                            $checkin = new DateTime($checkinDate . ' ' . $checkinTime);
                        } catch (Exception $e) {
                            $this->flash('Invalid Date/Time format. Please try again.');
                            $this->response->redirect('reservations/override/' . $reservation->id);
                            exit;
                        }
                        
						$observation = $reservation->observation;
						$observation->startTime = $checkin;
						$observation->save();
						
                        $reservation->checkedIn = true;
						$reservation->missed = false;
						$reservation->save();
                        
                        $this->flash('The person has been checked-in.');
                        $this->response->redirect('reservations/upcoming');
                }
            }
        }
        $now = new DateTime;
        if ($now > $reservation->startTime)
        {
            $now->setTime((int)$reservation->startTime->format('G'), 0);
        }
        elseif ($now->format('i') < 30)
        {
            $now->setTime((int)$now->format('G'), 0);
        }
        else
        {
            $now->setTime(((int)$now->format('G'))+1, 0);
        }

        $this->template->reservation = $reservation;
        $this->template->topOfHour = $now;
	}
    
    public function delete ()
    {
        $viewer = $this->requireLogin();
        $id = $this->getRouteVariable('id');
        $reservation = $this->requireExists($this->schema('Ccheckin_Rooms_Reservation')->get($id));
        
        if (!$this->hasPermission('admin') && $reservation->accountId != $viewer->id)
        {
            $this->triggerError('Ccheckin_Master_PermissionErrorHandler');
            exit;
        }
        
        if ($this->request->wasPostedByUser())
        {
            if ($command = $this->getPostCommand())
            {
                switch ($command)
                {
                    case 'delete':
                        if ($this->hasPermission('admin'))
                        {
                            $this->sendReservationCanceledNotification($reservation, $viewer);
                        }                 	
                        $reservation->delete();
                        $this->flash('The reservation has been canceled.');
                        $this->response->redirect('reservations/upcoming');
                        exit;
                }
            }
        }
        else
        {
            $this->template->reservation = $reservation;
        }
    }
    

    public function getSemesterWithinRange ($date)
    {
        $sems = $this->schema('Ccheckin_Semesters_Semester');
        $semester = $sems->findOne($sems->allTrue(
            $sems->startDate->beforeOrEquals($date),
            $sems->endDate->afterOrEquals($date)
        ));

        return $semester;
    }

    public function getSemesterWithinReservationRange ($date)
    {
        $sems = $this->schema('Ccheckin_Semesters_Semester');
        $semester = $sems->findOne($sems->allTrue(
            $sems->openDate->beforeOrEquals($date),
            $sems->closeDate->afterOrEquals($date)
        ));

        return $semester;
    }

    public function reserve ()
    {
        $roomId = $this->getRouteVariable('id');
        $year = $this->getRouteVariable('year');
        $month = $this->getRouteVariable('month');
        $day = $this->getRouteVariable('day');
        $hour = $this->getRouteVariable('hour');

        $reservations = $this->schema('Ccheckin_Rooms_Reservation');
        
        $rooms = $this->schema('Ccheckin_Rooms_Room');
        $room = $this->requireExists(
            $rooms->findOne($rooms->id->equals($roomId)->andIf(
                $rooms->deleted->isNull()->orIf($rooms->deleted->isFalse())))
        );
        $viewer = $this->getAccount();
        $authZ = $this->getAuthorizationManager();

        $now = new DateTime;
        $message = '';
        $existing = null;
        $validDate = true;

        try {
            $now = new DateTime;
            $date = new DateTime($year .'-'. $month  .'-'. $day .' '. $hour .':00');
            if (!$this->getSemesterWithinRange($now) || !$this->getSemesterWithinReservationRange($date))
            {
                $continue = false;
                $validDate = false;
                $message = 'Reservation date must be within the current semester.';
            }

            $this->setPageTitle('Create Reservation for ' . $room->name . ' on ' . $date->format("%b %e, %Y at %I %p"));

        } catch (Exception $e) {
            $continue = false;
            $validDate = false;
            $message = 'Invalid Date format';
        }
        		
        if ($this->request->wasPostedByUser())
        {
            if ($command = $this->getPostCommand())
            {
                switch ($command)
                {
                    case 'reserve':
                        if (empty($existing))
                        {
                            $purposeId = $this->request->getPostParameter('purpose');
                            $duration = $this->request->getPostParameter('duration');
                            $continue = false;
                            $contiguous = false;
                            $end = null;
                            $exceedsDuration = 'You can only reserve up to 3 hours in a row.';
                            
                            if ($duration)
                            {
                                $end = clone $date;
                                $end->setTime($hour + $duration, 0);
                                
                                if ($purpose = $this->schema('Ccheckin_Purposes_Purpose')->get($purposeId))
                                {
                                    $reservationSchema = $this->schema('Ccheckin_Rooms_Reservation');
                                    if (!($continue = Ccheckin_Rooms_Reservation::GetRoomAvailable($room, $date, $duration, $viewer, $reservationSchema)))
                                    {
                                        $message = 'We cannot reserve the room at this time for ' . $duration . ' hours';
                                    }
                                    elseif (($results = $reservationSchema->find($continue->andIf($reservationSchema->accountId->equals($viewer->id)))))
                                    {
                                    	$continue = false;
                                    	$message = 'You have another reservation that conflicts with this one.';
                                    }
                                    // check for reservations before, after or sandwiched around this request
                                    if ($continue)
                                    {
                                    	$beforeCondition = $reservationSchema->allTrue(
                                    		$reservationSchema->roomId->equals($room->id),
                                    		$reservationSchema->accountId->equals($viewer->id),
                                    		$reservationSchema->endTime->equals($date)
                                    	);
                                    	$afterCondition = $reservationSchema->allTrue(
                                    		$reservationSchema->roomId->equals($room->id),
                                    		$reservationSchema->accountId->equals($viewer->id),
                                    		$reservationSchema->startTime->equals($end)
                                    	);
                                    	
                                    	// if there is a prior reservation, check for one in the following hour
                                    	if (($contiguousBefore = $reservationSchema->findOne($beforeCondition)))
                                    	{
                                    		// proceed if combined will be no more than 3 hours
                                    		if ((($contiguousBefore->endTime->format('G')-$contiguousBefore->startTime->format('G'))+$duration) <= 3)
                                    		{
                                    			$contiguous = true;
	                                    		$sandwichCondition = $afterCondition;
                                                // is the requested hour sandwiched between two other reservations?
	                                    		if (($contiguousAfter = $reservationSchema->findOne($sandwichCondition)))
	                                    		{
	                                    			// following reservation is more than an hour
	                                    			if (($contiguousAfter->endTime->format('G') - $contiguousAfter->startTime->format('G')) > 1)
	                                    			{
	                                    				$continue = false;
	                                    				$message = $exceedsDuration;
	                                    			}
	                                    			else
	                                    			{
	                                    				$continue = false;
	                                    				$message = 'Your reservations before and after this one were combined together to make one long reservation.';
	                                    				$duration = 3;
	                                    				$date = $contiguousBefore->startTime;
	                                    				$contiguousBefore->endTime = $contiguousAfter->endTime;
	                                    				$contiguousBefore->save();
	                                    				$afterObservation = $contiguousAfter->observation;
	                                    				$contiguousAfter->delete();
	                                    				$afterObservation->delete(); 
	                                    				$reservation = $contiguousBefore; // set the reservation as the before-sandwich-breadslice                            				
	                                    			}
	                                    		}
	                                    		else
	                                    		{
	                                    			$continue = false;
	                                    			$message = 'Your reservation before this one was combined together to make it one longer reservation.';
	                                    			$date = $contiguousBefore->startTime;
	                                    			$duration = ($contiguousBefore->endTime->format('G')-$contiguousBefore->startTime->format('G'))+$duration;
	                                    			$contiguousBefore->endTime = (clone $contiguousBefore->startTime)->modify('+' . $duration . ' hours');
	                                    			$contiguousBefore->save();
		                                    		$reservation = $contiguousBefore; // set the reservation as the one prior to this request
	                                    		}
                                    		}
                                    		else
                                    		{
                                    			$continue = false;
                                    			$message = $exceedsDuration;
                                    		}
	
                                    	}
                                    	elseif (($contiguousAfter = $reservationSchema->findOne($afterCondition)))
                                    	{
                                    		// proceed if combined will be no more than 3 hours
                                    		if ((($contiguousAfter->endTime->format('G')-$contiguousAfter->startTime->format('G'))+$duration) <= 3)
                                    		{
                                    			$contiguous = true;
                                    			$continue = true;
                                    			$message = 'Your reservation immediately after this one was combined together to make it one longer reservation. 
                                    				An email with the new details has been sent to you.';

                                    			// set new end & duration then delete the later reservation/observation
                                    			$end = $contiguousAfter->endTime;
                                    			$duration = ($contiguousAfter->endTime->format('G')-$contiguousAfter->startTime->format('G')) + $duration;
                                    			$afterObservation = $contiguousAfter->observation;
                                    			$contiguousAfter->delete();
                                				$afterObservation->delete();                              				
                                    		}
                                    		else
                                    		{
                                     			$continue = false;
                                    			$message = $exceedsDuration;                           			
                                    		}
                                    	}
                                    }

                                    $cdate = clone $date;
                                    $cnow = clone $now;
                                    if (($cdate < $now) && !$this->hasPermission('admin'))
                                    {
                                        $continue = false;
                                        $message = 'You cannot reserve a room for a date and time that has already passed.';
                                    }

                                    $siteSettings = $this->getApplication()->siteSettings;
                                    $storedDates = json_decode($siteSettings->getProperty('blocked-dates'), true);
                                    $blockDates = $this->convertToDateTimes($storedDates);
                                    // double check they aren't trying to reserve a blocked off day.
                                    foreach ($blockDates as $blocked)
                                    {
                                        if ($blocked->format('Y/m/d') === $date->format('Y/m/d'))
                                        {
                                            $continue = false;
                                            $message = "You cannot reserve a room on a day that has been closed by Children's Campus.";
                                            break;
                                        }
                                    }  
                                }
                                else
                                {
                                    $purpose = null;
                                    $message = 'Please state the purpose for the visit';
                                }
                            }
                            else
                            {
                                $message = 'Please state the amount of time for the visit';
                            }

                            $dateSummary = 'Starts: ' . $date->format('M j, Y g:ia').' for '.$duration.' hour'. ($duration > 1 ? 's.' : '.');
                            
                            if ($continue)
                            {
                                $observation = $this->schema('Ccheckin_Rooms_Observation')->createInstance();
                                $observation->roomId = $room->id;
                                $observation->purposeId = $purpose->id;
                                $observation->accountId = $viewer->id;
                                $observation->save();
                                
                                $reservation = $this->schema('Ccheckin_Rooms_Reservation')->createInstance();
                                $reservation->roomId = $room->id;
                                $reservation->observationId = $observation->id;
                                $reservation->accountId = $viewer->id;
                                $reservation->startTime = $date;
                                $reservation->endTime = $end;
                                $reservation->checkedIn = false;
                                $reservation->missed = false;
                                $reservation->save();
                                
                                $this->sendReservationDetailsNotification($reservation, $viewer);

                                $flash = 'Your reservation has been scheduled for '. $dateSummary;
                                if ($contiguous) $flash = $message . ' ' . $dateSummary;
                                $this->flash($flash);
                                $this->response->redirect('reservations/view/' . $reservation->id);
                            }
                            elseif ($contiguous)
                            {
                            	if (isset($reservation))
                            	{
                            		$this->flash($message . ' ' . $dateSummary);
                            		$message = '';
                            		$this->response->redirect('reservations/view/' . $reservation->id);
                            	}
                            	else
                            	{   // flash them their error message
                                    $this->flash($message);
                            	}
                            }
                        }
                        break;
                }
            }
        }

        $azids = $authZ->getObjectsForWhich($viewer, 'purpose have');
        $purposes = $this->schema('Ccheckin_Purposes_Purpose')->getByAzids($azids);
        $purpose = null;
        
        foreach ($purposes as $idx => $p)
        {
            $object = $p->getObject();
            
            if (($object instanceof Ccheckin_Courses_Facet) && !$object->course->active || ($object->type->sortName !== $room->observationType)) 
            {
                unset($purposes[$idx]);
            }
        }

        $purposes = array_values($purposes);
        $selected = null;
        foreach ($purposes as $p)
        {
            if ($p->object->type->sortName === $room->observationType)
            {
                $selected = $p->id;
            }
        }

        if (count($purposes) == 1)
        {
            $purpose = $purposes[0];
        }

        $this->template->room = $room;
        $this->template->purposes = $purposes;
        $this->template->selected = $selected;
        $this->template->message = $message;
        $this->template->purpose = $purpose;
        $this->template->date = $date ?? null;
        $this->template->dateFormat = "%b %e, %Y at %l %p";
        $this->template->validDate = $validDate;
    }

    public function observations ()
    {
        $viewer = $this->requireLogin();
        
        $obsSchema = $this->schema('Ccheckin_Rooms_Observation');
        $cond = $obsSchema->allTrue(
            $obsSchema->accountId->equals($viewer->id),
            $obsSchema->startTime->before(new DateTime)
        );
        $observations = $obsSchema->find($cond, array('orderBy' => '-startTime'));

        $purposes = array();
                  
        foreach ($observations as $observation)
        {
            if ($observation->duration > 0)
            {
                if (empty($purposes[$observation->purpose->id]))
                {
                    $purposes[$observation->purpose->id] = array(
                        'purpose' => $observation->purpose,
                        'num' => 0, 
                        'time' => 0,
                        'observations' => array()
                    );
                }
                
                $purposes[$observation->purpose->id]['observations'][] = $observation;
                $purposes[$observation->purpose->id]['num']++;
                $purposes[$observation->purpose->id]['time'] += $observation->duration;

            }
        }

        $this->template->purposes = $purposes;
    }

    private function buildWeek ($room, $date, $month = null, $reservations = false, $semester = null)
    {
        $week = array();

        for ($i = 0; $i < 7; $i++)
        {
            $week[] = $this->buildDay($room, $date, $month, $reservations, $semester);
            $date = $date->modify('+1 day');
        }    

        return $week;
    }
    
    private function buildDay ($room, $date, $month = null, $reservations)
    {
        
        $day = array();
        $today = new DateTime();
        
        if ($today->format("m/d/y") == $date->format("m/d/y"))
        {
            $day['today'] = true;
        }
		
		$day['display'] = $date->format("m/d/y");
        
        if ($month && $date->format('m') != $month)
        {
            $day['outside'] = true;
        }

        $now = new DateTime;
        $currentSemester = $this->getSemesterWithinRange($now);
        $dateSemester = $this->getSemesterWithinRange($date);
      
        $day['dayOfWeek'] = $date->format('w');
        $day['dayOfMonth'] = $date->format('j');
        $day['suffix'] = $date->format('S');
        $day['month'] = $date->format('M');
        $day['date'] = $date->format('Y') . '/' . $date->format('m') . '/' . $date->format('d');
        $day['datetime'] = new DateTime($date->format('Y/m/d'));
        $day['inSemester'] = (($currentSemester && $dateSemester) && ($currentSemester->id === $dateSemester->id));
        $day['times'] = $this->buildDayTimes($room, $date, $reservations);

        return $day;
    }
    
    private function buildDayTimes ($room, $day, $reservations)
    {
        $times = array();
        
        // N: 1 for Monday thru 7 for Sunday
        // room->schedule: 0 for Monday thru 5 for Friday
        if (isset($room->schedule[$day->format('N')-1]))    
        {
            $startTime = clone $day;
            $endTime = clone $day;
            $hours = array_keys($room->schedule[$day->format('N')-1]);
            
            for ($i = self::START_HOUR; $i <= self::END_HOUR; $i++)
            {
                if (!in_array($i, $hours))
                {
                    $times[$i] = 'unavailable';
                }
                else
                {
                    $startTime->setTime($i, 0, 0);
                    $endTime->setTime($i + 1, 0, 0);

                    $tRoomReservation = $this->schema('Ccheckin_Rooms_Reservation');
                    $cond = $tRoomReservation->allTrue(
                        $tRoomReservation->roomId->equals($room->id),
                        $tRoomReservation->startTime->beforeOrEquals($startTime),
                        $tRoomReservation->endTime->afterOrEquals($endTime)
                    );
                    
                    $reservationRecords = $tRoomReservation->find($cond);
                    
                    if ($reservations)
                    {
                        $times[$i] = $reservationRecords;
                    }
                    else
                    {
                        if (count($reservationRecords) < $room->maxObservers)
                        {
                            $times[$i] = 'open-space';
                        }
                        else
                        {
                            $times[$i] = 'full';
                        }
                    }
                }
            }
        }
        else
        {
            for ($i = self::START_HOUR; $i <= self::END_HOUR; $i++)
            {
                if ($reservations)
                {
                    $times[$i] = array();
                }
                else
                {
                    $times[$i] = 'noday';
                }
            }
        }
        
        return $times;
    }

    protected function sendReservationDetailsNotification ($reservation, $account)
    {
        $emailManager = new Ccheckin_Admin_EmailManager($this->getApplication(), $this);
        $emailData = array();        
        $emailData['reservation'] = $reservation;
        $emailData['user'] = $account;
        $emailManager->processEmail('sendReservationDetails', $emailData);
    }

    protected function sendReservationCanceledNotification ($reservation, $account)
    {
        $emailManager = new Ccheckin_Admin_EmailManager($this->getApplication(), $this);
        $emailData = array();        
        $emailData['reservation_date'] = $reservation->startTime;
        $emailData['reservation_purpose'] = $reservation->observation->purpose->shortDescription;
        $emailData['user'] = $account;
        $emailManager->processEmail('sendReservationCanceled', $emailData);
    }


}

