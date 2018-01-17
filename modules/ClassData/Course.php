<?php

class Ccheckin_ClassData_Course extends Bss_ActiveRecord_Base
{
    public static function SchemaInfo ()
    {
        return array(
            '__type' => 'classdata_courses',
            '__pk' => array('externalCourseKey'),
            
            'externalCourseKey' => array('string', 'nativeName' => 'External_Course_Key'),
            'courseId' => array('string', 'nativeName' => 'Course_ID'),
            'courseName' => array('string', 'nativeName' => 'Course_Name'),
            'availableInd' => array('string', 'nativeName' => 'Available_Ind'),
            'rowStatus' => array('string', 'nativeName' => 'Row_Status'),
            'absLimit' => array('string', 'nativeName' => 'Abs_Limit'),
            'softLimit' => array('string', 'nativeName' => 'Soft_Limit'),
            'uploadLimit' => array('string', 'nativeName' => 'Upload_Limit'),
        );
    }
}