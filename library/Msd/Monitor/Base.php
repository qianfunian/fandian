<?php

abstract class Msd_Monitor_Base
{
	protected $params = array(
		'time_key' => 'monitor',
		'alert_offset' => 3,
		'alert_min_seconds' => 180	
		);
	protected $alertMethod = 'sms';
	protected $alerts = 0;
	
	abstract function isAvailable();
	
	public function setParams(array $params)
	{
		foreach ($params as $k=>$v) {
			$this->params[$k] = $v;
		}	
	}
	
	public function sendAlert()
	{
		$cacher = &Msd_Cache_Remote::getInstance();
		
		$alerts = (int)$cacher->get($this->params['time_key']);
		if ($alerts>=$this->params['alert_offset']) {
			$receivers = explode(',', Msd_Config::cityConfig()->monitor->alert_receivers);
			foreach ($receivers as $receiver) {
				Msd_Service_Sms::Send($receiver, $this->params['content']);
			}
			
			Msd_Log::getInstance()->alert($this->params['content']);
			
			$cacher->set($this->params['time_key']);
		}
	}
}