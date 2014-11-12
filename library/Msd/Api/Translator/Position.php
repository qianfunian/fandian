<?php

class Msd_Api_Translator_Position extends Msd_Api_Translator_Base
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
		$result = array(
			'longitude' => '',
			'latitude' => ''
			);
		if (Msd_Validator::isLngLat($params['Longitude'], $params['Latitude'])) {
			$result['longitude'] = sprintf('%01.6f', $params['Longitude']);
			$result['latitude'] = sprintf('%01.6f', $params['Latitude']);
		}
		
		return $result;
	}
}