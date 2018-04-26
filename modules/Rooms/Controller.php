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

        $sems = $this->schema('Ccheckin_Semesters_Semester');
        $activeSemesterCode = Ccheckin_Semesters_Semester::guessActiveSemester(true);
        $activeSemester = $sems->findOne($sems->internal->equals($activeSemesterCode));
        $withinSemesterRange = false;

        foreach ($courses as $course)
        {
            if ($course->semester->id === $activeSemester->id)
            {
                $openDate = ($activeSemester->openDate === null) ? $activeSemester->startDate : $activeSemester->openDate;
                $closeDate = ($activeSemester->closeDate === null) ? $activeSemester->endDate : $activeSemester->closeDate;
                $withinSemesterRange = ($openDate < $currDate && $currDate < $closeDate);
            }
            // elseif ($course->semester->internal[3] == 1)     // account for possible overlap with winter and spring....
            // {
            // }
        }

        $calendar['week'] = ($withinSemesterRange ? $this->buildWeek($room, $date) : array());
        
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
    
    public function view ()
    {
        $reservationId = $this->getRouteVariable('id');
        $reservation = $this->requireExists($this->schema('Ccheckin_Rooms_Reservation')->get($reservationId));
        
        $this->template->ismissed = $reservation->missed || (!$reservation->checkedIn && ($reservation->endTime < new DateTime));
        $this->template->reservation = $reservation;
        $this->template->dateFormat = "%b %e, %Y at %l %p";
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
            $upcomingReservations = $reservations->find($cond, array('orderBy' => '-startTime'));
        }
        else
        {
            $upcomingReservations = $reservations->find(
                ($reservations->checkedIn->isFalse()->orIf($reservations->checkedIn->isNull()))->andIf(
                $reservations->startTime->afterOrEquals($now)),
                array('orderBy' => '-startTime')
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
        $now->setTime((int)$now->format('G'), 0);

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
                        $reservation->delete();
                        $this->flash('The reservation has been deleted.');
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
    

    public function withinSemesterRange ($date)
    {
        $sems = $this->schema('Ccheckin_Semesters_Semester');
        $activeSemesterCode = Ccheckin_Semesters_Semester::guessActiveSemester(true);
        $activeSemester = $sems->findOne($sems->internal->equals($activeSemesterCode));
        $openDate = ($activeSemester->openDate === null) ? $activeSemester->startDate : $activeSemester->openDate;
        $closeDate = ($activeSemester->closeDate === null) ? $activeSemester->endDate : $activeSemester->closeDate;
        $withinSemesterRange = ($openDate < $date && $date < $closeDate);

        return $withinSemesterRange;
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
            $date = new DateTime($year .'-'. $month  .'-'. $day .' '. $hour .':00');
            if (!$this->withinSemesterRange($date))
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
                            $end = null;
                            
                            if ($duration)
                            {
                                $end = clone $date;
                                $end->setTime($hour + $duration, 0);
                                
                                if ($purpose = $this->schema('Ccheckin_Purposes_Purpose')->get($purposeId))
                                {
                                    $reservationSchema = $this->schema('Ccheckin_Rooms_Reservation');
                                    if (!($continue = Ccheckin_Rooms_Reservation::GetRoomAvailable($room, $date, $duration, $reservationSchema)))
                                    {
                                        $message = 'We cannot reserve the room at this time for ' . $duration . ' hours';
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

                                $this->flash('Your reservation has been scheduled for '.$date->format('M j, Y g:ia').' for '.$duration.' hour'. ($duration > 1 ? 's.' : '.'));
                                $this->response->redirect('reservations/view/' . $reservation->id);
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
            
            if (($object instanceof Ccheckin_Courses_Facet) && !$object->course->active) 
            {
                unset($purposes[$idx]);
            }
        }

        $purposes = array_values($purposes);
        
        if (count($purposes) == 1)
        {
            $purpose = $purposes[0];
        }
        
        $this->template->room = $room;
        $this->template->purposes = $purposes;
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
        
        $day['dayOfWeek'] = $date->format('w');
        $day['dayOfMonth'] = $date->format('j');
        $day['suffix'] = $date->format('S');
        $day['month'] = $date->format('M');
        $day['date'] = $date->format('Y') . '/' . $date->format('m') . '/' . $date->format('d');
        $day['times'] = $this->buildDayTimes($room, $date, $reservations);

        return $day;
    }
    
    private function buildDayTimes ($room, $day, $reservations)
    {
        $times = array();

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

}

