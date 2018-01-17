<?php

/**
 * Upgrade this module.
 * 
 * @author      Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Ccheckin_Admin_ModuleUpgradeHandler extends Bss_ActiveRecord_BaseModuleUpgradeHandler
{
    public function onModuleUpgrade ($fromVersion)
    {
        $app = $this->getApplication();
        $settings = $app->siteSettings;
        
        switch ($fromVersion)
        {
            case 0:
                $settings->defineProperty('siteNotice', 'A highly-visible notice that gets displayed on every page.', 'textarea');
                break;
        }
    }
}