<?php

class Msd_Dao_Table_Server_Category_Standard extends Msd_Dao_Table_Server
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'CategoryStandard';
		$this->_primary = 'CtgStdGuid';
		$this->_orderKey = 'CtgStdName';
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}