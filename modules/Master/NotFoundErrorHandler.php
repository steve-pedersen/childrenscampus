<?php

/**
 * Error handler for Resource/Page Not Found (404) errors.
 * 
 * @author      Daniel A. Koepke (dkoepke@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Ccheckin_Master_NotFoundErrorHandler extends Ccheckin_Master_ErrorHandler
{
    public static function getErrorClassList () { return array('Bss_Routing_ExNotFound'); }
    
    protected function getStatusCode () { return 404; }
    protected function getStatusMessage () { return "Not Found"; }
    protected function getPageTitle () { return 'Oops! - Page not found'; }
    protected function getTemplateFile () { return 'error-404.html.tpl'; }
    
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
