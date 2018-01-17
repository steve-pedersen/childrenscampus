<?php

/**
 */
class Ccheckin_AuthN_AuthJoinRequest extends Bss_ActiveRecord_BaseWithAuthorization
{
    public static function SchemaInfo ()
    {
        return array(
            '__type' => 'ccheckin_join_requests',
            '__pk' => array('id'),
            
            'id' => 'int',
            'firstName' => array('string', 'nativeName' => 'first_name'),
            'lastName' => array('string', 'nativeName' => 'last_name'),
            'email' => 'string',
            'position' => 'string',
            'institution' => 'string',
            'requestDate' => array('datetime', 'nativeName' => 'request_date'),
            'invitationId' => array('int', 'nativeName' => 'invitation_id'),

            'invitation' => array('1:1', 'to' => 'Ccheckin_AuthN_AuthInvitation', 'keyMap' => array('invitation_id' => 'id')),
        );
    }

}