<?php

/**
 */
abstract class Ccheckin_Master_ErrorHandler extends Bss_Master_ErrorHandler
{
    protected function getTemplateClass () { return 'Ccheckin_Master_Template'; }
    
    protected function handleError ($error)
    {
        parent::handleError($error);
        
        $this->template->userContext = $this->getUserContext($error->getRequest(), $error->getResponse());
        $this->template->loginTemplate = $this->getApplication()->moduleManager->getModule('bss:core:authN')->getResource('login.html.tpl');
    }
}
