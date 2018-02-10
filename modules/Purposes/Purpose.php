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

            'observations' => array('1:N', 'to' => 'Ccheckin_Rooms_Observation', 'reverseOf' => 'purpose', 'orderBy' => array('start_time')),
        );
    }

    // What is an object property of a purpose anyway?
    // TODO: Test this..... ************************
    public function getObject ()
    {
        $purposes = $this->schema('Ccheckin_Purposes_Purpose');
        $result = null;
        $type = $this->_fetch('objectType');
        $id = $this->_fetch('objectId');
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
    
    public function getShortDescription ()
    {
        return $this->object->shortDescription;
    }
    
    public function setObject ($object)
    {
        if ($object instanceof Bss_ActiveRecord_BaseWithAuthorization && $object->inDataSource) // TODO: Test **************** 2.8.18 - 1st test works for object=Facet
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