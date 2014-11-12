<?php

/**
 * 网上支付银行回调抽象类
 * 
 * @author pang
 *
 */

abstract class Msd_Service_Pay_Base
{
	protected $callbackResult = array();
	protected $signUrl = '';
	protected $payUrl = '';
	protected $queryUrl = '';
	
	abstract public function parseCallback($callback);
	abstract public function query($OrderNo);
	
	public function getSign($sign)
	{
		$parsed = '';

		$http = new Msd_Http_Client($this->signUrl);
		$http->setParameterPost(array(
			'notifyMsg' => $sign	
			));
		
		try {
			$response = $http->request('POST');
			if ($response->isSuccessful()) {
				$parsed = trim($response->getBody());
			}
		} catch (Exception $e) {
			Msd_Log::getInstance()->exception($e->getMessage()."\n".$e->getTraceAsString());
		}

		return $parsed;
	}
}