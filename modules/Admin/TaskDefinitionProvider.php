<?php

/**
 */
class Ccheckin_Admin_TaskDefinitionProvider extends Bss_AuthZ_TaskDefinitionProvider
{
    public function getTaskDefinitions ()
    {
        return array(
            'file download' => 'download the file',
            'file upload' => 'upload the file',
            'reports generate' => 'generate & download reports',
        );
    }
}
