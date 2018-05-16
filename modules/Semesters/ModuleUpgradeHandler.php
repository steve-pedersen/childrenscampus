<?php

/**
 * Upgrade/Install this module.
 * 
 * @author      Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Ccheckin_Semesters_ModuleUpgradeHandler extends Bss_ActiveRecord_BaseModuleUpgradeHandler
{
    public function onModuleUpgrade ($fromVersion)
    {
        switch ($fromVersion)
        {
            case 0:
                $def = $this->createEntityType('ccheckin_semesters', $this->getDataSource('Ccheckin_Semesters_Semester'));
                $def->addProperty('id', 'int', array('sequence' => true, 'primaryKey' => true));
                $def->addProperty('display', 'string');               
                $def->addProperty('internal', 'string');
                $def->addProperty('start_date', 'datetime');
                $def->addProperty('end_date', 'datetime');
                $def->addProperty('open_date', 'datetime');
                $def->addProperty('last_date', 'datetime');
                $def->save();

                break;

        }
    }
}



