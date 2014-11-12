<?php

class Files_DelController extends Msd_Controller_Files
{
	public function init()
	{
		parent::init();
	}
	
	public function indexAction()
	{
		$fid = trim($this->getRequest()->getParam('fid'));
		Msd_Uploader::Del($fid);
		
		$output = array();
		Msd_Output::prepareJson();
		echo json_encode($output);
		Msd_Output::doOutput();
	}
	
}