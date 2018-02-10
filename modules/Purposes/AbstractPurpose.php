<?php

/**
 * A class to handle the connection between the purpose class and concrete purposes.
 *
 * @author      Charles O'Sullivan (chsoney@sfsu.edu)
 * @copyright   copyright (c) San Francisco State University
 */
abstract class Ccheckin_Purposes_AbstractPurpose extends Bss_ActiveRecord_BaseWithAuthorization
{
	protected $_mailErrors;
	
    /**
     * A brief description of the purpose.
     *
     * @return string
     */
    abstract public function getShortDescription ();
    
    public function getPurpose ()
    {
        $purposes = $this->getSchema('Ccheckin_Purposes_Purpose');
        $condition = $purposes->allTrue(
        	$purposes->objectId->equals($this->id),
        	$purposes->objectType->equals(get_class($this))
        );
        
        $result = $purposes->findOne($condition);

        return $result;
    }
	
	public function getInvitationTemplate ()
	{
		$template = new DivaTemplate;   
        $template->setDefaultResourceDirectory(glue_path(dirname(__FILE__), 'resources'));
		$template->setTemplateFile('invitation.tpl');
		
		return $template;
	}
	
	public function getReactivationTemplate ()
	{
		$template = new DivaTemplate;
        $template->setDefaultResourceDirectory(glue_path(dirname(__FILE__), 'resources'));
		$template->setTemplateFile('reactivation.tpl');
		
		return $template;
	}
    
    public function removeUser ($account)
    {
    	$authZ = $this->getApplication()->authorizationManager;
        $authZ->revokePermission($account, 'purpose have', $this->purpose);	// TODO: Test
        $authZ->revokePermission($account, 'purpose observe', $this->purpose);	// TODO: Test
        $authZ->revokePermission($account, 'purpose participate', $this->purpose);	// TODO: Test
    }
	
	// TODO: SEND EMAIL NOTIFICATION TO USERS THAT THEY HAVE BEEN ADDED TO A COURSE AND CAN THEN ACCESS IT
	public function addUsers ($users, $observation = true)
	{
		$authZ = $this->getApplication()->authorizationManager;

		foreach ($users as $user)
		{
            $user->isActive = true;
            $user->save();
           
            $authZ->grantPermission($user, 'purpose have', $this->purpose, false);
            
            if ($observation)
            {
                $authZ->grantPermission($user, 'purpose observe', $this->purpose, false);
            }
            else
            {
                $authZ->grantPermission($user, 'purpose participate', $this->purpose, false);
            }
		}
		$authZ->updateCache();
	}
	
	public function getMailErrors ()
	{
		return $this->_mailErrors;
	}
    
    public function getUserObservations ($account)
    {
        $obs = $this->getSchema('Ccheckin_Rooms_Observation');
        $cond = $obs->allTrue(
        	$obs->purposeId->equals($this->purpose->id),
        	$obs->accountId->equals($account->id)
        );
        return $obs->find($cond);
    }
    
    public function userCanParticipate ($account)
    {
        return $account->hasPermission('purpose participate', $this->purpose);
    }
    
    protected function afterInsert ()
    {
        parent::afterInsert();
        $purpose = $this->getSchema('Ccheckin_Purposes_Purpose')->createInstance();
        $purpose->object = $this;
        $purpose->save();
    }
    
    protected function beforeDelete ()
    {
        parent::beforeDelete();
        
        if ($this->purpose) 
        {
            $this->purpose->delete();
        }
    }
}
