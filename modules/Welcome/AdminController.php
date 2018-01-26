<?php

class Ccheckin_Welcome_AdminController extends At_Admin_Controller
{
    public static function getRouteMap ()
    {
        return array(
            'admin/welcome' => array('callback' => 'adminWelcome'),
        );
    }
    
    public function adminWelcome ()
    {
        $siteSettings = $this->getApplication()->siteSettings;
        $this->addBreadcrumb('admin', 'Administrate');
        
        if ($this->getPostCommand() == 'save' && $this->request->wasPostedByUser())
        {           
            if ($welcomeText = $this->request->getPostParameter('welcome-text'))
            {
                $siteSettings->setProperty('welcome-text', $welcomeText);
            }
            if ($welcomeTitle = $this->request->getPostParameter('welcome-title'))
            {
                $siteSettings->setProperty('welcome-title', $welcomeTitle);
            }
            if ($welcomeTextExtended = $this->request->getPostParameter('welcome-text-extended'))
            {
                $siteSettings->setProperty('welcome-text-extended', $welcomeTextExtended);
            }
            if ($noticeWarning = $this->request->getPostParameter('notice-warning'))
            {
                $siteSettings->setProperty('notice-warning', $noticeWarning);
            }
            if ($noticeMessage = $this->request->getPostParameter('notice-message'))
            {
                $siteSettings->setProperty('notice-message', $noticeMessage);
            }
            if ($locationMessage = $this->request->getPostParameter('location-message'))
            {
                $siteSettings->setProperty('location-message', $locationMessage);
            }
        }
        
        if ($welcomeText = $siteSettings->getProperty('welcome-text'))
        {
            $this->template->welcomeText = $welcomeText;
        }
        if ($welcomeTitle = $siteSettings->getProperty('welcome-title'))
        {
            $this->template->welcomeTitle = $welcomeTitle;
        }
        if ($welcomeTextExtended = $siteSettings->getProperty('welcome-text-extended'))
        {
            $this->template->welcomeTextExtended = $welcomeTextExtended;
        }
        if ($noticeWarning = $siteSettings->getProperty('notice-warning'))
        {
            $this->template->noticeWarning = $noticeWarning;
        }
        if ($noticeMessage = $siteSettings->getProperty('notice-message'))
        {
            $this->template->noticeMessage = $noticeMessage;
        }
        if ($locationMessage = $siteSettings->getProperty('location-message'))
        {
            $this->template->locationMessage = $locationMessage;
        }
    }
}