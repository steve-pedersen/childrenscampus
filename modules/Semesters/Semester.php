<?php

class Ccheckin_Semesters_Semester extends Bss_ActiveRecord_BaseWithAuthorization
{
    public static function SchemaInfo ()
    {
        return array(
            // '__class' => 'Ccheckin_Semesters_Semester',
            '__type' => 'ccheckin_semesters',
            '__azidPrefix' => 'at:ccheckin:semesters/Semester/',
            '__pk' => array('id'),
            
            'id' => 'int',
            'display' => 'string',
            'internal' => 'string',
            'startDate' => array('datetime', 'nativeName' => 'start_date'),
            'endDate' => array('datetime', 'nativeName' => 'end_date'),

        );
    }
    
    public static function GetTerms ()
    {
        return array(
            'Fall',
            'Spring',
            'Summer',
            'Winter',
        );
    }
    
    public static function GetYears ($limit = 5)
    {
        $date = new DateTime();
        $year = $date->format('Y');
		$year -= 2;
        $years = array();
        
        for ($i = 0; $i < $limit; $i++)
        {
            $years[] = $year++;
        }
        
        return $years;
    }
 
    // public function getStartDate ()
    // {
    //     $startDate = $this->_fetch('startDate');
        
    //     return $startDate ? $startDate : new DateTime;
    // }

    // public function getEndDate ()
    // {
    //     $endDate = $this->_fetch('endDate');
        
    //     return $endDate ? $endDate : new DateTime;
    // }

    // public function getDisplay ()
    // {
    //     return $this->_fetch('display');
    // }

    // public function getInternal()

    public function setDisplay ($display)
    {
        $this->_assign('display', $display);
        $this->_assign('internal', strtolower(str_replace(' ', '_', $display)));
    }
    
    public function validate ()
    {
        $errors = array();
        
        if (!$this->startDate || !($this->startDate instanceof DateTime))
        {
            $errors['startDate'] = 'You must specify a start date';
        }
        
        if (!$this->endDate || !($this->endDate instanceof DateTime))
        {
            $errors['endDate'] = 'You must specify an end date';
        }
        
        if (!$this->display || !$this->internal)
        {
            $errors['display'] = 'You must specify the semester name and year';
        }
        
        return $errors;
    }
}
