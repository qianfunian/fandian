<?php

class StepController extends Msd_Controller_Default
{
	protected $ServiceName = '普通';
	protected $cPrefix = '';
	
	public function indexAction()
    {
    	$config = &Msd_Config::appConfig();
    	
		$this->ServiceName = trim(urldecode($this->getRequest()->getParam('service', $config->db->n->service_name->normal)));
		switch ($this->ServiceName) {
			case $config->db->n->service_name->night:
				$this->cPrefix = 'y_';
				break;
			case $config->db->n->service_name->noon:				
				break;
			default:
				$this->ServiceName = $config->db->n->service_name->normal;
				break;
		}
		
		$this->view->ServiceName = $this->ServiceName;
		$this->view->cPrefix = $this->cPrefix;
		
    	$AutoRedirect = false;
    	$s = trim($this->getRequest()->getParam('s', ''));
    	
    	if (!$s && Msd_Validator::isGuid($_COOKIE['coord_guid'])) {
    		$this->_helper->getHelper('Redirector')->gotoUrl($this->scriptUrl.'vendor');
    		exit(0);
    	}
    	
    	$this->view->s = $s;
    	$this->view->previousAddresses = $this->previousAddresses();
    }
}