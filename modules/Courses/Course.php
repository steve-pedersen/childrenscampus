<?php

class Ccheckin_Courses_Course extends Bss_ActiveRecord_BaseWithAuthorization // implements Bss_AuthZ_IObjectProxy
{
    private $_teachers;
    private $_students;

    public static function SchemaInfo ()
    {
        return array(
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
            'deleted' => 'bool',

            'facets' => array('1:N', 'to' => 'Ccheckin_Courses_Facet', 'reverseOf' => 'course', 'orderBy' => array('created_date')), 

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
        $semesters = $this->getSchema('Ccheckin_Semesters_Semester');
        $semester = $semesters->findOne(
            $semesters->startDate->equals($this->startDate)
        );
        
        return $semester;
    }

    public function getFacetType ()
    {
        $facets = $this->getSchema('Ccheckin_Courses_Facet');
        $types = $this->getSchema('Ccheckin_Courses_FacetType');
        $facet = $facets->findOne($facets->courseId->equals($this->id));
        $facetType = $types->findOne($types->id->equals($facet->typeId));

        return $facetType;
    }  

    public function getTeachers ($reload=false)
    {     
        if ($this->_teachers === null || $reload)
        {
            $enrollments = array();

            foreach ($this->enrollments as $enrollment)
            {
                if ($this->enrollments->getProperty($enrollment, 'role') === 'Teacher')
                {
                    $enrollments['teachers'][] = $enrollment;
                }
            }
            $this->_teachers = $enrollments['teachers'];
        }
        
        return $this->_teachers;
    }

    public function getStudents ($reload=false)
    {
        if ($this->_students === null || $reload)
        {
            $enrollments = array();

            foreach ($this->enrollments as $enrollment)
            {
                if ($this->enrollments->getProperty($enrollment, 'role') === 'Student')
                {
                    $enrollments['students'][] = $enrollment;
                }
            }
            $this->_students = $enrollments['students'];
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
    
    public function validate ()
    {
        $errors = array();
        $sanitizer = new Bss_RichText_HtmlSanitizer;
        
        if (!$this->fullName)
        {
            $errors['fullName'] = 'Please provide a full name for the course.';
        }
        
        if (!$this->shortName)
        {
            $errors['shortName'] = 'Please provide a short name for the course.';
        }
        
        if (!$this->startDate || !($this->startDate instanceof DateTime))
        {
            $errors['startDate'] = 'You must specify a semester';
        }

        return $errors;
    }
    

    // TODO: Test Instructor stuff **********************************************
    protected function beforeDelete ()
    {
        foreach ($this->facets as $facet)
        {
            $facet->delete();
        }
        
        $this->deleted = true;
        $this->save();
    }

}