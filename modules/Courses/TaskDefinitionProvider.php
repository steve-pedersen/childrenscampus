<?php

/**
 */
class Ccheckin_Courses_TaskDefinitionProvider extends Bss_AuthZ_TaskDefinitionProvider
{
    public function getTaskDefinitions ()
    {
        return array(
            'course request' => 'Ability to request a course be created.',
            'course create' => 'Ability to create a course.',
            'course edit' => 'Ability to edit a course.',
            'course view' => 'Ability to view a course.',
        );
    }
}