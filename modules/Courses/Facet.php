<?php

class Ccheckin_Courses_Facet extends Ccheckin_Purposes_AbstractPurpose // NOT? extends Bss_ActiveRecord_BaseWithAuthorization implements Bss_AuthZ_IObjectProxy
{
	static $Tasks;

    public static function SchemaInfo ()
    {
        return array(
            '__type' => 'ccheckin_course_facets',
            '__pk' => array('id'),
            
            'id' => 'int',
            'courseId' => array('int', 'nativeName' => 'course_id'),
            'typeId' => array('int', 'nativeName' => 'type_id'),
            'description' => 'string',
            'tasks' => 'string',
            'studentHours' => array('int', 'nativeName' => 'student_hours'),
            'createdDate' => array('datetime', 'nativeName' => 'created_date'),         

            'course' => array('1:1', 'to' => 'Ccheckin_Courses_Course', 'keyMap' => array('course_id' => 'id')),
            'type' => array('1:1', 'to' => 'Ccheckin_Courses_FacetType', 'keyMap' => array('type_id' => 'id')),
        );
    }

	public function GetAllTasks ()
	{
        $siteSettings = $this->getApplication()->siteSettings;
        $tasks = json_decode($siteSettings->getProperty('course-tasks', true));
        
        return ($tasks !== null && $tasks !== 1) ? $tasks : $this->getDefaultTasks();
	}

    public function getDefaultTasks ()
    {
        return array(
            'Create and lead an activity with children',
            'Pick one child to focus on for observations',
            'Interview a child',
            'Interview a parent',
            'Interview the Head Teacher',
            'Take photos of a child/children',
            'Take video of a child/children',
            'Complete a DRDP-r of a child',
            'Complete a portfolio of a child',
            'Create a documentation board of an activity',
            'Complete the ECERS or ITERS on the classroom',
        );
    }

	public function getTasks ($asIs=false)
	{
		if ($asIs)
        {
            return $this->_fetch('tasks');
        }
        $tasks = $this->_fetch('tasks');
		$tasks = json_decode($tasks, true);

		return ($tasks ? $tasks : array());
	}

    public function setTasks($tasks)
    {
        $this->_assign('tasks', json_encode($tasks));
    }  

    public function getShortDescription ()
    {
        return "{$this->type->name} for {$this->course->shortName}";
    }
    
    public function validate ()
    {
        $errors = array();
        
        if (!$this->typeId)
        {
            $errors['facet_type'] = 'You need to select a type of course';
        }
        
        return $errors;
    }

    public function getPurpose ()
    {
        $purposes = $this->getSchema('Ccheckin_Purposes_Purpose');
        $facetPurpose = $purposes->findOne($purposes->objectId->equals($this->id));

        return $facetPurpose;
    }

}