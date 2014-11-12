<?php

class Msd_Api_Translator_Vmember extends Msd_Api_Translator_Base
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
			$result['vendorname'] = $params['VendorName'];
			$result['vendorguid'] = $params['VendorGuid'];
		}
		return $result;
	}
}