<?php

/**
    This object is created when a person wants to reserve a room.  They choose their puprpose
    and the puprpose and account are set for the observation.
    
    This record is attached to the reservation they create.
    
    The report field provider that will be created for showing observation information
    
    @author Charles O'Sullivan (chsoney@sfsu.edu)
    @copyright San Francisco State University
 */
class Ccheckin_Rooms_Observation extends Bss_ActiveRecord_BaseWithAuthorization // implements Bss_AuthZ_IObjectProxy
{
    public static function SchemaInfo ()
    {
        return array(
            '__type' => 'ccheckin_room_observations',
            '__pk' => array('id'),
            
            'id' => 'int',
            'roomId' => array('int', 'nativeName' => 'room_id'),
            'purposeId' => array('int', 'nativeName' => 'purpose_id'),
            'accountId' => array('int', 'nativeName' => 'account_id'),
            'startTime' => array('datetime', 'nativeName' => 'start_time'),
            'endTime' => array('datetime', 'nativeName' => 'end_time'),
            'duration' => 'float',

            'room' => array('1:1', 'to' => 'Ccheckin_Rooms_Room', 'keyMap' => array('room_id' => 'id')),
            'purpose' => array('1:1', 'to' => 'Ccheckin_Purposes_Purpose', 'keyMap' => array('purpose_id' => 'id')),
            'account' => array('1:1', 'to' => 'Bss_AuthN_Account', 'keyMap' => array('account_id' => 'id')),
            
        );
    }

    // Should this be done from classdata/sims here or from within the Semester module?
    // Possibly add a parameter in here for the 'observation' object in question.
    public function getSemester ()
    {
        $semesters = $this->getSchema('Ccheckin_Semesters_Semester');

        $semester = $semesters->find(
            $semesters->startDate->beforeOrEquals($this->startTime)->andIf(
                $semester->endDate->afterOrEquals($this->endTime)
            )                  
        );

        return $semester;


        // Old Code
        $table = Semester::GetTable();
        $semProto = new Semester($this->_dataSource);
        $query = $table->getSelectQuery(null);
        $query->where($table->startDate->onOrBefore($this->startTime));
        $query->where($table->endDate->onOrAfter($this->endTime));
        $result = $semProto->fullCustomQuery($query);
        
        return (!empty($result) ? $result[0] : null);
    }

}