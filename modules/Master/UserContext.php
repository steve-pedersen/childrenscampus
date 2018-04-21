<?php

/**
 * Puts a request within the context of a user.
 * 
 * Concretely, this class associates a request with application data about the
 * person who has made this request: the anonymous roles that should be applied
 * to this user and his account (if he's logged in). It also participates in 
 * the authorization system, providing a convenient way of checking if the user
 * has a permission via any anonymous roles, his account (if he's logged in),
 * etc.
 * 
 * 
 * @author      Daniel A. Koepke (dkoepke@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Ccheckin_Master_UserContext extends Bss_Master_UserContext
{
    private $request;
    private $response;
    private $account;
	private $anonRoleMap;
    
    /**
     * Construct a user context for the given request and response.
     * 
     * @param Bss_Core_IRequest $request
     * @param Bss_Core_IResponse $response
     */
    public function __construct (Bss_Core_IRequest $request, Bss_Core_IResponse $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->account = null;
		$this->anonRoleMap = null;
    }
    
    public function getAuthorizationId () { return null; } // Skip direct permission checks.
    
    /**
     * Get the request this user context was created for.
     * 
     * @return Bss_Core_IRequest
     */
    public function getRequest ()
    {
        return $this->request;
    }
    
    /**
     * Get the account the user is logged in as (if they are logged in).
     * 
     * @return Diva_AuthN_Account
     */
    public function getAccount ()
    {
        $session = $this->request->getSession();
        
        if ($this->account === null && isset($session->accountId))
        {
            $schemaManager = $this->request->getApplication()->schemaManager;
            $this->account = $schemaManager->getSchema('Bss_AuthN_Account')->get($session->accountId);
        }
        
        return $this->account;
    }
    
    /**
     * Set the account the user is logged in as.
     * 
     * @param Diva_AuthN_Account $account
     */
    public function setAccount ($account)
    {
        $this->account = $account;
        $session = $this->request->getSession();
        $session->accountId = $account->id;
    }
    
    /**
     * Logout the current account.
     * 
     * If the user has performed a become operation to login as the current
     * user, then this performs an 'unbecome' operation and returns the user
     * to their real account on the page where they performed the become. The
     * $returnTo argument is ignored in this case.
     * 
     * Otherwise, the user is logged out, their session is destroyed, and
     * they are redirected to the URL specified in $returnTo. If $returnTo is
     * null or not specified, no redirection occurs.
     * 
     * @param string $returnTo
     *    If the current login is NOT due to a becomeAccount() operation,
     *    redirect the user to this URL after logging them out. If this is
     *    null or not specified, no redirection occurs and this method
     *    returns.
     */
    public function logout ($returnTo = null)
    {
        $session = $this->request->getSession();
        
        if (isset($session->logoutReturnTo))
        {
            $returnTo = $session->logoutReturnTo;
            unset($session->logoutReturnTo);
        }
        
        $this->account = null;
        unset($session->accountId);
        
        if (!$this->unbecome())
        {
            // If this was a real logout, delete all session data. Otherwise, it
            // was an unbecome operation, so the user has just switched back to
            // their real account.
            $session->delete();
            $returnTo = '/';
        }
        
        if ($returnTo !== null)
        {
            $this->response->redirect($returnTo);
        }
    }
    /**
     * Login as the specified account.
     * 
     * This method sets the account's last and (if it's unset) first login
     * timestamps, and saves any changes that have been made to the account
     * and the active records it references.
     * 
     * @param Bss_AuthN_Account $account
     */
    public function login (Bss_AuthN_Account $account)
    {
        $account->lastLoginDate = new DateTime;
        
        if ($account->firstLoginDate === null)
        {
            $account->firstLoginDate = $account->lastLoginDate;
        }
        
        $account->save();
        $this->setAccount($account);
        $this->response->redirect('');
    }
    
    /**
     * Switch the current user into a new account without requiring or 
     * recording a login, and remember what account they were previously
     * logged in as.
     * 
     * This is used for the administrative 'become' operation, where an
     * administrative may 'become' a different account.
     * 
     * On unbecome() or logout(), the user is redirected back to the URL
     * specified as $returnTo.
     * 
     * @param Diva_AuthN_Acccount $account
     * @param string $returnTo
     * @return bool
     */
    public function becomeAccount ($account, $returnTo)
    {
        if ($account)
        {
            $session = $this->request->getSession();
            
            // Switch back to their real user account before becoming another user.
            $this->unbecome();
            
            // Remember their real account and where they used 'become' from.
            $session->wasAccountId = $session->accountId;
            $session->logoutReturnTo = $returnTo;
            
            // And set their account to the new user.
            $this->setAccount($account);
            return true;
        }
        
        return false;
    }
    
    /**
     * Switch back from an account we have 'become' to our original account.
     * 
     * @return bool
     *    Returns true if the account was switched back, or false if the
     *    current account was not set by a becomeAccount() operation.
     */
    public function unbecome ()
    {
        $session = $this->request->getSession();
        
        if (isset($session->quickList))
        {
            unset($session->quickList);
        }
        
        if (isset($session->wasAccountId))
        {
            $this->account = null; // Force reload of account on getAccount().
            $session->accountId = $session->wasAccountId;
            unset($session->wasAccountId);
            return true;
        }
        
        return false;
    }

	public function addAnonymousRole (Ccheckin_AuthN_Role $role)
	{
		$session = $this->request->getSession();
		
		if ($this->anonRoleMap === null)
		{
			$this->anonRoleMap = array();
		}
		
		$this->anonRoleMap[$role->id] = $role;
		
		if (isset($session->anonymousRoles))
		{
			$roleSet = $session->anonymousRoles;
		}
		else
		{
			$roleSet = array();
		}
		
		$roleSet[$role->id] = true;
		$session->anonymousRoles = $roleSet;
	}
	
	public function removeAnonymousRole (Ccheckin_AuthN_Role $role)
	{
		if (isset($this->anonRoleMap[$role->id]))
		{
			unset($this->anonRoleMap[$role->id]);
			
			$roleSet = $session->anonymousRoles;
			unset($roleSet[$role->id]);
			$session->anonymousRoles = $roleSet;
		}
	}
    
    public function getAnonymousRoles ()
    {
		if ($this->anonRoleMap === null)
		{
	        $schemaManager = $this->request->getApplication()->schemaManager;
	        $session = $this->request->getSession();
			$this->anonRoleMap = array();

			if (!isset($session->anonymousRoles))
			{
				$ipRoles = $schemaManager->getSchema('Ccheckin_AuthN_IpRoleAssignment');
				$ipRoleList = $ipRoles->find($ipRoles->ipAddress->containsIpAddress($this->request->getRemoteAddress()));
			
				$roleSet = array();
			
				foreach ($ipRoleList as $ipRole)
				{
					$roleSet[$ipRole->role_id] = true;
					$this->anonRoleMap[$ipRole->role_id] = $ipRole->role;
				}
			
				$session->anonymousRoles = $roleSet;
			}
			else
			{
				$roleSet = $session->anonymousRoles;
				$roles = $schemaManager->getSchema('Ccheckin_AuthN_Role');
				
				foreach ($roles->findById(array_keys($roleSet)) as $role)
				{
					$this->anonRoleMap[$role->id] = $role;
				}
			}
		}
		
		return $this->anonRoleMap;
    }
    
    public function getSubjectProxies ()
    {
        $subjectProxies = array();
        
        if ($this->getAccount())
        {
            $subjectProxies[] = $this->getAccount();
        }
        
        foreach ($this->getAnonymousRoles() as $role)
        {
            $subjectProxies[] = $role;
        }
        
        return $subjectProxies;
    }
    
    public function getObjectProxies ()
    {
        return ((array) $this->getAccount());
    }
    
    public function getAuthorizationManager ()
    {
        return $this->request->getApplication()->authorizationManager;
    }
    
    public function hasSessionPermission ($task, $object = Bss_AuthZ_Manager::SYSTEM_ENTITY)
    {
        $this->getAuthorizationManager()->hasPermission(null, $task, $object, array('source' => 'session'));
    }
    
    public function grantSessionPermission ($task, $object = Bss_AuthZ_Manager::SYSTEM_ENTITY)
    {
        $this->getAuthorizationManager()->grantPermission(null, $task, $object, false, 'session');
    }
    
    public function revokeSessionPermission ($task, $subject = Bss_AuthZ_Manager::SYSTEM_ENTITY)
    {
        $this->getAuthorizationManager()->revokePermission(null, $task, $object, false, 'session');
    }
    
    public function __get ($name)
    {
        $getter = "get{$name}";
        
        return (method_exists($this, $getter)
            ? $this->{$getter}()
            : null
        );
    }
}
