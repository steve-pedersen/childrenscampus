<?php

class Ccheckin_Courses_Request extends Bss_ActiveRecord_BaseWithAuthorization // implements Bss_AuthZ_IObjectProxy
{
    public static function SchemaInfo ()
    {
        return array(
            '__type' => 'ccheckin_course_requests',
            '__pk' => array('id'),
            
            'id' => 'int',
            'courseId' => array('int', 'nativeName' => 'course_id'),
            // 'courseUsers' => array('string', 'nativeName' => 'course_users'),    // Serialized
            'requestDate' => array('datetime', 'nativeName' => 'request_date'),
            'requestedById' => array('int', 'nativeName' => 'request_by_id'),

            'course' => array('1:1', 'to' => 'Ccheckin_Courses_Course', 'keyMap' => array('course_id' => 'id')),
            'requestedBy' => array('1:1', 'to' => 'Bss_AuthN_Account', 'keyMap' => array('request_by_id' => 'id')),
        );
    }

    public function setCourseUsers ($courseUsers)
    {
        echo "<pre>"; var_dump('in Request CourseUsers setter func.', $courseUsers); die;
        $this->_assign('courseUsers', json_encode($courseUsers));
    }

    public function getCourseUsers ()
    {
        $users = $this->_fetch('courseUsers');
        $users = json_decode($users, true);
        
        return ($users ? $users : array());
    }

}