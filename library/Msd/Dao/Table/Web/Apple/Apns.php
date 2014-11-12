<?php

class Msd_Dao_Table_Web_Apple_Apns extends Msd_Dao_Table_Web
{
	protected static $instance = null;

	public function __construct()
	{
		parent::__construct();

		$this->_name = $this->prefix.'AppleApns';
		$this->_primary = 'CustGuid';
	}

	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}