<?php

/**
 */
class Ccheckin_AuthN_Controller extends Ccheckin_Master_Controller
{
    public static function getRouteMap ()
    {
        return array(
            'profile' => array('callback' => 'editProfile'),
            'logout' => array('callback' => 'logout'),
            '/kiosk/logout' => array('callback' => 'kioskLogout'),
        );
    }


    public function kioskLogout ()
    {
        $logoutRedirect = $this->getLogoutRedirect();     
        $this->getUserContext()->logout();

        $this->template->baseUrl = $this->baseUrl();
        $this->template->metaRedirect = '<meta http-equiv="refresh" content="1;URL=' . $this->baseUrl('') . '">';
        $this->template->shibbolethLogout = $logoutRedirect;
    }

    /**
     * Logout a user account and return them to the login page.
     */
    public function logout ()
    {
        $this->template->clearBreadcrumbs();
        $context = $this->getUserContext();

        if ($context->unbecome())
        {
            $this->response->redirect('admin');
        }
        $context->logout();
        $logoutRedirect = $this->getLogoutRedirect();
        $this->template->baseUrl = $this->baseUrl();
        $this->template->metaRedirect = '<meta http-equiv="refresh" content="1;URL=' . $this->baseUrl('') . '">';
        $this->template->shibbolethLogout = $logoutRedirect;
    }

    public function getLogoutRedirect ()
    {
        // session_unset();
        $logoutRedirect = null;
        
        if (($idProviderName = $this->request->getCookie('wayfSettings')))
        {
            $manager = $this->application->identityProviderManager;
            
            if (($idProvider = $manager->getProvider($idProviderName)))
            {
                $logoutRedirect = $idProvider->getLogoutRedirection('/logout');
                $this->template->singleSignOut = $idProvider->hasSingleSignOut();
            }
        }

        if ($logoutRedirect === null)
        {
            $logoutRedirect = 'https://idp-test.sfsu.edu/idp/Logout';
        }

        return $logoutRedirect;
    }

    public function editProfile ()
    {
        $account = $this->requireLogin();

        if ($this->hasPermission('admin'))
        {
            $this->response->redirect('admin/accounts/' . $account->id);
        }

        if (($this->getPostCommand() == 'save') && $this->request->wasPostedByUser())
        {
            $account->firstName = $this->request->getPostParameter('firstname');
            $account->middleName = $this->request->getPostParameter('middlename');
            $account->lastName = $this->request->getPostParameter('lastname');
            if ($this->hasPermission('receive system notifications'))
            {
                $account->receiveAdminNotifications = $this->request->getPostParameter('receiveAdminNotifications', false);
            }
            $account->save();

            $this->flash('Account information saved.');
            $this->response->redirect('home');
        }

        $this->template->canReceiveNotifications = $this->hasPermission('receive system notifications');
        $this->template->account = $account;
        $this->template->canEditNotifications = $this->hasPermission('edit system notifications');
    }

}