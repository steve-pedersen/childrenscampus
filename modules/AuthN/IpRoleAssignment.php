<?php

/**
 * Automatically assigns a role to a user coming from a particular IP
 * address.
 * 
 * @author      Daniel A. Koepke (dkoepke@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Ccheckin_AuthN_IpRoleAssignment extends Bss_ActiveRecord_Base
{
    public static function SchemaInfo ()
    {
        return array(
            '__type' => 'ccheckin_authn_role_ip_assignments',
            '__pk' => array('id'),
            
            'id' => 'int',
            'ipAddress' => array('cidr', 'nativeName' => 'ip_address'),
            'role' => array('1:1', 'to' => 'Ccheckin_AuthN_Role'),
            'description' => 'string',
        );
    }
}