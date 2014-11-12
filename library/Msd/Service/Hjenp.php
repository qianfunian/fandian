<?php

/**
 * 杭景GPRS打印机相关（利用PHP扩展）
 * 
 * @author pang
 *
 */

class Msd_Service_Hjenp extends Msd_Service_Base
{
	public static function Decryption($str, $key)
	{
		$config = &Msd_Config::cityConfig();
		$result = $config->service->hjenp->ext_enabled 
			? self::_Decryption($str, $key) 
			: self::_RemoteDecryption($str, $key);
		
		return $result;
	}
	
	public static function ShowPrintInfo($VendorName, $VendorGuid, $Footer, $Cell, $Pages=1)
	{
		$config = &Msd_Config::cityConfig();
		$result = $config->service->hjenp->ext_enabled 
			? self::_ShowPrintInfo($VendorName, $VendorGuid, $Footer, $Cell, $Pages) 
			: self::_RemoteShowPrintInfo($VendorName, $VendorGuid, $Footer, $Cell, $Pages);
		
		return $result;
	}
	
	protected static function _ShowPrintInfo($VendorName, $VendorGuid, $Footer, $Cell, $Pages=1)
	{
		return ShowPrintInfo($VendorName, $VendorGuid, $Footer, $Cell, $Pages, 0);
	}
	
	protected static function _RemoteShowPrintInfo($VendorName, $VendorGuid, $Footer, $Cell, $Pages=1)
	{
		$config = &Msd_Config::cityConfig();

		$http = new Msd_Http_Client();
		$http->setUri($config->service->hjenp->remote_service);
		$http->setAuth('pysche', 'leipang', Zend_Http_Client::AUTH_BASIC);
		$http->setParameterPost(array(
			'func' => 'showprintinfo',
			'VendorName' => $VendorName,
			'VendorGuid' => $VendorGuid,
			'Footer' => $Footer,
			'Cell' => $Cell,
			'Pages' => $Pages
			));
		$response = $http->request('POST');

		return trim($response->getBody());
	}
	
	protected static function _Decryption($str, $key)
	{
		return Decryption($str, $key);
	}
	
	protected static function _RemoteDecryption($str, $key)
	{
		$cacher = &Msd_Cache_Remote::getInstance();
		$ckey = 'hjenp_'.md5($str.$key);
		$result = $cacher->get($ckey);

		if (!$result) {
			$config = &Msd_Config::cityConfig();
			
			$http = new Msd_Http_Client();
			$http->setUri($config->service->hjenp->remote_service);
			$http->setAuth('pysche', 'leipang', Zend_Http_Client::AUTH_BASIC);
			$http->setParameterPost(array(
				'func' => 'decryption',
				'str' => $str,
				'key' => $key
				));
			try {
				$response = $http->request('POST');
				$result = $response->getBody();
				$cacher->set($ckey, $result);
			} catch (Exception $e) {
				
			}
		}

		return trim($result);
	}
}