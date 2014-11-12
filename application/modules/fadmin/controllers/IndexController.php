<?php

class Fadmin_IndexController extends Msd_Controller_Fadmin
{
	public function init()
	{
		parent::init();
		
		$this->auth('index');
	}
	
	public function indexAction()
    {
    	$this->view->headLink()->appendStylesheet($this->baseUrl.'js/jquery/treeview/treeview.css');
    	
    	$config = &Msd_Config::cityConfig();
    	$this->view->acl_groups = $config->acl_group->fadmin->toArray();
    }

}

