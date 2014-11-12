<?php

class Msd_Dao_Table_Web_Searchlogs extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'SearchLogs';
		$this->_primary = 'AutoId';
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}

	public function insert(array $params)
	{
		$params['Timeline'] = $this->expr('GETDATE()');
		return parent::insert($params);
	}
}