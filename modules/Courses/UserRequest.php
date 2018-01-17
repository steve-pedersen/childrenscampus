<?php

class Ccheckin_Courses_UserRequest extends Bss_ActiveRecord_BaseWithAuthorization // implements Bss_AuthZ_IObjectProxy
{
    public static function SchemaInfo ()
    {
        return array(
            '__type' => 'ccheckin_course_user_requests',
            '__pk' => array('id'),
            
            'id' => 'int',
            'courseId' => array('int', 'nativeName' => 'course_id'),
            'requestDate' => array('datetime', 'nativeName' => 'request_date'),
            'users' => 'string',    // Serialized
            'requestedById' => array('int', 'nativeName' => 'request_by_id'),

            'course' => array('1:1', 'to' => 'Ccheckin_Courses_Course', 'keyMap' => array('course_id' => 'id')),
            'requestedBy' => array('1:1', 'to' => 'Bss_AuthN_Account', 'keyMap' => array('request_by_id' => 'id')),
        );
    }

    public function getUsers ()
    {
        $users = $this->getProperty('users');
        
        return ($users ? $users : array());
    }

}