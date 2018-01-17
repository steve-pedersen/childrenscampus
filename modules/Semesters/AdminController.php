<?php

class Ccheckin_Semesters_AdminController extends Ccheckin_Master_Controller
{

    public static function getRouteMap ()
    {
        return array(
            'admin/semester/configure' => array('callback' => 'configure'),
        );
    }

    public function beforeCallback ($callback)
    {
        parent::beforeCallback($callback);
        $this->requirePermission('admin');
    }

    public function configure ()
    {
        $this->setPageTitle('Configure Semesters');
        $semesters = $this->schema('Ccheckin_Semesters_Semester');
        $errors = array();
        $message = '';
        
        if ($command = $this->request->getPostParameter('command'))
        {
            $action  = array_shift(array_keys($command));
            
            switch ($action)
            {
                case 'remove':
                    if ($sems = $this->request->getPostParameter('semesters'))
                    {
                        foreach ($sems as $sem)
                        {                            
                            if ($semester = $semesters->get($sem))
                            {
                                $semester->delete();
                            }
                        }

                        $message = 'The semesters have been deleted';
                    }
                    break;

                case 'add':
                    $semester = $semesters->createInstance();
                    
                    $startDate = $this->request->getPostParameter('startDate');
                    if ($startDate)
                    {
                        $semester->startDate = new Date(strtotime($startDate));
                    }
                    
                    $endDate = $this->request->getPostParameter('endDate');
                    if ($endDate)
                    {
                        $semester->endDate = new Date(strtotime($endDate));
                    }
                    
                    $semester->display = $this->request->getPostParameter('semester') . ' ' . $this->request->getPostParameter('year');
                    
                    $errors = $semester->validate();
                    
                    if (empty($errors))
                    {
                        $semester->save();
                        $message = 'Semester created';
                    }
                    break;
            }
        }
        
        // Get the remaining queued records
        $semesters = $semesters->getAll(array('orderBy' => 'startDate'));
        
        $this->template->semesters = $semesters;
        $this->template->terms = Semester::GetSemesters();
        $this->template->years = Semester::GetYears();
        $this->template->message = $message;
        $this->template->errors = $errors;
    }
}
