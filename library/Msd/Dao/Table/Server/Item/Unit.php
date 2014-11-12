<?php

class Msd_Dao_Table_Server_Item_Unit extends Msd_Dao_Table_Server
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'ItemUnit';
		$this->_primary = 'UnitGuid';
		$this->_orderKey = 'UnitName';
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}