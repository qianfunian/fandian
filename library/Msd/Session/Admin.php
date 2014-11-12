<?php

class Msd_Session_Admin extends Msd_Session_Base
{
	protected static $instance = null;
	
	private function __construct()
	{
		$this->namespace = 'admin';
		$this->init();
		
		if ($this->get('uid')) {
			$this->acl->addRole(new Msd_Acl_Role('member'));
		}
	}
	
	public static function &getInstance()
	{
		if (self::$instance == null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}