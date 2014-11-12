<?php

class Msd_Service_Wcf_Base extends Msd_Service_Base
{
	protected $url = '';
	protected $soapClient = null;
	protected $soapOptions = array(
			'soap_version' => SOAP_1_1,
			'cache_wsdl' => WSDL_CACHE_MEMORY,
			'keep_alive' => true,
     'exceptions' => true,
     'encoding' => 'utf8'
			);
	
	protected function __construct()
	{
		$this->soapOptions['connection_timeout'] = (int)Msd_Config::cityConfig()->wcf->timeout;
		$this->soapClient = new SoapClient($this->url, $this->soapOptions);
	}
	
	public function __call($method, $params)
	{
		
	}
}