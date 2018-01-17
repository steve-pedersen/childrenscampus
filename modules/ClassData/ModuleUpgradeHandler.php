<?php

class Ccheckin_ClassData_ModuleUpgradeHandler extends Bss_ActiveRecord_BaseModuleUpgradeHandler
{
    public function onModuleUpgrade ($fromVersion)
    {
        $siteSettings = $this->getApplication()->siteSettings;
        switch ($fromVersion)
        {
            case 0:
                // $def = $this->createEntityType('classdata_users', $this->getDataSource('Ccheckin_ClassData_User'));
                // $def->addProperty('SFSUid', 'string', array('primaryKey' => true));
                // $def->addProperty('External_Person_Key', 'string');
                // $def->addProperty('User_ID', 'string');
                // $def->addProperty('Passwd', 'string');
                // $def->addProperty('Firstname', 'string');
                // $def->addProperty('Lastname', 'string');
                // $def->addProperty('Email', 'string');
                // $def->addProperty('Institution_Role', 'string');
                // $def->addProperty('System_Role', 'string');
                // $def->addProperty('Available_Ind', 'string');
                // $def->addProperty('Row_Status', 'string');
                // $def->save();
                
                $def = $this->createEntityType('classdata_courses', $this->getDataSource('Ccheckin_ClassData_Course'));
                $def->addProperty('External_Course_Key', 'string', array('primaryKey' => true));
                $def->addProperty('Course_ID', 'string');
                $def->addProperty('Course_Name', 'string');
                $def->addProperty('Available_Ind', 'string');
                $def->addProperty('Row_Status', 'string');
                $def->addProperty('Abs_Limit', 'string');
                $def->addProperty('Soft_Limit', 'string');
                $def->addProperty('Upload_Limit', 'string');
                $def->save();

                $def = $this->createEntityType('classdata_enrollments', $this->getDataSource('Ccheckin_ClassData_Enrollment'));
                $def->addProperty('SFSUid', 'string', array('primaryKey' => true));
                $def->addProperty('External_Course_Key', 'string', array('primaryKey' => true));
                $def->addProperty('External_Person_Key', 'string');
                $def->addProperty('Role', 'string', array('primaryKey' => true));
                $def->addProperty('Available_Ind', 'string');
                $def->addProperty('Row_Status', 'string');
                $def->save();

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