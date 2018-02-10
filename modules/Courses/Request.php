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

    public function getCourseEnrollments ()
    {
        $enrollments = array();
        $allEnrolled = $this->course->enrollments;
        foreach ($allEnrolled as $enrollment)
        {
            if ($allEnrolled->getProperty($enrollment, 'role') === 'Student')
            {
                $enrollments['students'][] = $enrollment;
            }
            elseif ($allEnrolled->getProperty($enrollment, 'role') === 'Teacher')
            {
                $enrollments['teachers'][] = $enrollment;
            }
            // $enrollments[] = $enrollment;
        }
        return $enrollments;
    }

}