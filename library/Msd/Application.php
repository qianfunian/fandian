<?php

/**
 * 主应用程序类，继承自Zend，主体功能都在Zend里面
 * 
 * @author pang
 * 
 */
require_once 'Zend/Application.php';

class Msd_Application extends Zend_Application
{
	
	public function __construct($environment, $options=null)
	{
		parent::__construct($environment, $options);
	}
	
	public function __destruct()
	{
		Msd_Log::getInstance()->trace();
	}	
}