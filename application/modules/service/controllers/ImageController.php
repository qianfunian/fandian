<?php

class Service_ImageController extends Msd_Controller_Service
{
	public function init()
	{
		parent::init();
	}
		
	public function captchaAction()
	{
		$module = $this->getRequest()->getParam('sess', '');
		Msd_Image_Captcha::output($module);
	}
}