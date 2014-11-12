<?php

class Msd_Api_Translator_Addressbook extends Msd_Api_Translator_Base
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
		if ($params) {
			$ct = new DateTime($params['CreateTime']);
			
			$result['id'] = $params['ABGuid'];
			$result['name'] = $params['Title'];
			$result['contactor'] = $params['Contactor'];
			$result['phone'] = $params['Phone'];
			$result['address'] = $params['Address'];
			$result['is_default'] = $params['IsDefault'];
			$result['create_time'] = date('Y-m-d H:i:s', $ct->getTimestamp());
			
			if ($params['CoordGuid']) {
				$result['placemark'] = Msd_Api_Translator::getInstance()->t('placemark')->translate($params['pm']);
			} else {
				$result['placemark'] = '';
			}
		}
		
		return $result;
	}
}