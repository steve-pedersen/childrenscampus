<?php

/**
 */
class Ccheckin_AuthN_TaskDefinitionProvider extends Bss_AuthZ_TaskDefinitionProvider
{
    public function getTaskDefinitions ()
    {
        return array(
            'account view' => 'Ability to view an existing account.',
            'account edit' => 'Ability to edit an existing account.',
            'account manage' => 'Ability to change usernames, add/remove roles, and delete accounts.',
            'edit system notifications' => 'Ability to turn on or off admin email notifications such as "Course Requested".',
            'receive system notifications' => 'Ability to receive system email notifications such as "Course Requested".',
        );
    }
}