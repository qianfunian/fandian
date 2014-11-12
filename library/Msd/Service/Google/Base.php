<?php

abstract class Msd_Service_Google_Base extends Msd_Service_Base
{
	protected $service_url = '';
	protected $format = 'json';
	protected $params = array();
	
	public function setParams(array $params)
	{
		foreach ($params as $key=>$val) {
			$this->params[$key] = $val;
		}
	}
	
	public function setFormat($format='xml')
	{
		$this->format = $format=='xml' ? $format : 'json';
	}
	
	public function calUrl()
	{
		$this->service_url = str_replace('{FORMAT}', $this->format, $this->service_url);
	}
}