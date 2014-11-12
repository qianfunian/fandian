<?php

class Msd_Dao_Table_Server_Category_Group extends Msd_Dao_Table_Server
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'CategoryGroup';
		$this->_primary = 'CtgGroupGuid';
		$this->_orderKey = 'CtgGroupName';
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}