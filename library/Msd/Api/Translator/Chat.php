<?php

class Msd_Api_Translator_Chat extends Msd_Api_Translator_Base
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
		if ($params['ID']) {
			$result = $params['ID'].','.substr($params['SendTime'], 11,8).','.$params['Sender'].','.$params['Message'];
		}
		
		return $result;
	}
}