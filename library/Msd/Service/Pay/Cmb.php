<?php

class Msd_Service_Pay_Cmb extends Msd_Service_Pay_Base
{
	protected $instance = null;
	
	public function __construct()
	{
		$config = &Msd_Config::cityConfig()->onlinepay;
		
		$this->signUrl = $config->bankcmb->sign_url;
		$this->payUrl = $config->bankcmb->redirect_url;
		$this->queryUrl = $config->bankcmb->query_url;
	}
	
	public function query($OrderNo)
	{
		$TotalPayed = 0;
		
		$realOrderNo = substr($OrderNo, 1, strlen($OrderNo)-1);
		$url = $this->queryUrl.'?billno='.$realOrderNo.'&date=20'.substr($realOrderNo, 0, 6);
		
		$http = new Msd_Http_Client($url);
		$response = $http->request();
		if ($response->isSuccessful()) {
			$body = trim($response->getBody());
			Msd_Log::getInstance()->pay(Msd_Iconv::g2u($body));
			
			$lines = explode("\n", $body);
			if (count($lines)>=3 && trim($lines[0])=='ok') {
				$TotalPayed = (float)$lines[4];
			}
		}
		
		return $TotalPayed;
	}
	
	public function parseCallback($callback)
	{
		$qstr = isset($callback['QUERY_STRING']) ? $callback['QUERY_STRING'] : $_SERVER['QUERY_STRING'];
		
		if ($this->getSign($qstr)=='true') {
			$Succeed = trim(urldecode($callback['Succeed']));
			$CoNo = trim(urldecode($callback['CoNo']));
			$BillNo = trim(urldecode($callback['BillNo']));
			$Amount = trim(urldecode($callback['Amount']));
			$Date = trim(urldecode($callback['Date']));
			$MerchantPara = trim(urldecode($callback['MerchantPara']));
			$Msg = trim(urldecode($callback['Msg']));
			$Signature = trim(urldecode($callback['Signature']));
			
			$this->callbackResult['trans_result'] = $Succeed=='Y' ? 1 : 0;
			$this->callbackResult['error'] = $this->callbackResult['trans_result'] ? 0 : 1;
			$this->callbackResult['trans_money'] = $Amount;
			$this->callbackResult['_bid'] = Msd_Dao::table('order')->CmbBid($BillNo);
			$this->callbackResult['ono'] = $CoNo;
			$this->callbackResult['suffix'] = $BillNo;
			$this->callbackResult['redirect2hash'] = true;
		}

		return $this->callbackResult;
	}
}