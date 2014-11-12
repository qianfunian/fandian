<?php

class RobotsController extends Msd_Controller_Default
{
	protected function _init()
	{
		
	}
	
	public function indexAction()
	{
		$cConfig = &Msd_Config::cityConfig();
		
		$this->view->baseHref = $cConfig->meta->base_href;
		$this->view->baseDomain = $cConfig->meta->base_domain;
	}
}