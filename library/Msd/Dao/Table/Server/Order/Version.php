<?php

class Msd_Dao_Table_Server_Order_Version extends Msd_Dao_Table_Server_Order_Base
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'OrderVersion';
		$this->_primary = 'OrdVerGuid';
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
		$params['AddTime'] = $this->expr('GETDATE()');
		$params['AddUser'] || $params['AddUser'] = Msd_Config::appConfig()->db->status->name->default;
		$params['IsClosed'] = isset($params['IsClosed']) ? (int)$params['IsClosed'] : 0;
		$params['IsCanceled'] = isset($params['IsCanceled']) ? (int)$params['IsCanceled'] : 0;
		
		return parent::insert($params);
	}
	
	public function getBefore($OrderGuid, $LastVersionId)
	{
		$select = &$this->s();
		$select->from($this->sn('ov'));
		$select->where('OrderGuid=?', $OrderGuid);
		$select->where('VersionId<?', $LastVersionId);
		$select->order('VersionId ASC');
		$select->limit(1);
		
		return $this->one($select);
	}
	
	public function lastItemChange($OrderGuid)
	{
		$config = &Msd_Config::appConfig();
		
		$select = &$this->s();
		$select->from($this->sn('ov'), array(
			$this->expr('CONVERT(NVARCHAR, ov.AddTime, 120) AS LastChange'),
			'ov.*'	
			));
		$select->where('OrderGuid=?', $OrderGuid);
		$select->where('VersionId>?', 0);
		$select->where('ItemChanged=?', 1);
		$select->where('StatusId!=?', $config->order->status->delivered);
		$select->order('AddTime DESC');
		$select->limit(1);
		
		return $this->one($select);
	}
	
	public function firstVersion($OrderGuid)
	{
		$select = &$this->s();
		$select->from($this->sn('ov'));
		$select->where('OrderGuid=?', $OrderGuid);
		$select->where('VersionId=?', 0);
		$select->limit(1);
		
		return $this->one($select);
	}
	
	public function getOIV($OrderGuid, $OIVGuid)
	{
		$row = array();
		
		$select = &$this->s();
		$select->from($this->sn('ov'));
		$select->where('OrderGuid=?', $OrderGuid);
		$select->where('OIVGuid=?', $OIVGuid);
		$select->limit(1);
		
		$row = $this->one($select);
		
		return $row;
	}
	
	public function getOIVLast($OrderGuid, $OIVGuid)
	{
		$row = array();
		
		$select = &$this->s();
		$select->from($this->sn('ov'));
		$select->where('OrderGuid=?', $OrderGuid);
		$select->where('OIVGuid=?', $OIVGuid);
		$select->order('AddTime DESC');
		$select->limit(1);
		
		$row = $this->one($select);
		
		return $row;
	}
}
