<?php

class Ccheckin_ClassData_ModuleUpgradeHandler extends Bss_ActiveRecord_BaseModuleUpgradeHandler
{
    public function onModuleUpgrade ($fromVersion)
    {
        $siteSettings = $this->getApplication()->siteSettings;
        switch ($fromVersion)
        {
            case 0:
                $siteSettings->defineProperty('classdata-api-url', 'The URL for the ClassData/SIMS API', 'string');
                $siteSettings->defineProperty('classdata-api-key', 'The key for the ClassData/SIMS API', 'string');
                $siteSettings->defineProperty('classdata-api-secret', 'The secret for the ClassData/SIMS API', 'string');

                $def = $this->createEntityType('classdata_sync_logs', $this->getDataSource('Ccheckin_ClassData_SyncLog'));
                $def->addProperty('id', 'int', array('primaryKey' => true, 'sequence' => true));
                $def->addProperty('dt', 'datetime');
                $def->addProperty('by', 'string');
                $def->addProperty('status', 'int');
                $def->addProperty('error_code', 'string');
                $def->addProperty('error_message', 'string');
                $def->save();
                
                break;
        }
    }
}