<?php

/**
 * Error handler for errors that would otherwise not be handled.
 * 
 * @author      Daniel A. Koepke (dkoepke@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Ccheckin_Master_UnhandledErrorHandler extends Ccheckin_Master_ErrorHandler
{
    public static function getErrorClassList () { return array(Bss_Routing_ErrorManager::UNHANDLED_ERROR_CLASS); }
    
    protected function getStatusCode () { return 500; }
    protected function getStatusMessage () { return "Internal Error"; }
    protected function getPageTitle () { return 'Oops! We\'ve screwed up.'; }
    protected function getTemplateFile () { return 'error-500.html.tpl'; }
    
    protected function handleError ($error)
    {
        parent::handleError($error);
        
        $request = $error->getRequest();
        $referrer = $request->getReferrer();
        
        if ($error instanceof Bss_Routing_ExWrappedException)
        {
            $error = $error->getExtraInfo();
        }
        
        if ($this->getApplication()->runMode == Bss_Core_Application::RUN_MODE_DEBUG)
        {
            // In debug mode, we let exceptions happen.
            echo "<pre>";
            throw $error;
        }
        
        
        $app = $this->getApplication();
        $app->log('critical',
            "On page " . $request->getRequestedUri() . " CCheckin caught an" .
            " unhandled exception of type " . get_class($error) .
            " with message: " . $error->getMessage()
        );
    }
}
