<?php

class Msd_Monitor_Httpd_Sz extends Msd_Monitor_Httpd
{
	public function __construct()
	{
		$cConfig = &Msd_Config::cityConfig();
		
		$this->params = $cConfig->monitor->httpd_sz->params->toArray();	
		$this->alertMethod = $this->params['alert_method'];
	}
}