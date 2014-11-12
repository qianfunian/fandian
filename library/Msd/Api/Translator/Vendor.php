<?php

class Msd_Api_Translator_Vendor extends Msd_Api_Translator_Base
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
		if ($params['basic']['VendorGuid']) {
			$config = &Msd_Config::appConfig();
			
			$VendorGuid = $params['basic']['VendorGuid'];
			$vTable = &Msd_Dao::table('vendor');
			$basic = &$params['basic'];
			$address = &$params['address'];
			$pt = &Msd_Api_Translator::getInstance()->t('position');
			$pct = &Msd_Api_Translator::getInstance()->t('product_category');
			
			$result['code'] = $VendorGuid;
			$result['name'] = $basic['VendorName'];
			$result['category'] = $params['groups'][Msd_Config::appConfig()->db->n->ctg_std_name->vendor];
			$result['address'] = $address['Address'];
			$result['service_time'] = $vTable->getServiceTimeString($VendorGuid);
			$result['logo'] = Msd_Waimaibao_Vendor::imageBigUrl(array(
								'VendorGuid' => $VendorGuid
								));
			$result['intro'] = $basic['Remark'];
			$result['position'] = $pt->translate(array(
				'Longitude' => $address['Longitude'],
				'Latitude' => $address['Latitude']	
				));
			$result['products'] = array();
			foreach ($params['item_category'] as $category) {
				$result['products'][] = array(
					'product_category' => $pct->translate($category)	
					);
			}
		}

		return $result;
	}
}