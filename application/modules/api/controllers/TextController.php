<?php

/**
 * 获取注册条款
 * @author pang
 * @email pang@fandian.com
 *
 */
class Api_TextController extends Msd_Controller_Api
{
	public function init()
	{
		parent::init();
		
		Zend_Controller_Front::getInstance()->setParam('noViewRenderer', 0);
	}
	
	/**
	 * 获取注册条款
	 * 
	 */
	public function termAction()
	{
		$this->xmlRoot = 'message';
		$terms = Msd_Config::appConfig()->api->term->toArray();
		$this->message(implode("\n", $terms));
	}
	
}