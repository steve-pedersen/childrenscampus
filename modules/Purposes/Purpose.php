<?php

class Ccheckin_Purposes_Purpose extends Bss_ActiveRecord_BaseWithAuthorization // implements Bss_AuthZ_IObjectProxy
{
    public static function SchemaInfo ()
    {
        return array(
            // '__class' => 'Ccheckin_Purposes_Purpose',
            '__type' => 'ccheckin_purposes',
            '__azidPrefix' => 'at:ccheckin:purposes/Purpose/',
            '__pk' => array('id'),
            
            'id' => 'int',
            'objectId' => array('int', 'nativeName' => 'object_id'),
            'objectType' => array('string', 'nativeName' => 'object_type'),

            'observations' => array('1:N', 'to' => 'Ccheckin_Rooms_Observations', 'reverseOf' => 'purpose', 'orderBy' => array('start_time')),
        );
    }

    // What is an object property of a purpose anyway?
    // TODO: Figure out what the hell this function is trying to actually do
    public function getObject ()
    {
        $purposes = $this->schema('Ccheckin_Purposes_Purpose');
        $result = null;
        $type = $this->getProperty('object_type');
        $id = $this->getProperty('object_id');
        $object = null;       

        if ($type)
        {
            $refClass = new ReflectionClass($type);
            $object = $refClass->newInstance($this->getDataSource('Ccheckin_Purposes_Purpose'));    // changed arg from $this->_dataSource | TODO: Needs Testing ********
        }
        
        if ($object && ($object=$object->get($id))) 
        {
            $result = $object;
        }
        
        return $result;
    }
    
    // What is $this->object?
    public function getShortDescription ()
    {
        return $this->object->shortDescription;
    }
    
    public function setObject ($object)
    {
        if ($object instanceof Bss_ActiveRecord_BaseWithAuthorization && $object->inDataSource) // TODO: Test ****************
        {
            $id = $object->id;
            $type = get_class($object);
            
            if ($id && $type)
            {
                $this->setProperty('object_id', $id);
                $this->setProperty('object_type', $type);
            }
        }
    }

}