<?php

class Ccheckin_Family_Purpose extends Bss_ActiveRecord_BaseWithAuthorization // implements Bss_AuthZ_IObjectProxy
{
    public static function SchemaInfo ()
    {
        return array(
            '__type' => 'ccheckin_family_purposes',
            '__pk' => array('id'),
            
            'id' => 'int',
            'name' => 'string',
        );
    }

    public function getShortDescription ()
    {
        return $this->name;
    }
    
    public function validate ()
    {
        $errors = array();
        
        if (!$this->name)
        {
            $errors['name'] = 'You must supply a name.';
        }
        
        return $errors;
    }

}