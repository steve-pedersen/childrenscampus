<?php

/**
 * Provides the welcome page.
 * 
 * @author      Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; California State University Trustees.
 */
class Ccheckin_Welcome_Controller extends Ccheckin_Master_Controller
{
	public static function getRouteMap ()
	{
		return array(
				'/' => array('callback' => 'index'),
				'home' => array('callback' => 'index'),
		);
	}
	
	public function index ()
	{

		if ($user = $this->getUserContext()->getAccount())
		{
			// redirect to /home or is that not needed anymore?
		}

		$siteSettings = $this->getApplication()->siteSettings;

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
