<?php

class Msd_Session_Web extends Msd_Session_Base
{
	protected static $instance = null;
	
	private function __construct()
	{
		$this->namespace = 'web_'.APPLICATION_ENV;
		$this->init('memcache');
		
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