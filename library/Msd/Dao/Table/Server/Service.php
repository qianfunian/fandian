<?php

class Msd_Dao_Table_Server_Service extends Msd_Dao_Table_Server
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'Service';
		$this->_primary = 'SrvGuid';
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	public function &getCityServices($CityId)
	{
		$select = &$this->s();
		$select->from($this->sn('s'));
		$select->where('CityId=?', $CityId);
		$rows = $this->all($select);

		return $rows;
	}
}
