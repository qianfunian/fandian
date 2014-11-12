<?php

class Msd_Hook_Wcf extends Msd_Hook_Base
{
	protected static $instance = null;
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function NewOrderCreated(array $params=array())
	{
		$config = &Msd_Config::cityConfig();

		if ($config->wcf->enabled && $params['OrderId']) {
			$cacher = &Msd_Cache_Remote::getInstance();
			$key = 'wcf_orders';

			$os = $cacher->get($key);
			
			$os || $os = array();
			$os[] = $params['OrderId'];
			$cacher->set($key, $os);
		}
	}
}