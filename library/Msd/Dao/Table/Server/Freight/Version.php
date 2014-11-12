<?php

class Msd_Dao_Table_Server_Freight_Version extends Msd_Dao_Table_Server
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'FreightVersion';
		$this->_primary = 'FrtVerGuid';
		
		$this->nullKeys = array(
				'Distance', 'FreightOrigin', 'Remark'
				);
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
		$params['AddUser'] || $params['AddUser'] = Msd_Config::appConfig()->db->status->name->default;
		$params['AddTime'] = $this->expr('GETDATE()');	
			
		if ($params['ReqTimeStart']) {
			//	预订的
			$params['ReqDateTime'] = $this->expr("CAST('".$params['ReqDate']." ".$params['ReqTimeStart']."' AS DATETIME)");
		} else {
			//	尽快
			$params['ReqDateTime'] = $this->expr('GETDATE()');
			unset($params['ReqTimeStart']);
		}
		
		if (isset($params['ReqDate'])) {
			unset($params['ReqDate']);
		}
		
		return parent::insert($params);
	}
	
	public function last($OrderGuid)
	{
		$select = &$this->s();
		$select->from($this->sn('fv'));
		$select->where('OrderGuid=?', $OrderGuid);
		$select->order('AddTime DESC');
		$select->limit(1);
		
		$row = $this->one($select);
		
		return $row;
	}
}