<?php

/**
 * API 测试，提供给第三方做基础连接测试
 * @author pang
 * @email pang@fandian.com
 *
 */
class Api_DemoController extends Msd_Controller_Api
{
	public function init()
	{
		parent::init();
	
		Zend_Controller_Front::getInstance()->setParam('noViewRenderer', 0);
	}
	
	public function indexAction()
	{
		$this->message('API连接成功');
	}
}