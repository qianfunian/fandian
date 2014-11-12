<?php

class Msd_Api_Translator_City extends Msd_Api_Translator_Base
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
		if ($params['zone'] && $params['name']) {
			$pt = Msd_Api_Translator::getInstance()->t('position');
			
			$result['id'] = $params['zone'];
			$result['name'] = $params['name'];
			$result['position'] = $pt->translate(array(
				'Longitude' => $params['Longitude'],
				'Latitude' => $params['Latitude']	
				));

			$result['areas'] = array();
			foreach ($params['biz_area'] as $region) {
				$biz_area = array();
				foreach ($region['vendors'] as $key=>$row) {
					$biz_area[] = array(
						'biz_area' => $key	
						);
				}
				
				$data = array(
					'area' => array(
						'name' => $region['RegionName'],
						'biz_areas' => $biz_area
						)	
					);
				$result['areas'][] = $data;
			}
			
			$result['vendor_category'] = array();
			foreach ($params['categories'] as $category) {
				$result['vendor_category'][] = array(
					'category' => $category['CtgName']	
					);
			}
			
			$result['services'] = array();
			foreach ($params['services'] as $service) {
				$result['services'][] = array(
					'service' => $service	
					);
			}
			
			$result['api_server'] = $params['api_host'] ? 'http://'.$params['api_host'] : 'http://open.fandian.com';
		}
		
		return $result;
	}
}