<?php

/**
 * 项目启动器
 * 
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
	public function __construct($application) {
		parent::__construct ( $application );
		
		$config = &Msd_Config::getInstance ();
		$router = new Zend_Controller_Router_Rewrite ();
		$router->addConfig ( $config, 'routes' );
		
		$frontController = Zend_Controller_Front::getInstance ();
		$frontController->setRouter ( $router );
	}
}

