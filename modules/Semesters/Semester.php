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
            'startDate' => array('int', 'nativeName' => 'start_date'),
            'endDate' => array('string', 'nativeName' => 'end_date'),

        );
    }
    
    public static function GetSemesters ()
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
        $date = new Date();
        $year = $date->getYear();
		$year -= 2;
        $years = array();
        
        for ($i = 0; $i < $limit; $i++)
        {
            $years[] = $year++;
        }
        
        return $years;
    }
    
    public function setDisplay ($display)
    {
        $this->setProperty('display', $display);
        $this->setProperty('internal', strtolower(str_replace(' ', '_', $display)));
    }
    
    public function validate ()
    {
        $errors = array();
        
        if (!$this->startDate || !($this->startDate instanceof Date))
        {
            $errors['startDate'] = 'You must specify a start date';
        }
        
        if (!$this->endDate || !($this->endDate instanceof Date))
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
