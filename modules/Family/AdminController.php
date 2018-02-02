<?php

class Ccheckin_Family_AdminController extends Ccheckin_Master_Controller
{
	public static function getRouteMap ()
	{
		return array(
				'admin/family' => array('callback' => 'index'),
				'admin/family/edit/:id' => array('callback' => 'edit', 'id' => '([0-9]+|new)'),
				// 'admin/family/:id/edit' => array('callback' => 'edit', ':id' => '[0-9]+|new')
		);
	}

    public function beforeCallback ($callback)
    {
        parent::beforeCallback($callback);
        $this->requirePermission('admin');
    }

    public function index ()
    {
    	$this->setPageTitle('Manage Family Purposes');
    	$familyPurposes = $this->schema('Ccheckin_Family_Purpose');
        $message = '';
        
        if ($this->request->wasPostedByUser())
        {
            if ($command = $this->getPostCommand())
            {   
                switch ($command)
                {
                    case 'remove':
                        $purposes = $this->request->getPostParameter('purposes');
                        
                        if (!empty($purposes))
                        {
                            foreach ($purposes as $purposeId)
                            {                               
                                if ($purpose = $familyPurposes->get($purposeId))
                                {
                                    $purpose->delete();
                                }
                            }
                            
                            $message = 'The selected purposes have been deleted.';
                        }
                        break;
                }
            }
        }
        
        $this->template->purposes = $familyPurposes;
        $this->template->message = $message;
    }
    
    // DEBUG: does this $id param get picked up this way? does it need to be a route var?
    public function edit ()
    {
        $id = $this->getRouteVariable('id');
        if (!preg_match('/^([0-9]+|new)$/', $id))
        {
            $this->notFound();
            exit;
        }
        
        $purpose = $this->schema('Ccheckin_Family_Purpose')->get($id);
        $errors = array();
		$new = false;
        
        if (is_numeric($id))
		{
			if (!$purpose->inDataSource)
			{
				$this->notFound();
				exit;
			}
			
			$this->setPageTitle('Edit Family purpose: ' . $purpose->name);
		}
		else
		{
			$new = true;
			$this->setPageTitle('Create Family Purpose');
		}
        
        if ($this->request->wasPostedByUser())
        {
            if ($command = $this->getPostCommand())
            {   
                switch ($command)
                {
                    case 'save':
                    	$purpose->absorbData($this->request->getPostParameter('purpose'));
                        $errors = $purpose->validate();
                        
                        if (empty($errors))
                        {
                            $purpose->save();
                            $this->response->redirect('admin/family');
                        }
                        
                        break;
                }
            }
        }
        
        $this->template->purpose = $purpose;
        $this->template->new = $new;
        $this->template->errors = $errors;
    }

}