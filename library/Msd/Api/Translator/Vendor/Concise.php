<?php

class Msd_Api_Translator_Vendor_Concise extends Msd_Api_Translator_Base
{
	protected static $instance = null;

	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	public function translate(array $params)
	{
		$result = array();
		if ($params['VendorGuid']) {
			$pt = &Msd_Api_Translator::getInstance()->t('position');
			
			$result['code'] = $params['VendorGuid'];
			$result['name'] = $params['VendorName'];
			$result['category'] = $params['CtgName'];
			$result['address'] = $params['Address'];
			$result['logo'] = Msd_Waimaibao_Vendor::imageUrl(array(
												'VendorGuid' => $params['VendorGuid']
												));
			$result['service_time'] = $params['ServiceTimeString'];
			$result['intro'] = $params['Remark'];
			$result['position'] = $pt->translate(array(
				'Longitude' => $params['Longitude'],
				'Latitude' => $params['Latitude']	
				));
			$result['express_price'] = $params['freight'];
		}
		
		return $result;
	}
}