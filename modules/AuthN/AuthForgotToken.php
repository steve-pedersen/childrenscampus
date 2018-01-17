<?php

/**
 */
class Ccheckin_AuthN_AuthForgotToken extends Bss_ActiveRecord_BaseWithAuthorization
{
    public static function SchemaInfo ()
    {
        return array(
            '__type' => 'ccheckin_auth_forgot_tokens',
            '__pk' => array('id'),
            
            'id' => 'int',
            'code' => 'string',
            'dated' => 'datetime',
        );
    }

}