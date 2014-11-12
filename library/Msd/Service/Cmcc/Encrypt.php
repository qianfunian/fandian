<?php

/**
 * 中国移动12580接口的加密解密
 * 
 * @author pang
 *
 */

class Msd_Service_Cmcc_Encrypt extends Msd_Service_Cmcc_Base
{
	protected static function EncUrl()
	{
		$config = &Msd_Config::cityConfig();
		$url = $config->service->v12580->encrypt->url;
		
		return $url;
	}
	
	protected static function DecUrl()
	{
		$config = &Msd_Config::cityConfig();
		$url = $config->service->v12580->deccrypt->url;
		
		return $url;
	}
	
	/**
	 * 加密
	 * 
	 * @param string $string
	 */
	public static function Enc($string)
	{
		$result = '';
		
		try {
			$http = new Msd_Http_Client();
			$http->setUrl(self::EncUrl());
			$http->setParameterPost(array(
				'string' => $string	
				));
			$response = $http->request('POST');
			$result = $response->getBody();
		} catch (Exception $e) {
			Msd_Log::getInstance()->v12580($e->getMessage()."\n".$e->getTraceAsString());
		}
		
		return $result;
	}
	
	/**
	 * 解密
	 * 
	 * @param string $string
	 */
	public static function Dec($string)
	{
		$result = '';
		
		try {
			$http = new Msd_Http_Client();
			$http->setUrl(self::DecUrl());
			$http->setParameterPost(array(
				'string' => $string	
				));
			$response = $http->request('POST');
			$result = $response->getBody();
		} catch (Exception $e) {
			Msd_Log::getInstance()->v12580($e->getMessage()."\n".$e->getTraceAsString());
		}
		
		return $result;
	}
}