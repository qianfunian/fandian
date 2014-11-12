<?php

class Msd_Service_Baidu_Base
{
	protected static $config = null;
	protected static $cityConfig = null;
	protected $key = '';
	protected $url = '';
	
	public function __construct()
	{
		self::$config = &Msd_Config::appConfig();
		self::$cityConfig = &Msd_Config::cityConfig();
		
		$keys = explode(',', self::$cityConfig->service->baidu->keys);
		$this->key = $keys[rand(0, count($keys)-1)];
	}
}