<?php

class Ccheckin_Welcome_AdminController extends At_Admin_Controller
{
    public static function getRouteMap ()
    {
        return array(
            'admin/welcome' => array('callback' => 'adminWelcome'),
        );
    }

    protected function beforeCallback ($callback)
    {
        parent::beforeCallback($callback);
        $this->template->clearBreadcrumbs();
        $this->addBreadcrumb('home', 'Home');
        $this->addBreadcrumb('admin', 'Admin');
        // if admin and on admin page, don't display 'Contact' sidebar
        $this->template->adminPage = $this->hasPermission('admin') && (strpos($this->request->getFullRequestedUri(), 'admin') !== false); 
    }
  
    public function adminWelcome ()
    {
        $siteSettings = $this->getApplication()->siteSettings;
        
        if ($this->getPostCommand() == 'save' && $this->request->wasPostedByUser())
        {           
            $siteSettings->setProperty('welcome-text', $this->request->getPostParameter('welcome-text'));
            $siteSettings->setProperty('welcome-title', $this->request->getPostParameter('welcome-title'));
            $siteSettings->setProperty('welcome-text-extended', $this->request->getPostParameter('welcome-text-extended'));
            $siteSettings->setProperty('location-message', $this->request->getPostParameter('location-message'));
            $siteSettings->setProperty('contact-info', $this->request->getPostParameter('contact-info'));
        }

        if ($this->request->wasPostedByUser())
        {
            if ($command = $this->getPostCommand())
            {          
                switch ($command)
                {
                    case 'remove':
                        $siteAnnouncements = json_decode($siteSettings->getProperty('announcements'), true);
                        if ($announcementsToRemove = $this->request->getPostParameter('announcements'))
                        {
                            foreach ($announcementsToRemove as $announcekey => $announce)
                            {   
                                unset($siteAnnouncements[$announcekey]);
                                $updatedAnnouncements = array_values($siteAnnouncements);
                            }

                            $siteSettings->setProperty('announcements', json_encode($updatedAnnouncements));
                            $this->flash('The site announcements have been deleted');
                        }
                        break;

                    case 'add':           
                        $updatedAnnouncements = json_decode($siteSettings->getProperty('announcements'), true);
                        if ($newAnnouncement = $this->request->getPostParameter('announcement'))
                        {
                            $updatedAnnouncements[] = $newAnnouncement;
                            $siteSettings->setProperty('announcements', json_encode($updatedAnnouncements));
                            $this->flash('Site announcement created');
                        }
                        break;
                }
            }
        }

        $this->template->welcomeText = $siteSettings->getProperty('welcome-text');
        $this->template->welcomeTitle = $siteSettings->getProperty('welcome-title');
        $this->template->welcomeTextExtended = $siteSettings->getProperty('welcome-text-extended');
        $this->template->announcements = json_decode($siteSettings->getProperty('announcements'), true);
        $this->template->locationMessage = $siteSettings->getProperty('location-message');
        $this->template->contactInfo = $siteSettings->getProperty('contact-info');
    }
}