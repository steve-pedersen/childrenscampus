<?php

/**
 */
class Ccheckin_Purposes_TaskDefinitionProvider extends Bss_AuthZ_TaskDefinitionProvider
{
    public function getTaskDefinitions ()
    {
        return array(
            'purpose have' => 'Ability to have a purpose.',
        );
    }
}