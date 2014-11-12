<?php

class Msd_Service_Pay extends Msd_Service_Base
{
	public static $allowedGateway = array(
		'0', '1'
		);
	
	public static function &factory($gateway)
	{
		$gateway = self::bankEnum2String($gateway);
		
		$class = 'Msd_Service_Pay_'.ucfirst(strtolower($gateway));
		$object = new $class();
		
		return $object;
	}
	
	/**
	 * 根据参数判断来自哪个银行的回调
	 * 
	 * @param array $params
	 */
	public static function getGatewayName($params)
	{
		$gateway = '';
		$config = &Msd_Config::cityConfig();
		
		if (isset($params['notifyMsg'])) {
			$gateway = $config->onlinepay->bankcomm->enum_value;
		} else if (isset($params['Signature'])) {
			$gateway = $config->onlinepay->bankcmb->enum_value;
		}
		
		return $gateway;
	}
	
	/**
	 * 转换银行Enum值到字符串
	 * 
	 * @param unknown_type $gateway
	 */
	public static function bankEnum2String($gateway)
	{
		$string = 'comm';
		
		switch ((int)$gateway) {
			case 1:
				$string = 'cmb';
				break;
			default:
				$string = 'comm';
				break;
		}
		
		return $string;
	}
}