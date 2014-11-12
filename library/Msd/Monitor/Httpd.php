<?php

class Msd_Monitor_Httpd extends Msd_Monitor_Base
{
	public function __construct()
	{
		$cConfig = &Msd_Config::cityConfig();
		
		$this->params = $cConfig->monitor->httpd->params->toArray();	
		$this->alertMethod = $this->params['alert_method'];
	}
	
	public function isAvailable()
	{
		$result = false;
		$uri = 'http://'.$this->params['ip'].$this->params['url'];
		$client = new Msd_Http_Client($uri);
		
		$params = array(
			'timeout' => $this->params['timeout'] ? (int)$this->params['timeout'] : 5,
			'keepalive' => true	
			);
		$client->setConfig($params);
		
		if ($this->params['host']) {
			$client->setHeaders(array(
				'Host' => $this->params['host']
				));
		}
		
		try {
			$output = $client->request()->getBody();
			if (trim($output)=='OK') {
				$result = true;
			}
		} catch (Exception $e) {
			Msd_Log::getInstance()->monitor($e->getMessage()."\n".$e->getTraceAsString());
		}
		
		if (!$result) {
			$cacher = &Msd_Cache_Remote::getInstance();
			$alerts = (int)$cacher->get($this->params['time_key']);
			$cacher->set($this->params['time_key'], $alerts+1, $this->params['alert_min_seconds']);
		}
		
		return $result;
	}
}