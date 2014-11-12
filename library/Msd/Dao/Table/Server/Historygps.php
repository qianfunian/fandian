<?php

class Msd_Dao_Table_Server_Historygps extends Msd_Dao_Table_Server
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'D_HistoryGps';
		$this->_primary = 'AutoId';
		$this->_orderKey = 'AutoId';
		$this->_realPrimary = 'AutoId';
		$this->_primaryIsGuid = false;
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
		$params['HeartBeatTime'] = $this->expr('GETDATE()');
		
		return parent::insert($params);
	}
}