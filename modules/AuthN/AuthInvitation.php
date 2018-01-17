<?php

/**
 * An invitation to an account.
 * 
 * @author	Daniel A. Koepke (dkoepke@sfsu.edu)
 * @copyright	Copyright &copy; San Francisco State University.
 */
class Ccheckin_AuthN_AuthInvitation extends Bss_ActiveRecord_BaseWithAuthorization
{
    public static function SchemaInfo ()
    {
        return array(
            '__type' => 'ccheckin_auth_invitations',
            '__pk' => array('id'),
            
            'id' => 'int',
            'email' => 'string',
            'code' => array('string', 'nativeName' => 'key_code'),
            'invitedById' => array('int', 'nativeName' => 'invited_by_id'),
            'invitedDate' => array('datetime', 'nativeName' => 'invited_date'),
            'purposeId' => array('int', 'nativeName' => 'purpose_id'),

            'purpose' => array('1:1', 'to' => 'Ccheckin_Purposes_Purpose', 'keyMap' => array('purpose_id' => 'id')),
            'invitedBy' => array('1:1', 'to' => 'Bss_AuthN_Account', 'keyMap' => array('invited_by_id' => 'id')),
        );
    }

	public function generateCode ()
	{
		$code = sha1(uniqid(rand(), true));
		$this->setProperty('code', $code);
		return $code;
	}

}