<?php

/**
 * Upgrade/Install this module.
 * 
 * @author      Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Ccheckin_Rooms_ModuleUpgradeHandler extends Bss_ActiveRecord_BaseModuleUpgradeHandler
{
    public function onModuleUpgrade ($fromVersion)
    {
        switch ($fromVersion)
        {
            case 0:
                /**
                *   Create tables
                */
                $def = $this->alterEntityType('bss_authn_accounts', $this->getDataSource('Bss_AuthN_Account'));
                $def->addProperty('missed_reservation', 'bool');
                $def->save();

                $def = $this->createEntityType('ccheckin_rooms', $this->getDataSource('Ccheckin_Rooms_Room'));
                $def->addProperty('id', 'int', array('sequence' => true, 'primaryKey' => true));
                $def->addProperty('name', 'string');               
                $def->addProperty('description', 'string');
                $def->addProperty('observation_type', 'string');
                $def->addProperty('max_observers', 'string');
                $def->addProperty('schedule', 'string');
                $def->addProperty('hours', 'string');
                $def->addProperty('days', 'string');
                $def->save();

                $def = $this->createEntityType('ccheckin_room_observations', $this->getDataSource('Ccheckin_Rooms_Observation'));
                $def->addProperty('id', 'int', array('sequence' => true, 'primaryKey' => true));
                $def->addProperty('room_id', 'int');
                $def->addProperty('purpose_id', 'int');
                $def->addProperty('account_id', 'int');
                $def->addProperty('start_time', 'datetime');
                $def->addProperty('end_time', 'datetime');
                $def->addProperty('duration', 'float');   
                $def->addForeignKey('ccheckin_rooms', array('room_id' => 'id')); 
                $def->addForeignKey('ccheckin_purposes', array('purpose_id' => 'id')); 
                $def->addForeignKey('bss_authn_accounts', array('account_id' => 'id'));
                $def->save();

                $def = $this->createEntityType('ccheckin_room_reservations', $this->getDataSource('Ccheckin_Rooms_Reservation'));
                $def->addProperty('id', 'int', array('sequence' => true, 'primaryKey' => true));
                $def->addProperty('room_id', 'int');
                $def->addProperty('observation_id', 'int');
                $def->addProperty('account_id', 'int');
                $def->addProperty('checked_in', 'bool');
                $def->addProperty('start_time', 'datetime');
                $def->addProperty('end_time', 'datetime');
                $def->addProperty('missed', 'bool');
                $def->addForeignKey('ccheckin_rooms', array('room_id' => 'id'));
                $def->addForeignKey('ccheckin_room_observations', array('observation_id' => 'id'));
                $def->addForeignKey('bss_authn_accounts', array('account_id' => 'id'));
                $def->save();

                break;

        }
    }
}


