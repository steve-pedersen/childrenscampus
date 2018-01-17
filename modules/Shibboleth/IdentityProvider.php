<?php

/**
 */
class Ccheckin_Shibboleth_IdentityProvider extends At_Shibboleth_IdentityProvider
{
    private $allowedAffiliationList;
    
    protected function getDefaultAttributeHeaders ()
    {
        return array(
            'username' => 'UID',
            'organization' => 'calstateEduPersonOrg',
            'emailAddress' => 'mail',
            'displayName' => 'displayName',
            'firstName' => 'givenName',
            'lastName' => 'surname',
            'affiliation' => 'calstateEduPersonAffiliation',
        );
    }
    
    protected function getDefaultAllowedAffiliations ()
    {
        return array('Employee Faculty', 'Student', 'Employee Staff');
    }
    
    protected function configureProvider ($attributeMap)
    {
        parent::configureProvider($attributeMap);
        
        $this->allowedAffiliationsList = array_map(
            array($this, 'normalizeAffiliation'),
            (!empty($attributeMap['allowedAffiliations'])
                ? $attributeMap['allowedAffiliations']
                : $this->getDefaultAllowedAffiliations()
            )
        );
    }
    
    protected function initializeIdentityProperties (Bss_Core_IRequest $request, Bss_AuthN_Identity $identity)
    {
        parent::initializeIdentityProperties($request, $identity);
        
        $identity->setProperty('allowCreateAccount', $this->getAllowCreateAccount($identity));
    }
    
    protected function getAllowCreateAccount (Bss_AuthN_Identity $identity)
    {
        return true;
    }
    
    protected function normalizeAffiliation ($affiliation)
    {
        return strtolower(trim($affiliation));
    }
}
