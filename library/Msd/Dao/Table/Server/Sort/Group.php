<?php

class Msd_Dao_Table_Server_Sort_Group extends Msd_Dao_Table_Server
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'SortGroup';
		$this->_primary = 'SortGroupGuid';
		$this->_orderKey = 'SortIndex';
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}