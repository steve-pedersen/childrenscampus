<?php

/**
 * Upgrade this module.
 * 
 * @author      Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Ccheckin_AuthN_ModuleUpgradeHandler extends Bss_ActiveRecord_BaseModuleUpgradeHandler
{
    public function onModuleUpgrade ($fromVersion)
    {
        switch ($fromVersion)
        {
            case 0:
                $this->requireModule('bss:core:authZ', 1);
                /**
                *   Add a few new columns to the accounts table.
                */
                $def = $this->alterEntityType('bss_authn_accounts', $this->getDataSource('Bss_AuthN_Account'));
                $def->addProperty('user_alias', 'string');               
                $def->addProperty('ldap_user', 'string');   // maybe not this one...
                $def->addProperty('is_active', 'bool');
                $def->addProperty('receive_admin_notifications', 'bool');
                $def->save();

                $def = $this->createEntityType('ccheckin_authn_roles', $this->getDataSource('Ccheckin_AuthN_Role'));
                $def->addProperty('id', 'int', array('sequence' => true, 'primaryKey' => true));
                $def->addProperty('name', 'string');
                $def->addProperty('description', 'string');
                $def->addProperty('is_system_role', 'bool');
                $def->addProperty('hidden', 'bool');
                $def->save();

                // M:N mapping for accounts <=> roles
                $def = $this->createEntityType('ccheckin_authn_account_roles', 'Ccheckin_AuthN_Role');
                $def->addProperty('account_id', 'int', array('primaryKey' => true));
                $def->addProperty('role_id', 'int', array('primaryKey' => true));
                $def->addForeignKey('bss_authn_accounts', array('account_id' => 'id'));
                $def->addForeignKey('ccheckin_authn_roles', array('role_id' => 'id'));
                $def->addIndex('account_id');
                $def->save();

                // Create access levels entity.
                $def = $this->createEntityType('ccheckin_authn_access_levels', 'Ccheckin_AuthN_AccessLevel');
                $def->addProperty('id', 'int', array('sequence' => true, 'primaryKey' => true));
                $def->addProperty('name', 'string');
                $def->addProperty('description', 'string');
                $def->save();

                $def = $this->createEntityType('ccheckin_authn_role_ip_assignments', 'Ccheckin_AuthN_IpRoleAssignment');
                $def->addProperty('id', 'int', array('sequence' => true, 'primaryKey' => true));
                $def->addProperty('role_id', 'int');
                $def->addProperty('ip_address', 'cidr');
                $def->addProperty('description', 'string');
                $def->addIndex('ip_address');
                $def->addForeignKey('ccheckin_authn_roles', array('role_id' => 'id'));
                $def->save();

                
                $this->useDataSource('Ccheckin_AuthN_Role');
                // Insert some default data
                $roleIdMap = $this->insertRecords('ccheckin_authn_roles',
                    array(
                        array('name' => 'Anonymous', 'description' => 'Automatically granted to everyone who visits CCheckIn.', 'is_system_role' => true),
                        array('name' => 'Administrator', 'description' => 'Has every possible permission. Assign to anyone you trust with the ability to wreak havoc.', 'is_system_role' => true),
                        array('name' => 'Teacher', 'description' => 'Assign to SF State teachers. Probably CAD teachers.', 'is_system_role' => true),
                        array('name' => 'CC Teacher', 'description' => "Assign to Children\'s Campus teachers. These are the actual children's teachers.", 'is_system_role' => true),
                        array('name' => 'Student', 'description' => 'Assign to Children\'s Campus students.', 'is_system_role' => true),
                    ),
                    array(
                        'idList' => array('id')
                    )
                );
                
                // $this->useDataSource('Ccheckin_AuthN_AccessLevel');  // Do I need this here???

                $levelIdMap = $this->insertRecords('ccheckin_authn_access_levels',
                    array(
                        array('name' => 'Public', 'description' => 'Open to anyone who knows the address. <div class="detail">Good for sharing with the general public.</div>'),
                        array('name' => 'Protected', 'description' => 'Require a password. <div class="detail">Good for protecting course materials that you want to share with students or other large groups.</div>'),
                        array('name' => 'Private', 'description' => 'Only visible to those who have explicitly been granted access. <div class="detail">Good for tightly controlling access.</div>'),
                        array('name' => 'SFSU', 'description' => 'Only visible to those who are SFSU people.'),
                        array('name' => 'CCheckIn-All', 'description' => 'Only visible to all those who are CCheckIn people.'),
                        array('name' => 'Teacher', 'description' => 'Only visible to those who are CCheckIn <strong>teachers</strong>.'),
                        array('name' => 'Student', 'description' => 'Only visible to those who are CCheckIn <strong>students</strong>.'),
                    ),
                    array(
                        'idList' => array('id')
                    )
                );

                break;

        }
    }
}
