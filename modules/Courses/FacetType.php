<?php

class Ccheckin_Courses_FacetType extends Bss_ActiveRecord_BaseWithAuthorization // implements Bss_AuthZ_IObjectProxy
{
    public static function SchemaInfo ()
    {
        return array(
            '__type' => 'ccheckin_course_facet_types',
            '__pk' => array('id'),
            
            'id' => 'int',
            'name' => 'string',
            'sortName' => array('string', 'nativeName' => 'sort_name'),
        );
    }

    public function setName ($name)
    {
        $this->_assign('name', $name);
        $this->_assign('sortName', strtolower($name));
    }
    
    public function validate ()
    {
        $errors = array();
        
        if (!$this->name || !$this->sortName)
        {
            $errors['display'] = 'You must specify the semester name and year';
        }
        
        return $errors;
    }

}