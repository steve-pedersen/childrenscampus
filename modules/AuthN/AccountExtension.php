<?php

/**
 * Adds properties/methods to accounts.
 * 
 * @author      Daniel A. Koepke (dkoepke@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Ccheckin_AuthN_AccountExtension extends Bss_AuthN_AccountExtension implements Bss_AuthN_IAccountSettingsExtension
{
    
    /**
     * Get all properties to add to an account.
     * 
     * @return array
     */
    public function getExtensionProperties ()
    {
        return array(
            'userAlias' => array('string', 'nativeName' => 'user_alias'),
            'ldap_user' => 'string',    // old data simply used underscores ***
            'isActive' => array('bool', 'nativeName' => 'is_active'),
            'receiveAdminNotifications' => array('bool', 'nativeName' => 'receive_admin_notifications'),
            'roles' => array('N:M', 'to' => 'Ccheckin_AuthN_Role', 'via' => 'ccheckin_authn_account_roles', 'fromPrefix' => 'account', 'toPrefix' => 'role'),          
        );
    }
 
    public function getSubjectProxies ($account)
    {
        return $account->roles->asArray();
    }
    
    /**
     * Get the methods to add to instances of the account class.
     * 
     * @return array
     */
    public function getExtensionMethods ()
    {
        return array('handleSettings');
    }
        
     /**
     * Get the weight of these settings, which determines their order in
     * the form. A heavier item always comes after a lighter item. Two
     * items of the same weight are presented in the order they are
     * loaded, which may vary.
     * 
     * @return int
     */
    public function getAccountSettingsWeight ()
    {
        return 10;
    }
    
    /**
     * Get the path to a template file for rendering as part of the
     * account settings form. May return null if this extension does not
     * render any settings (the extension's processAccountSettings method
     * will still be called).
     * 
     * @return string
     */
    public function getAccountSettingsTemplate ()
    {
        return $this->getModule()->getResource('_settings.html.tpl');
    }
    
    /**
     * Called when the settings form is submitted with the request that
     * submitted the form and the account instance for which the settings
     * are being modified.
     * 
     * @param Bss_Core_IRequest $request
     *    The request that has submitted the form.
     * @param Bss_AuthN_Account $account
     *    The account for which the settings have been submitted.
     * @param array& $errorMap
     *    A reference to an associative array mapping field names to arrays of
     *    error messages related to that field. This method will modify this
     *    error map with any errors that it causes to be set. If any errors are
     *    set in the error map, this method must return false.
     * @return bool
     *    True if the submission did not contain any errors for this settings
     *    extension. Else false. If any errors are set into the error map, this
     *    method must return false.
     */
    public function processAccountSettings (Bss_AuthZ_IParticipant $viewer, Bss_Core_IRequest $request, Bss_AuthN_Account $account, &$errorMap)
    {
        $authZ = $this->getApplication()->authorizationManager;
        
        if ($authZ->hasPermission($viewer, 'admin'))
		{
            $roles = $account->getSchema()->roles->getToSchema();
            $roleList = $roles->find($roles->isSystemRole->equals(true));
			
			// Build a map of role ids to roles for convenience.
			$roleMap = array();
			foreach ($roleList as $role)
			{
				$roleMap[$role->id] = $role;
			}
			
			// Remove all of the account's roles -- any selected roles will be added back (without any unnecessary writes to the DB).
			$account->roles->removeAll();
			
			// Find the selected roles.
			$selRoleSet = $request->getPostParameter('role');
			
			if (is_array($selRoleSet))
			{
				foreach ($selRoleSet as $roleId => $nonce)
				{
					if (isset($roleMap[$roleId]))
					{
						$account->roles->add($roleMap[$roleId]);
					}
				}
			}

            // save active status
            $account->isActive = $request->getPostParameter('status', false);
            $account->receiveAdminNotifications = $request->getPostParameter('receiveAdminNotifications', true);
            $account->save();
		}
        
        return true;
    }
    
    public function getAccountSettingsTemplateVariables (Bss_Routing_Handler $handler)
    {
        $roles = $handler->schema('Ccheckin_AuthN_Role');
		$roleList = $roles->find($roles->isSystemRole->equals(true), array('orderBy' => '+name'));
        $canEditNotifications = $handler->hasPermission('admin') || $handler->hasPermission('edit system notifications');
        $newAccount = $handler->getRouteVariable('id') === 'new';
        $adminPage = $handler->hasPermission('admin') && (strpos($handler->getRequest()->getFullRequestedUri(), 'admin') !== false);

        return array('roleList' => $roleList, 'canEditNotifications' => $canEditNotifications, 'newAccount' => $newAccount, 'adminPage' => $adminPage);
    }
    
    public function initializeRecord (Bss_ActiveRecord_Base $account)
    {
        $account->addEventHandler('before-delete', array($this, 'deleteAccount'));
    }
    
    public function deleteAccount (Bss_ActiveRecord_Base $account)
    {
        $account->roles->removeAll();
        $account->roles->save();
        $account->enrollments->removeAll();
        $account->enrollments->save();
    }
}
