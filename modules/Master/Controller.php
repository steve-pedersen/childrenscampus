<?php

/**
 */
abstract class Ccheckin_Master_Controller extends Bss_Master_Controller
{
    protected function getTemplateClass () { return 'Ccheckin_Master_Template'; }
    
    private $userContext;
    private $onLoadScriptList;
    private $includeScriptList;
    private $userMessageList;
    private $pageTitle = array();
    
    protected function initController ()
    {
        parent::initController();
        $this->template->userContext = $this->getUserContext();
        $this->template->viewer = $this->getAccount();
        
        
        $authZ = $this->getAuthorizationManager();
        $authZ->addSource('session',
            new Bss_AuthZ_SessionPermissionSource($authZ, array(
                'session' => $this->request->getSession(),
            ))
        );

        // $masterModule = $this->getApplication()->moduleManager->getModule('at:ccheckin:master');
        // $this->template->setMasterTemplate($masterModule->getResource('master.html.tpl'));

		if (($locator = $this->getRouteVariable('_locator')))
		{
			$this->addTemplateFileLocator($locator);

            // if has admin permission load master
			$this->template->setMasterTemplate($locator->getPathToTemplate('master'));
            // else load kiosk mode
            // $this->template->setMasterTemplate($locator->getPathToTemplate('kiosk'));
		}
        // if ($this->isKiosk())
        // {
        //     $diva = Diva::GetInstance();
            
        //     if (!$diva->user->hasPermission('admin'))
        //     {
        //         $this->template->setTemplateFile('kiosk.tpl');
        //     }
        // }		
		$this->template->controller = $this;
    }

    protected function flash ($content) {
        $session = $this->request->getSession();
        $session->flashContent = $content;
    }

    protected function beforeCallback ($callback)
    {
        parent::beforeCallback($callback);
        $this->setupTemplatePermissions();
    }

    protected function afterCallback ($callback)
    {
        $this->template->onLoad = $this->onLoadScriptList;
        $this->template->userMessageList = $this->userMessageList;
        $this->template->includeScripts = $this->includeScriptList;
        $this->template->analyticsCode = $this->getApplication()->configuration->getProperty('analyticsTrackingCode');
        $this->template->setPageTitle(!empty($this->pageTitle) ? implode(' - ', $this->pageTitle) : '');
    

        $siteSettings = $this->getApplication()->siteSettings;
        if ($contactInfo = $siteSettings->getProperty('contact-info'))
        {
            $this->template->contactInfo = $contactInfo;
        }

        $session = $this->request->getSession();
        if (isset($session->flashContent))
        {
            $this->template->flashContent = $session->flashContent;
            unset($session->flashContent);
        }

        parent::afterCallback($callback);
    }

    public function setupTemplatePermissions ()
    {
        // add 'Home' breadcrumb so long as not on homepage
        $path = $this->request->getFullRequestedUri();
        if (($path !== '') && ($path !== '/') && ($path !== '/home') && ($path !== '/ccheckin') &&
            ($path !== '/ccheckin/') && ($path !== '/ccheckin/home'))
        {
            $this->addBreadcrumb('home', 'Home');
        }
        $homePage = false;
        if ($path === '' || $path === '/' || $path === '/ccheckin' || $path === '/ccheckin/')
        {
            $homePage = true;
        }
        $this->template->homePage = $homePage;

        // if admin and on admin page, don't display 'Contact' sidebar
        $adminPage = false;
        if (!$homePage && $this->hasPermission('admin') && (strpos($path, 'admin') !== false))
        {
            $adminPage = true;
        }
        $this->template->adminPage = $adminPage; 
        $this->template->pAdmin = $this->hasPermission('admin');
        $this->template->isTeacher = $this->hasPermission('course request');
        $this->template->isCCTeacher = $this->hasPermission('room view schedule');

    }

    public function convertToDateTimes ($strings)
    {
        $dates = array();
        if ($strings)
        {
            foreach ($strings as $i => $day)
            {
                $dates[$i] = new DateTime($day);
            }            
        }
        return $dates;       
    }
   
    public function userMessage ($primary, $details = null)
    {
        $this->userMessageList[] = array(
            'primary' => $primary,
            'details' => (array) $details,
        );
    }
    
    public function includeScript ($js)
    {
        $this->includeScriptList[] = $js;
    }
    
    public function addLoadScript ($js)
    {
        $this->onLoadScriptList[] = $js;
    }

    public function addToPageTitle ($piece)
    {
        $this->pageTitle[] = $piece;
    }

    public function overridePageTitle ($title)
    {
        $this->pageTitle = (array)$title;
    }
    
    public function getUserContext ()
    {
        if ($this->userContext == null)
        {
            $this->userContext = new Ccheckin_Master_UserContext($this->request, $this->response);
        }
        
        return $this->userContext;
    }
    
