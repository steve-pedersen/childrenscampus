<?php

class Ccheckin_Courses_Instructor extends Bss_ActiveRecord_BaseWithAuthorization // implements Bss_AuthZ_IObjectProxy
{
    public static function SchemaInfo ()
    {
        return array(
            '__type' => 'ccheckin_course_instructors',
            '__pk' => array('id'),
            
            'id' => 'int',
            'accountId' => array('int', 'nativeName' => 'account_id'),
            'courseId' => array('int', 'nativeName' => 'course_id'),

            'account' => array('1:1', 'to' => 'Bss_AuthN_Account', 'keyMap' => array('account_id' => 'id')),
            'course' => array('1:1', 'to' => 'Ccheckin_Courses_Course', 'keyMap' => array('course_id' => 'id')),
        );
    }

}