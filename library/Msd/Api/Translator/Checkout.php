<?php

class Msd_Api_Translator_Checkout extends Msd_Api_Translator_Base
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
		$result = '';
		if ($params['OrderGuid']) {
			$data = array();
			$data[] = $params['OrderId'];
			$data[] = $params['VendorName']; 
			$data[] = $params['CompletionTime'];
			$data[] = $params['ItemAmount'];
			$data[] = $params['Freight'];
			$data[] = $params['BaoXiao'];
			$data[] = $params['FaPiao'];
			$data[] = $params['Comment'];
			
			$result = implode(',', $data);
		}
		
		return $result;
	}
}