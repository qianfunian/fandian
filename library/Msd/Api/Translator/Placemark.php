<?php

class Msd_Api_Translator_Placemark extends Msd_Api_Translator_Base
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
		$pt = &Msd_Api_Translator::getInstance()->t('position');
		$result = array();
		
		if ($params['CoordGuid']) {
			$result['id'] = $params['CoordGuid'];
			$result['name'] = $params['CoordName'];
			$result['position'] = $pt->translate($params);
		}
		
		return $result;
	}
}