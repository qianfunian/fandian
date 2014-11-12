<?php

class Msd_Api_Translator_City_Concise extends Msd_Api_Translator_Base
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
			$result['id'] = $params['zone'];
			$result['name'] = $params['name'];
		}
		
		return $result;
	}
}