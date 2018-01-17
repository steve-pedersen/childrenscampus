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
        	$purposes->object_id->equals($this->id),
        	$purposes->object_type->equals(get_class($this))
        );
        
        $result = $purposes->getAll($condition);
        
        return (!empty($result) ? $result[0] : null);
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
    	$authZ = $this->getAuthorizationManager();
        $authZ->revokePermission($account, 'purpose have', $this->purpose);	// TODO: Test
        $authZ->revokePermission($account, 'purpose observe', $this->purpose);	// TODO: Test
        $authZ->revokePermission($account, 'purpose participate', $this->purpose);	// TODO: Test
    }
	
	public function addUsers ($users, $observation = true)
	{
		$this->_mailErrors = array();
		$authZ = $this->getAuthorizationManager();
		$inviter = $this->requireLogin();
		
		foreach ($users as $user)
		{
			$user = trim($user);
			
			if ($user)
			{
				$proto = $this->getSchema('Bss_AuthN_Accounts');
				
				if (strpos($user, '@') === false)
				{
                    if (preg_match('@([0-9]{6,})@', $user, $matches))
                    {
                        $user = $matches[1];
                        // $accounts = $proto->query($proto->q->ldap_user->equalsTo($user), array('id' => true), 1);
                        $accounts = $proto->find($proto->ldap_user->equals($user), array('orderBy' => 'id'));

                        if (!empty($accounts))
                        {
                            $account = $accounts[0];
                            $account->isActive = true;
                            $account->save();
                        }
                        else
                        {
                            $account = $proto->createInstance();
                            $account->ldap_user = $user;
                            $account->isActive = true;
                            $account->save();
                        }
                        
                        $account->grantPermission('purpose have', $this->purpose);
                        
                        if ($observation)
                        {
                            $account->grantPermission('purpose observe', $this->purpose);
                        }
                        else
                        {
                            $account->grantPermission('purpose participate', $this->purpose);
                        }
                    }
				}
				elseif ($this->validEmailAddress($user))
				{
					$accounts = $proto->find($proto->emailAddress->equals($user), array('orderBy' => 'id'));
					$account = null;
					
					if (!empty($accounts))
					{
						$account = $accounts[0];
						
						if ($account->isActive)
						{
							$account->grantPermission('purpose have', $this->purpose);
                            
                            if ($observation)
                            {
                                $account->grantPermission('purpose observe', $this->purpose);
                            }
                            else
                            {
                                $account->grantPermission('purpose participate', $this->purpose);
                            }
                        
							continue;
						}
					}
					
					$invitation = $this->getSchema('Ccheckin_AuthN_AuthInvitation')->createInstance();
					$invitation->emailAddress = $user;
					$invitation->generateCode();
					$invitation->invitedById = $inviter->id;
					$invitation->invitedDate = new Date();
					$invitation->purposeId = $this->purpose->id;
					$invitation->save();
					
					// TODO: Update mailer *********************************************
					$mail = new DivaMailer();
					$mail->SMTPKeepAlive = true;
					
					if ($account)
					{
						$template = $this->reactivationTemplate;
						$mail->Subject = 'Children\'s Campus Check-In account has been re-activated!';
					}
					else
					{
						$mail->Subject = 'Activate your new Children\'s Campus Check-In account';
						$template = $this->invitationTemplate;
						$url = $diva->getUrl() . '/account/accept_invite/' . $invitation->id . '/' . $invitation->code;
						$template->assign('accountLink', '<a href="' . $url . '">' . $url . '</a>');
					}
					
					$template->assign('diva', $diva);
					$indMessage = $template->render();
					$mail->FromName = 'Children\'s Campus Check-In';
					$mail->Body = nl2br($indMessage);
					$mail->AltBody = strip_tags($indMessage);
					$mail->AddAddress($user);
					
					if (!$mail->Send())
					{
						$this->_mailErrors[] = $user;
					}
					
					$mail->SmtpClose();
					
				}
			}
		}
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
