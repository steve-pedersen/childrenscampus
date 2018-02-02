<?php

class Ccheckin_Courses_Course extends Bss_ActiveRecord_BaseWithAuthorization // implements Bss_AuthZ_IObjectProxy
{
    public static function SchemaInfo ()
    {
        return array(
            // '__class' => 'Ccheckin_Courses_Course',
            '__type' => 'ccheckin_courses',
            '__azidPrefix' => 'at:ccheckin:courses/Course/',
            '__pk' => array('id'),
            
            'id' => 'int',
            'fullName' => array('string', 'nativeName' => 'full_name'),
            'shortName' => array('string', 'nativeName' => 'short_name'),
            'department' => array('string'),
            'startDate' => array('datetime', 'nativeName' => 'start_date'),
            'endDate' => array('datetime', 'nativeName' => 'end_date'),
            'active' => 'bool',

            'facets' => array('1:N', 'to' => 'Ccheckin_Courses_Facet', 'reverseOf' => 'course', 'orderBy' => array('created_date')), 
            'instructors' => array('1:N', 'to' => 'Ccheckin_Courses_Instructor', 'reverseOf' => 'course'),

            'enrollments' => array('N:M',
                'to' => 'Bss_AuthN_Account',
                'via' => 'ccheckin_course_enrollment_map',
                'fromPrefix' => 'course',
                'toPrefix' => 'account',
                'properties' => array('term' => 'string', 'role' => 'string', 'enrollment_method' => 'string')
            ),
        );
    }

    protected function initialize ()
    {
        $this->addEventHandler('before-delete', array($this, 'beforeDelete'));
    }

    public function getSemester ()
    {
        $semesters = $this->schema('Ccheckin_Semesters_Semester');
        $semester = $semesters->find(
            $semesters->startDate->equals($this->startDate)                
        );

        return $semester;

        // // Old Code
        // $table = Semester::GetTable();
        // $semProto = new Semester($this->_dataSource);
        // $query = $table->getSelectQuery(null);
        // $query->where($table->startDate->equalsTo($this->startDate));
        // $result = $semProto->fullCustomQuery($query);
        
        // return (!empty($result) ? $result[0] : null);
    }
    

    // Not sure if this fixes things
    public function getStudents ()
    {
        $authZ = $this->getAuthorizationManager();
        $accounts = $this->schema('Bss_AuthN_Account');
        $userAzids = $authZ->getSubjectsWhoCan('purpose have', $course);
        $this->_students = $accounts->getByAzids($userAzids);

        return $this->_students;

        
        // Old Code
        if ($this->_students === null)
        {
            $students = array();
            
            foreach ($this->facets as $facet)
            {
                // gets all accounts who have the 'purpose have' permission
                $students = array_merge($students, $facet->purpose->whoCan('purpose have'));
            }
            
            $this->_students = new DormRecordSet($students);
        }
        
        return $this->_students;
    }
    
    public function studentCanParticipate ($student)
    {
        // Old code
        $participate = false;
        
        foreach ($this->facets as $facet)
        {
            if ($facet->userCanParticipate($student))
            {
                $participate = true;
            }
        }
        
        return $participate;
    }
    
    // use RichText/HtmlSanitizer.php:$this->sanitize() instead of convertSmartQuotes() ???
    // $sanitizer = new Bss_RichText_HtmlSanitizer;
    // $sanitized_string = $sanitizer->sanitize($some_string);
    public function validate ()
    {
        $errors = array();
        $sanitizer = new Bss_RichText_HtmlSanitizer;
        
        if (!$this->fullName)
        {
            $errors['fullName'] = 'Please provide a full name for the course.';
        }
        // else
        // {
        //     // $this->fullName = iconv("Windows-1252", "UTF-8", $this->convertSmartQuotes($this->fullName));
        //     $this->fullName = $sanitizer->sanitize($this->fullName);
        // }
        
        if (!$this->shortName)
        {
            $errors['shortName'] = 'Please provide a short name for the course.';
        }
        // else
        // {
        //     // $this->shortName = iconv("Windows-1252", "UTF-8", $this->convertSmartQuotes($this->shortName));
        //     $this->shortName = $sanitizer->sanitize($this->shortName);
        // }
        
        if (!$this->startDate || !($this->startDate instanceof DateTime))
        {
            $errors['startDate'] = 'You must specify a semester';
        }

        return $errors;
    }
    
    protected function beforeDelete ()
    {
        // parent::beforeDelete(); // there is no parent beforeDelete() anymore
        
        foreach ($this->facets as $facet)
        {
            $facet->delete();
        }
        
        foreach ($this->instructors as $instructor)
        {
            $instructor->delete();
        }
    }

}