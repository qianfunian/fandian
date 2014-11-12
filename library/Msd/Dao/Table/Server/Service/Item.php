<?php

class Msd_Dao_Table_Server_Service_Item extends Msd_Dao_Table_Server
{
	protected static $instance = null;

	public function __construct()
	{
		parent::__construct();

		$this->_name = $this->prefix.'ServiceItem';
		$this->_primary = 'SrvItemGuid';
	}

	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	public function &getCityServices($CityId,$SrvGrp)
	{
		$select = &$this->s();
		$select->from($this->sn('s'));
		$select->where('CityId=?', $CityId);
		$select->where('SrvGrpGuid=?', $SrvGrp);
		$rows = $this->all($select);
	
		return $rows;
	}
}