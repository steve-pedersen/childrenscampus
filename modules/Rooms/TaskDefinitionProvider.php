<?php

/**
 */
class Ccheckin_Rooms_TaskDefinitionProvider extends Bss_AuthZ_TaskDefinitionProvider
{
    public function getTaskDefinitions ()
    {
        return array(
            'room reserve' => 'Ability to reserve a room.',
            'room create' => 'Ability to create a room.',
            'room edit' => 'Ability to edit a room.',
            'room delete' => 'Ability to view a room.',
            'room view schedule' => 'Ability to view a room\'s schedule.',
        );
    }
}