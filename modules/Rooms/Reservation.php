<?php

/**
    A user picks a time and date for a room and says until when they need it.
    They should specify how many hours so we can check the number of reservations
    for each hour starting from the start time and seeing of they are allowed.
    
    Say they got the time.  When they come to the center, they will enter their 
    credentials into the kiosk and it will grab all the reservations for that user for that day.
    They will select the one they created and the system will:
        1.  Set the start time on the observation.
        2.  Set the checkedIn flagged to true.
    
    When they checkout at the kiosk with their user information, this record will
    be grabbed again and it will checked the checkedIn flag.  Since is was set, the
    observations end time will be set.
    
    @author Charles O'Sullivan (chsoney@sfsu.edu)
    @copyright San Francisco State University
 */
class Ccheckin_Rooms_Reservation extends Bss_ActiveRecord_BaseWithAuthorization // implements Bss_AuthZ_IObjectProxy
{
    public static function SchemaInfo ()
    {
        return array(
            '__type' => 'ccheckin_room_reservations',
            '__pk' => array('id'),
            
            'id' => 'int',
            'roomId' => array('int', 'nativeName' => 'room_id'),
            'observationId' => array('int', 'nativeName' => 'observation_id'),
            'accountId' => array('int', 'nativeName' => 'account_id'),
            'checkedIn' => array('bool', 'nativeName' => 'checked_in'),
            'startTime' => array('datetime', 'nativeName' => 'start_time'),
            'endTime' => array('datetime', 'nativeName' => 'end_time'),
            'reminderSent' => array('bool', 'nativeName' => 'reminder_sent'),
            'missed' => 'bool',

            'room' => array('1:1', 'to' => 'Ccheckin_Rooms_Room', 'keyMap' => array('room_id' => 'id')),
            'observation' => array('1:1', 'to' => 'Ccheckin_Rooms_Observation', 'keyMap' => array('observation_id' => 'id')),
            'account' => array('1:1', 'to' => 'Bss_AuthN_Account', 'keyMap' => array('account_id' => 'id')),
        );
    }

    public function GetRoomAvailable ($room, $start, $duration, $resSchema)
    {
        $available = false;
        $continue = true;
        $endHour = (int)$start->format('G') + (int)$duration;
        $hours = $room->getHours($start->format('N')-1);
        
        // capital G for 0-23 hour format
        for ($i = (int)$start->format('G'); $i < $endHour; $i++)
        {
            if (!in_array($i, $hours))
            {
                $continue = false;
            }   
        }
        
        if ($continue)
        {
            $end = clone $start;
            
            $end->setTime($endHour, 0);
            $tRoomReservation = $resSchema;
            
            $cond = $tRoomReservation->find($tRoomReservation->anyTrue(
                $tRoomReservation->allTrue(
                    $tRoomReservation->roomId->equals($room->id),
                    // $tRoomReservation->deleted->isNull()->orIf($tRoomReservation->deleted->isFalse()),
                    $tRoomReservation->startTime->beforeOrEquals($start),
                    $tRoomReservation->endTime->after($start)
                ),
                $tRoomReservation->allTrue(
                    $tRoomReservation->roomId->equals($room->id),
                    // $tRoomReservation->deleted->isNull()->orIf($tRoomReservation->deleted->isFalse()),
                    $tRoomReservation->startTime->before($end),
                    $tRoomReservation->endTime->afterOrEquals($end)
                ),
                $tRoomReservation->allTrue(
                    $tRoomReservation->roomId->equals($room->id),
                    // $tRoomReservation->deleted->isNull()->orIf($tRoomReservation->deleted->isFalse()),
                    $tRoomReservation->startTime->beforeOrEquals($start),
                    $tRoomReservation->endTime->afterOrEquals($end)
                ),
                $tRoomReservation->allTrue(
                    $tRoomReservation->roomId->equals($room->id),
                    // $tRoomReservation->deleted->isNull()->orIf($tRoomReservation->deleted->isFalse()),
                    $tRoomReservation->startTime->afterOrEquals($start),
                    $tRoomReservation->endTime->beforeOrEquals($end)
                )
            ));
            
            $reservations = is_array($cond) ? $cond : $tRoomReservation->find($cond);
            
            if (count($reservations) < $room->maxObservers)
            {
                $available = true;
            }
        }
        
        return $available;
    }
    
    public static function GetAccountReservations ($account)
    {
        $reservations = $this->schema('Ccheckin_Rooms_Reservation');
        $cond = $reservations->allTrue(
            $reservations->accountId->equals($account->id),
            $reservations->missed->isFalse()
        );
        $accountReservations = $reservations->find($cond, array('orderBy' => array('start_time')));
        
        return $accountReservations;
    }

}