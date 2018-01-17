<?php

/**
 */
class Ccheckin_AuthN_AccessLevel extends Bss_ActiveRecord_BaseWithAuthorization
{
    public static function SchemaInfo ()
    {
        return array(
            '__class' => 'Ccheckin_AuthN_AccessLevelSchema',
            '__type' => 'ccheckin_authn_access_levels',
            '__pk' => array('id'),
            '__azidPrefix' => 'at:ccheckin:authN/AccessLevel/',
            
            'id' => 'int',
            'name' => 'string',
            'description' => 'string',
        );
    }
}
