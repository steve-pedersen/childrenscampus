<?php

class Ccheckin_Courses_Course extends Bss_ActiveRecord_BaseWithAuthorization //implements Bss_AuthZ_IObjectProxy
{
    private $_teachers;
    private $_students;
    private $_droppedStudents;

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
            'externalCourseKey' => array('string', 'nativeName' => 'external_course_key'),

            'facets' => array('1:N', 'to' => 'Ccheckin_Courses_Facet', 'reverseOf' => 'course', 'orderBy' => array('created_date')), 

            'enrollments' => array('N:M',
                'to' => 'Bss_AuthN_Account',
                'via' => 'ccheckin_course_enrollment_map',
                'fromPrefix' => 'course',
                'toPrefix' => 'account',
                'properties' => array('term' => 'string', 'role' => 'string', 'enrollment_method' => 'string', 'drop_date' => 'datetime')
            ),
        );
    }

    protected function initialize ()
    {
        parent::initialize();
        $this->addEventHandler('before-delete', array($this, 'beforeDelete'));
        // $this->addEventHandler('before-insert', array($this, 'beforeInsert'));
    }

    public function getCollege ()
    {
        list($dept, $college) = $this->convertShortNameToDept($this->shortName, true);

        return $college;
    }

    public function convertShortNameToDept ($shortName, $includeCollege=false)
    {
        $dept = '';
        $college = '';
        $service = new Ccheckin_ClassData_Service($this->getApplication());
        list($status, $orgs) = $service->getOrganizations();

        if ($status < 400)
        {
            $abbr = str_replace('_', ' ', substr($shortName, 0, strpos($shortName, '-')));

            foreach ($orgs as $key => $org)
            {
                $deptKey = substr($key, strpos($key, '- ') + 2);
                if ($deptKey === $abbr)
                {
                    $dept = $org['name'];
                    $college = array_shift($org['college']);
                }
            }
        }

        return $includeCollege ? array($dept, $college) : $dept;
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

    public function getStudents ($reload=false, $includeDropped=false)
    {
        if ($this->_students === null || $reload)
        {
            $enrollments = array();

            foreach ($this->enrollments as $enrollment)
            {
                if ($this->enrollments->getProperty($enrollment, 'role') === 'Student')
                {
                    if (($this->enrollments->getProperty($enrollment, 'drop_date') === null) || $includeDropped)
                    {
                        $enrollments['students'][] = $enrollment;
                    }        
                }
            }
            $this->_students = $enrollments['students'];
        }
        
        return $this->_students;
    }

    public function getDroppedStudents ($reload=false)
    {
        if ($this->_droppedStudents === null || $reload)
        {
            $enrollments = array();

            foreach ($this->enrollments as $enrollment)
            {
                if ($this->enrollments->getProperty($enrollment, 'role') === 'Student')
                {
                    if ($this->enrollments->getProperty($enrollment, 'drop_date') !== null)
                    {
                        $enrollments['students'][] = $enrollment;
                    }                   
                }
            }
            $this->_droppedStudents = $enrollments['students'];
        }
        
        return $this->_droppedStudents;
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


    // public function getObjectProxies () {}

    // public function setDepartment ($department)
    // {
    //     $dept = $department;

    //     if (!$this->department && $department === $this->shortName)
    //     {
    //         $dept = $this->convertShortNameToDept($department);
    //     }

    //     $this->_assign('department', $dept);
    // }

    // public function getDepartment ($fetchNew=false)
    // {
    //     $department = $this->_fetch('department');
    //     if ($fetchNew)
    //     {
    //         $department = $this->convertShortNameToDept($this->shortName);
    //     }

    //     return $department;
    // }

}