    public function grantPermission ($taskList, $object = Bss_AuthZ_Manager::SYSTEM_ENTITY)
    {
        if (($account = $this->getAccount()))
        {
            $taskList = (array) $taskList;
            $authZ = $this->getAuthorizationManager();
            
            foreach ($taskList as $task)
            {
                $authZ->grantPermission($account, $task, $object, false);
            }
            
            $authZ->updateCache();
            return true;
        }
        
        return false;
    }
    
    public function revokePermission ($taskList, $object = Bss_AuthZ_Manager::SYSTEM_ENTITY)
    {
        if (($account = $this->getAccount()))
        {
            $taskList = (array) $taskList;
            $authZ = $this->getAuthorizationManager();
            
            foreach ($taskList as $task)
            {
                $authZ->revokePermission($account, $task, $object, false);
            }
            
            $authZ->updateCache();
            return true;
        }
        
        return false;
    }
    
    public function getAccount ()
    {
        return $this->getUserContext()->getAccount();
    }
    
    public function requireLogin ()
    {
        if (!($account = $this->getAccount()))
        {
            $this->triggerError('Bss_AuthN_ExLoginRequired');
        }
        
        return $account;
    }

	public function requireExists ($entity, $suggestionList = array())
	{
		if ($entity === null)
		{
			$this->notFound($suggestionList);
		}
		
		return $entity;
	}
    
    public function processSubmission (Bss_ActiveRecord_Base $record, $fieldMap, $paramMap = array())
    {
        $skipIfEmpty = ($paramMap && !empty($paramMap['skipIfEmpty']));
        
        foreach ($fieldMap as $fieldName => $propertyName)
        {
            if (is_numeric($fieldName))
            {
                $fieldName = $propertyName;
            }
            
            $value = $this->request->getPostParameter($fieldName);
            
            if (!$skipIfEmpty || !empty($value))
            {
                $record->setProperty($propertyName, $value);
            }
        }
        
        if (!$this->request->wasPostedByUser())
        {
            $this->userMessage('Form submission out of date.', 'Please resubmit the form to save your changes.');
        }

		$this->template->errorMap = $record->getValidationMessages();
		return $this->request->wasPostedByUser() && $record->isValid();
    }
    
    /**
     */
    public function whoCan ($task, $object = Bss_AuthZ_Manager::SYSTEM_ENTITY, $paramMap = array())
    {
        $recordClass = (isset($paramMap['recordClass']) ? $paramMap['recordClass'] : 'Bss_AuthN_Account');
        
        return $this->schema($recordClass)->getByAzids(
            $this->getAuthorizationManager()->getSubjectsWhoCan($task, $object),
            null, // No additional filtering.
            $paramMap
        );
    }
    
    /**
     */
    public function publishActivity ($toWho, $typeCode, $by, $lede, $message = '', $isPublic = true)
    {
        return Diva_ActivityStream_Activity::newActivity($this->getApplication()->schemaManager, array(
            'account' => $toWho,
            'typeCode' => $typeCode,
            'isPublic' => $isPublic,
            'dated' => new DateTime,
            'publishedBy' => $by,
            'lede' => $lede,
            'message' => $message,
        ));
    }
   
    protected function logActivity ($user, $course, $description, $url)
    {
        
    }

    /**
     * Check if a provided e-mail address is well-formed.
     * 
     * The regex used here is far from perfect or comprehensive, but is good
     * enough for the vast majority of cases that we're interested in.
     * 
     * @param string $email
     * @return bool
     */
    public function validEmailAddress ($email)
    {
        return preg_match(
            '/^([0-9a-zA-Z]+[-._+&])*[0-9a-zA-Z]+@([-0-9a-zA-Z]+[.])+[a-zA-Z]{2,6}$/',
            $email
        ) === 1;
    }
     
    public function createEmailTemplate ()
    {
        $template = $this->createTemplateInstance();
        $template->setMasterTemplate(Bss_Core_PathUtils::path(dirname(__FILE__), 'resources', 'email.html.tpl'));
        return $template;
    }
    
    
    public function createEmailMessage ($contentTemplate = null)
    {
        $message = new Bss_Mailer_Message($this->getApplication());

        if ($contentTemplate)
        {
            $tpl = $this->createEmailTemplate();
            $message->setTemplate($tpl, $this->getModule()->getResource($contentTemplate));
        }
        
        return $message;
    }

    protected function isKiosk ()
    {
        $cookieName = 'cc-kiosk';
        $cookieValue = 'kiosk';
        return (isset($_COOKIE[$cookieName]) && $_COOKIE[$cookieName] == $cookieValue);
    }
}
