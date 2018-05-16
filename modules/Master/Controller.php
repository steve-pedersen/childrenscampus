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
        $viewer = $this->getAccount();
        $app = $this->getApplication();
        $this->template->viewer = $viewer;

        $authZ = $this->getAuthorizationManager();
        $authZ->addSource('session',
            new Bss_AuthZ_SessionPermissionSource($authZ, array(
                'session' => $this->request->getSession(),
            ))
        );

        if ($this->isKiosk())
        {
            if (!$this->hasPermission('admin'))
            {
                $this->template->setMasterTemplate(Bss_Core_PathUtils::path(dirname(__FILE__), 'resources', 'kiosk.html.tpl'));
                $this->template->kioskMode = true;

                if ($viewer && (strpos($this->request->getFullRequestedUri(), 'kiosk/logout') === false))
                {
                    $this->runKiosk();
                }
            }
            else
            {
                $this->template->setMasterTemplate(Bss_Core_PathUtils::path(dirname(__FILE__), 'resources', 'master.html.tpl'));
            }
        }
        else
        {
            $this->template->kioskMode = false;
        }           
        $this->template->controller = $this;

    }

    protected function isKiosk ()
    {
        $cookieName = 'cc-kiosk';
        $cookieValue = 'kiosk';
        return (isset($_COOKIE[$cookieName]) && $_COOKIE[$cookieName] == $cookieValue);
    }

    public function runKiosk ()
    {
        $viewer = $this->getAccount();
        if (!$viewer)
        {
            // Display an error page.
            $this->template->loginError = true;
            return;
        }
        if ($this->hasPermission('admin'))
        {
            $this->response->redirect('admin');
        }
        
        $accounts = $this->schema('Bss_AuthN_Account');
        $resSchema = $this->schema('Ccheckin_Rooms_Reservation');     
        $now = new DateTime;
        $redirectTime = 15;

        // Find any reservations where the user has checked in
        $cond = $resSchema->allTrue(
            $resSchema->accountId->equals($viewer->id),
            $resSchema->checkedIn->isTrue()
        );
        $reservations = $resSchema->find($cond, array('orderBy' => 'id'));
        
        // check the user out
        if (!empty($reservations))
        {
            $reservation = is_array($reservations) ? $reservations[0] : $reservations;
            $observation = $reservation->observation;
            $observation->endTime = $now;
            $st = $observation->startTime;

            $sameSession = false;
            
            if ($now->format('m')===$st->format('m') && $now->format('d')===$st->format('d') && 
                ($observation->startTime < $now && $now < $reservation->endTime))
            {
                $sameSession = true;
            }
            
            if ($sameSession && ($now->format('G') === $st->format('G')))
            {
                $duration = intval($now->format('i') - $st->format('i'));
            }
            elseif ($sameSession && ($now->format('G') > $st->format('G')))
            {
                $duration = intval($now->format('i') - $st->format('i') + 60);
            }
            else
            {   // default the duration length to when reservation was set to end minus when they actually checked in
                $duration = ($reservation->endTime->format('G')-$st->format('G') - ($st->format('i')/60)) * 60;
            }

            if ($sameSession && $duration < 5)
            {
                $this->template->earlycheckout = 'earlycheckout';
            }
            else
            {
                $observation->duration = $duration;
                $observation->save();
                $reservation->delete();              
                $this->template->checkedOut = 'checkedOut';
            }
        }
        else // check the user in
        {
            $startWindow = new DateTime('-30 minutes');
            $endWindow = new DateTime('+30 minutes');
            
            // Find all reservations for the user within a thirty minute window.           
            $cond = $resSchema->allTrue(
                $resSchema->startTime->afterOrEquals($startWindow),
                $resSchema->startTime->beforeOrEquals($endWindow),
                $resSchema->accountId->equals($viewer->id),
                $resSchema->checkedIn->isFalse()
            );
            $reservations = $resSchema->find($cond, array('orderBy' => 'id'));
            
            if (!empty($reservations))
            {
                $reservation = $reservations[0];
                $observation = $reservation->observation;
                $observation->startTime = $now;
                $observation->save();
                $reservation->checkedIn = true;
                $reservation->reminderSent = true; // they've checked in, so make sure no reminder emails sent
                $reservation->save();
                $this->template->reservation = $reservation;
            }
            else
            {
                $lateCondition = $resSchema->allTrue(
                    $resSchema->startTime->before($startWindow),
                    $resSchema->missed->isFalse(),
                    $resSchema->accountId->equals($viewer->id)
                );
                $late = $resSchema->find($lateCondition);

                $earlyCondition = $resSchema->allTrue(
                    $resSchema->startTime->after($endWindow),
                    $resSchema->accountId->equals($viewer->id)
                );
                $early = $resSchema->find($earlyCondition);
                
                if (!empty($late))
                {
                    $reservation = $late[0];
                    $late['room'] = $reservation->room->name;
                    $late['time'] = $reservation->startTime;
                    $reservation->missed = true;
                    $reservation->save();
                    $redirectTime = 20;
                    $this->template->late = $late;
                }
                
                if (!empty($early))
                {
                    $reservation = $early[0];
                    $early['room'] = $reservation->room->name;
                    $early['time'] = $reservation->startTime;
                    $this->template->early = $early;
                }
                
                if (empty($late) && empty($early))
                {
                    $this->template->empty = 'empty';
                }
            }
        }

        $this->template->metaRedirect = '<meta http-equiv="refresh" content="'.$redirectTime.';URL=' . $this->baseUrl('kiosk/logout') . '">';
        $this->refreshKioskCookie();
    }


    public function refreshKioskCookie ()
    {
        $cookieName = 'cc-kiosk';
        $cookieValue = 'kiosk';

        setCookie($cookieName, false, time()+60*60*24*30*12, '/');
        setCookie($cookieName, $cookieValue, time()+60*60*24*30*12, '/');
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
        $this->template->adminPage = $this->hasPermission('admin') && (strpos($path, 'admin') !== false);
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

}
