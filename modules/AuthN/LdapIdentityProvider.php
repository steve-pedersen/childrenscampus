<?php

/**
 */
class Ccheckin_AuthN_LdapIdentityProvider extends At_LDAP_IdentityProvider
{
    private $allowedAffiliationsList;
    
    
    /**
     * The list of affiliations that are allowed to create accounts through
     * LDAP.
     * 
     * @return array
     */
    protected function getDefaultAllowedAffiliations ()
    {
        return array();
        // return array('Employee Faculty', 'Employee Staff');
    }
    
    protected function getDefaultAttributes ()
    {
        return array(
            'username' => 'CN',
            'emailAddress' => 'mail',
            'firstName' => 'givenName',
            'lastName' => 'sn',
            'affiliation' => 'calstateEduPersonAffiliation',
        );
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
    
    protected function initializeIdentityProperties ($rs, Bss_AuthN_Identity $identity)
    {
        parent::initializeIdentityProperties($rs, $identity);
        
        if ($rs)
        {
            $affiliations = array_map(array($this, 'normalizeAffiliation'), $this->attr($rs, 'affiliation', array(), true));
            $identity->setProperty('affiliation', implode(';', $affiliations));
            
            foreach ($affiliations as $affiliation)
            {
                if (in_array($affiliation, $this->allowedAffiliationsList))
                {
                    $identity->setProperty('allowCreateAccount', true);
                    break;
                }
            }
        }
    }
    
    /**
     * Normalize an affiliation name.
     * 
     * @param string $in
     * @return string
     */
    protected function normalizeAffiliation ($in)
    {
        return strtolower(trim($in));
    }
}
