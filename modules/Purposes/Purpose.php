<?php

class Ccheckin_Purposes_Purpose extends Bss_ActiveRecord_BaseWithAuthorization // implements Bss_AuthZ_IObjectProxy
{
    public static function SchemaInfo ()
    {
        return array(
            '__type' => 'ccheckin_purposes',
            '__azidPrefix' => 'at:ccheckin:purposes/Purpose/',
            '__pk' => array('id'),
            
            'id' => 'int',
            'objectId' => array('int', 'nativeName' => 'object_id'),
            'objectType' => array('string', 'nativeName' => 'object_type'),

            'observations' => array('1:N', 'to' => 'Ccheckin_Rooms_Observation', 'reverseOf' => 'purpose', 'orderBy' => array('start_time')),
        );
    }

    public function getObject ()
    {
        $purposes = $this->getSchema('Ccheckin_Purposes_Purpose');
        $result = null;
        $type = $this->_fetch('objectType');
        $id = $this->_fetch('objectId');
        $object = null;       

        if ($type)
        {
            $object = $this->getSchema($type);
        }
        
        if ($object && ($object=$object->get($id))) 
        {
            $result = $object;
        }
        
        return $result;
    }
    
    public function getShortDescription ()
    {
        return $this->object->shortDescription;
    }
    
    public function setObject ($object)
    {
        if ($object instanceof Bss_ActiveRecord_BaseWithAuthorization && $object->inDataSource)
        {
            $id = $object->id;
            $type = get_class($object);
            
            if ($id && $type)
            {
                $this->_assign('objectId', $id);
                $this->_assign('objectType', $type);
            }
        }
    }

}