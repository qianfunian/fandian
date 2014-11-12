<?php

class Msd_Dao_Table_Server_Item_Property extends Msd_Dao_Table_Server
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'ItemProperty';
		$this->_primary = 'PropGuid';
		$this->_orderKey = 'PropName';
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}