<?php

/**
 * Upgrade/Install this module.
 * 
 * @author      Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Ccheckin_Purposes_ModuleUpgradeHandler extends Bss_ActiveRecord_BaseModuleUpgradeHandler
{
    public function onModuleUpgrade ($fromVersion)
    {
        switch ($fromVersion)
        {
            case 0:
                /**
                *   Create tables
                */
                $def = $this->createEntityType('ccheckin_purposes', $this->getDataSource('Ccheckin_Purposes_Purpose'));
                $def->addProperty('id', 'int', array('sequence' => true, 'primaryKey' => true));
                $def->addProperty('object_id', 'int');               
                $def->addProperty('object_type', 'string');
                $def->save();

                break;

        }
    }
}



