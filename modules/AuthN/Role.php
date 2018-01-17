<?php

/**
 */
class Ccheckin_AuthN_Role extends Bss_ActiveRecord_BaseWithAuthorization
{
    public static function SchemaInfo ()
    {
        return array(
            '__type' => 'ccheckin_authn_roles',
            '__pk' => array('id'),
            '__azidPrefix' => 'at:ccheckin:authN/Role/',
            
            'id' => 'int',
            'name' => 'string',
            'description' => 'string',
            'isSystemRole' => array('bool', 'nativeName' => 'is_system_role'),
            'hidden' => 'bool',
            
            'accounts' => array('N:M', 'to' => 'Bss_AuthN_Account', 'via' => 'ccheckin_authn_account_roles', 'toPrefix' => 'account', 'fromPrefix' => 'role'),
            'ipAssignments' => array('1:N', 'to' => 'Ccheckin_AuthN_IpRoleAssignment', 'reverseOf' => 'role', 'orderBy' => array('+ipAddress', '+id')),
        );
    }
}
