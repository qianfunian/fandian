<?php

require_once 'Tencent/OpenApi/V3.php';

class Msd_Service_Tencent_Openapi extends Msd_Service_Tencent_Base
{
	protected static $instance = null;
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			$config = Msd_Config::cityConfig()->service->tencent->openapi;
			
			self::$instance = new OpenApiV3(
					$config->app_id,
					$config->app_key
					);
			self::$instance->setServerName(
					$config->server_name
					);
		}
		
		return self::$instance;
	}
}