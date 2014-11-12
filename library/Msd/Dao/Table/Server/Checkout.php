<?php

class Msd_Dao_Table_Server_Checkout extends Msd_Dao_Table_Server
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'D_Checkout';
		$this->_primary = 'ID';
		$this->_orderKey = 'ID';
		$this->_realPrimary = 'ID';
		$this->_primaryIsGuid = false;
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	public function &getByOrderId($OrderId, $CityGuid)
	{
		$row = array();
		
		$select = &$this->s();
		$select->from($this->sn('co'));
		$select->where('OrderId=?', $OrderGuid);
		$select->where('CityGuid=?', $CityGuid);
		$select->limit(1);
		
		$row = &$this->one($select);
		
		return $row;
	}
}