<?php

class Ccheckin_ClassData_Enrollment extends Bss_ActiveRecord_Base
{
    public static function SchemaInfo ()
    {
        return array(
            '__type' => 'classdata_enrollments',
            '__pk' => array('externalCourseKey', 'sfsuId', 'role'),
            
            'sfsuId' => array('string', 'nativeName' => 'SFSUid'),
            'externalCourseKey' => array('string', 'nativeName' => 'External_Course_Key'),
            'externalPersonKey' => array('string', 'nativeName' => 'External_Person_Key'),
            'role' => array('string', 'nativeName' => 'Role'),
            'availableInd' => array('string', 'nativeName' => 'Available_Ind'),
            'rowStatus' => array('string', 'nativeName' => 'Row_Status'),
        );
    }

}