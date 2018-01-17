<?php

/**
 * Error handler for Forbidden/Access Denied (403) errors.
 * 
 * @author      Daniel A. Koepke (dkoepke@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Ccheckin_Master_PermissionErrorHandler extends Ccheckin_Master_ErrorHandler
{
    public static function getErrorClassList () { return array('Bss_AuthZ_ExPermissionDenied'); }
    
    protected function getStatusCode () { return 403; }
    protected function getStatusMessage () { return "Access Denied"; }
    protected function getPageTitle () { return 'Access Denied'; }
    protected function getTemplateFile () { return 'error-403.html.tpl'; }
    
    protected function handleError ($error)
    {
        parent::handleError($error);
        
        $request = $error->getRequest();
        
        if (($referrer = $request->getReferrer()))
        {
            if (strpos($referrer, $request->getApplication()->siteUrl) === 0)
            {
                $this->template->likelyCause = 'internal';
            }
            else
            {
                $this->template->likelyCause = 'external';
            }
        }
        else
        {
            $this->template->likelyCause = 'user';
        }
    }
}
