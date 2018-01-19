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
            'tasks' => 'string',	// WAS of type SERIALIZED
            'studentHours' => array('int', 'nativeName' => 'student_hours'),
            'createdDate' => array('datetime', 'nativeName' => 'created_date'),         

            'course' => array('1:1', 'to' => 'Ccheckin_Courses_Course', 'keyMap' => array('course_id' => 'id')),
            'type' => array('1:1', 'to' => 'Ccheckin_Courses_FacetType', 'keyMap' => array('type_id' => 'id')),
        );
    }

	public static function GetAllTasks ()
	{
		if (self::$Tasks == null)
		{
			self::$Tasks = array(
				'clawc' => 'Create and lead an activity with children',
				'pocfoo' => 'Pick one child to focus on for observations',
				'ic' => 'Interview a child',
	//			'ip' => 'Interview a parent',
				'iht' => 'Interview the Head Teacher',
				'tpc' => 'Take photos of a child/children',
				'tvc' => 'Take video of a child/children',
				'cdrdprc' => 'Complete a DRDP-r of a child',
				'cpc' => 'Complete a portfolio of a child',
				'cdba' => 'Create a documentation board of an activity',
				'ceic' => 'Complete the ECERS or ITERS on the classroom',
			);
		}
		
		return self::$Tasks;
	}
	
    // NOTE: Figure this out...
	public function getTasks ()
	{
        return array(); // added this to get the page working temporarily
		$tasks = $this->getProperty('tasks'); // TODO: Unserialize
		
		return ($tasks ? $tasks : array());
	}
    
    public function getShortDescription ()
    {
        return "{$this->type->name} for {$this->course->shortName}";
    }
    
    // use RichText/HtmkSanitizer.php:$this->sanitize() instead of convertSmartQuotes() ???
    // $sanitizer = new Bss_RichText_HtmlSanitizer;
    // $sanitized_string = $sanitizer->sanitize($some_string);
    public function validate ()
    {
        $errors = array();
        
        if (!$this->typeId)
        {
            $errors['facet_type'] = 'You need to select a type of course';
        }

        if ($this->description)
        {
            // $this->description = iconv("Windows-1252", "UTF-8", $this->convertSmartQuotes($this->description));
            $sanitizer = new Bss_RichText_HtmlSanitizer;
            $this->description = $sanitizer->sanitize($this->description);
        }
        
        return $errors;
    }

}