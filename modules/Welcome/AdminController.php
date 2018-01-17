<?php

class Ccheckin_Welcome_AdminController extends Ccheckin_Master_Controller
{
    public static function getRouteMap ()
    {
        return array(
            'admin/welcome' => array('callback' => 'adminWelcome'),
        );
    }

	public function beforeCallback ($callback)
	{
		parent::beforeCallback($callback);
		$this->requirePermission('admin');
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
        }
        
        if ($welcomeText = $siteSettings->getProperty('welcome-text'))
        {
            $this->template->welcomeText = $welcomeText;
        }
    }
}