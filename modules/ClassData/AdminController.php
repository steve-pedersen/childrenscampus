<?php

/**
 * Administrate accounts, roles, and access levels.
 * 
 * @author      Charles O'Sullivan (chsoney@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Ccheckin_ClassData_AdminController extends At_Admin_Controller
{
    public static function getRouteMap ()
    {
        return array(
            'admin/classdata' => array('callback' => 'index'),
        );
    }

    public function beforeCallback ($callback)
    {
        parent::beforeCallback($callback);
        $this->requirePermission('admin');
        $this->template->clearBreadcrumbs();
        $this->addBreadcrumb('home', 'Home');
        $this->addBreadcrumb('admin', 'Admin');
    }    
    
    public function index ()
    {
        $siteSettings = $this->getApplication()->siteSettings;
        
        if ($this->getPostCommand() == 'save' && $this->request->wasPostedByUser())
        {
            $siteSettings->setProperty('classdata-api-url',$this->request->getPostParameter('classdata-api-url'));
            $siteSettings->setProperty('classdata-api-key',$this->request->getPostParameter('classdata-api-key'));
            $siteSettings->setProperty('classdata-api-secret',$this->request->getPostParameter('classdata-api-secret'));
        }
        
        $this->template->classdataApiUrl = $siteSettings->getProperty('classdata-api-url');
        $this->template->classdataApiKey = $siteSettings->getProperty('classdata-api-key');
        $this->template->classdataApiSecret = $siteSettings->getProperty('classdata-api-secret');
    }
}