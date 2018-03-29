<?php

/**
 */
class Ccheckin_ClassData_SyncLog extends Bss_ActiveRecord_Base
{
    public static function SchemaInfo ()
    {
        return array(
            '__type' => 'classdata_sync_logs',
            '__pk' => array('id'),
            
            'id' => 'int',
            'dt' => 'datetime',
            'by' => 'string',
            'status' => 'int',
            'errorCode' => array('string', 'nativeName' => 'error_code'),
            'errorMessage' => array('string', 'nativeName' => 'error_message'),
        );
    }
}
