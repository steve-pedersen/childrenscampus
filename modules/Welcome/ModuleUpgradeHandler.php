<?php

/**
 * Create the configuration options, which in this case consists of customizable text for the website.
 * 
 * @author      Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University
 */
class Ccheckin_Welcome_ModuleUpgradeHandler extends Bss_ActiveRecord_BaseModuleUpgradeHandler
{
    public function onModuleUpgrade ($fromVersion)
    {
        $siteSettings = $this->getApplication()->siteSettings;
        switch ($fromVersion)
        {
            case 0:
                $siteSettings->defineProperty('welcome-text', 'Text to show on login page and homepage once signed in.', 'string');
                $siteSettings->defineProperty('welcome-title', 'Title to show on page before signed in.', 'string');
                $siteSettings->defineProperty('welcome-text-extended', 'Additional welcome text for logged in user homepage.', 'string');
                $siteSettings->defineProperty('announcements', 'List of sentences used as announcements on homepage. JSON encoded.', 'string');
                $siteSettings->defineProperty('location-message', 'Text featured below image of Childrens Campus on homepage.', 'string');
                break;
        }
    }
}