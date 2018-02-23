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
            'openDate' => array('datetime', 'nativeName' => 'open_date'),
            'closeDate' => array('datetime', 'nativeName' => 'close_date'),
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

    public static function ConvertToDescription ($code)
    {
        $term = $code[3];
        $year = $code[0] . '0' . $code[1] . $code[2];

        switch ($term) {
            case 1:
                $term = 'Winter';
                break;
            
            case 3:
                $term = 'Spring';
                break;

            case 5:
                $term = 'Summer';
                break;

            case 7:
                $term = 'Fall';
                break;
        }

        return $term . ' ' . $year;
    }

    public static function guessActiveSemester ($returnTermCode = true)
    {
        $y = date('Y');
        $m = date('n');
        $d = date('d');
        $earlyWinter = false;

        // Winter session ~ Dec 20 to Jan 18
        if (($m < 2 && $d < 18) || ($m == 12 && $d > 20))
        {
            $s = 1; // Winter
            if ($m == 12)
            {
                $earlyWinter = true;
            }
        }
        elseif ($m < 5)
        {
            $s = 3; // Spring
        }
        elseif ($m < 8)
        {
            $s = 5; // Summer
        }
        else
        {
            $s = 7; // Fall
        }

        if ($earlyWinter)
        {
            $y = (string) ($y + 1);
        }
        $y = $y[0] . substr($y, 2);

        return ($returnTermCode ? "$y$s" : array($y, $s));
    }

    public function setDisplay ($display)
    {
        $this->_assign('display', $display);
    }

    public function setInternal ($internal)
    {
        $this->_assign('internal', $internal);
    }

    public function getOpenDate ()
    {
        if ($openDate = $this->_fetch('openDate'))
        {
            return $openDate;
        }
        return $this->_fetch('startDate')->modify('+1 week');
    }
    public function getCloseDate ()
    {
        if ($closeDate = $this->_fetch('closeDate'))
        {
            return $closeDate;
        }
        return $this->_fetch('endDate');
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
