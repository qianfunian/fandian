<?php

class Msd_Service_Pay_Comm extends Msd_Service_Pay_Base
{
	protected $instance = null;
	
	public function __construct()
	{
		$config = &Msd_Config::cityConfig()->onlinepay;
	
		$this->signUrl = $config->bankcomm->sign_url;
		$this->payUrl = $config->bankcomm->redirect_url;
		$this->queryUrl = $config->bankcomm->query_url;
	}
	
	public function query($OrderNo)
	{
		$TotalPayed = 0;
		
		$realOrderNo = substr($OrderNo, 1, strlen($OrderNo)-1);
		$url = $this->queryUrl.'?orders='.$realOrderNo;
		$http = new Msd_Http_Client($url);
		$response = $http->request();
		if ($response->isSuccessful()) {
			$body = trim(strip_tags($response->getBody()));
			Msd_Log::getInstance()->pay(Msd_Iconv::g2u($body));
			
			$lines = explode("\n", $body);
			if (count($lines)>=3) {
				$tags = explode('|', $lines[4]);
				count($tags)<5 && $tags = explode('|', $lines[2]);
				
				if (count($tags)>5) {
					$TotalPayed = strlen($tags[2])>7 ? (float)$tags[5] : (float)$tags[2];
				}
			}
		}
		
		return $TotalPayed;
	}
		
	public function parseCallback($callback)
	{
		$data = explode('|', $this->getSign($callback['notifyMsg']));
		
		$this->callbackResult['trans_result'] = (int)$data[9];
		$this->callbackResult['error'] = trim($data[13]);
		$this->callbackResult['trans_money'] = $data[2];
		$this->callbackResult['_bid'] = 'W'.$data[1];
		$this->callbackResult['ono'] = $data[6];
		$this->callbackResult['suffix'] = $data[8];
		$this->callbackResult['redirect2hash'] = false;
		
		return $this->callbackResult;
	}
	
}