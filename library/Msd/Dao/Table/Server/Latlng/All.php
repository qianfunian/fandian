<?php

class Msd_Dao_Table_Server_Latlng_All extends Msd_Dao_Table_Server_Latlng_Base
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'LatlngAll';
		$this->_primary = 'Longitude';
		$this->_primaryIsGuid = false;
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}