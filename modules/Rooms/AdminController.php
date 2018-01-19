<?php

/**
 *  Manage rooms, observations, and reservations as admin
 *
 * @author  Charles O'Sullivan (chsoney@sfsu.edu)
 * @author  Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Ccheckin_Rooms_AdminController extends At_Admin_Controller
{
    public static function getRouteMap ()
    {
        return array(
            'admin/rooms' => array('callback' => 'rooms'),
            'admin/rooms/:id' => array('callback' => 'editRoom', ':id' => '([0-9]+|new)'),
            'admin/observations/current' => array('callback' => 'currentObservations'),
            'admin/observations/reservations' => array('callback' => 'currentReservations'),
            'admin/observations/missed' => array('callback' => 'missedObservations'),
            'admin/observations/generate' => array('callback' => 'generateObservations'), // TODO: Figure out what this is supposed to do and how it is accessed on dev site
        );
    }

    public function beforeCallback ($callback)
    {
        parent::beforeCallback($callback);
        $this->requirePermission('admin');
    }

    public function rooms () 
    {
        $this->setPageTitle('Manage Rooms');
        $roomSchema = $this->schema('Ccheckin_Rooms_Room');
        $message = '';
        
        if ($this->request->getRequestMethod() == 'post')
        {
            if ($command = $this->request->getPostParameter('command'))
            {
                $action = array_shift(array_keys($command));
                
                switch ($action)
                {
                    case 'remove':
                        $rooms = $this->request->getPostParameter('rooms');
                        
                        if (!empty($rooms))
                        {
                            foreach ($rooms as $roomId)
                            {                               
                                if ($room = $roomSchema->get($roomId))
                                {
                                    $room->delete();
                                }
                            }
                            
                            $message = 'The selected rooms have been deleted.';
                        }
                        break;
                }
            }
        }
        
        $this->template->rooms = $roomSchema->getAll(array('orderBy' => 'name'));
        $this->template->message = $message;
    }

    public function editRoom () 
    {
        $id = $this->getRouteVariable('id');
        if (!preg_match('/^([0-9]+|new)$/', $id))
        {
            $this->notFound(); exit;
        }
        
        $rooms = $this->schema('Ccheckin_Rooms_Room');
        $errors = array();
        $new = false;
        
        $hours = array(7,8,9,10,11,12,13,14,15,16,17,18,19,20);
        $days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday', );
        
        if (is_numeric($id))
        {
            $room = $this->requireExists($rooms->get($id));
            $this->setPageTitle('Edit Room: ' . $room->name);
        }
        else
        {
            $new = true;
            $room = $rooms->createInstance();
            $this->setPageTitle('Create Room');
        }
        
        if ($this->request->getRequestMethod() == 'post')
        {
            if ($command = $this->request->getPostParameter('command'))
            {
                $action = array_shift(array_keys($command));
                
                switch ($action)
                {
                    case 'save':
                        $room->absorbData($this->request->getPostParameter('room'));
                        $errors = $room->validate();
                        
                        if (empty($errors))
                        {
                            $room->save();
                            $this->response->redirect('admin/rooms');
                        }
                        
                        break;
                }
            }
        }
        
        $this->template->hours = $hours;
        $this->template->days = $days;
        $this->template->room = $room;
        $this->template->new = $new;
        $this->template->observationTypes = $room->getTypes(); // $room::$types or Ccheckin_Rooms_Room::$types
        $this->template->errors = $errors;
    }

    public function currentObservations () 
    {
        $reservations = $this->schema('Ccheckin_Rooms_Reservation');
        $observations = $reservations->find($reservations->checkIn->isTrue());

        $this->template->reservations = $observations;
    }

    public function currentReservations () 
    {
        $reservations = $this->schema('Ccheckin_Rooms_Reservation');

        $this->template->reservations = $reservations;
    }

    // Missed if 30 minutes late and still haven't checked in.
    public function missedObservations () 
    {
        $reservations = $this->schema('Ccheckin_Rooms_Reservation');
        $condition = $reservations->allTrue(
            $reservations->startTime->before(new Date(strtotime('now - 30 minutes'))),
            $reservations->checkIn->isFalse()
        );
        $missed = $reservations->find($condition);

        $this->template->reservations = $missed;
    }

    // NOTE: On old app this begins with a die statement, so probably just a dev/debug func
    public function generateObservation () {}

}