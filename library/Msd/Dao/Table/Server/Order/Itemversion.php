<?php

class Msd_Dao_Table_Server_Order_Itemversion extends Msd_Dao_Table_Server_Order_Base
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'OrderItemVersion';
		$this->_primary = 'OIVGuid';
		
		$this->nullKeys = array(
				'BoxQty'
				);
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function someVersion($OrderGuid, $VersionId)
	{
		$ovTable = &$this->t('order/version');
	
		$select = &$this->s();
		$select->from($this->sn('oiv'));
		$select->join($ovTable->sn('ov'), 'ov.OIVGuid=oiv.OIVGuid', array(
				'ov.OrdVerGuid', 'ov.StatusId', 'ov.OrdVerGuid'
		));
		$select->where('ov.OrderGuid=?', $OrderGuid);
		$select->where('oiv.VersionId=?', $VersionId);
		$select->limit(1);
	
		$row = $this->one($select);
	
		return $row;
	}
	
	public function lastVersion($OrderGuid)
	{
		$ovTable = &$this->t('order/version');
		
		$select = &$this->s();
		$select->from($this->sn('oiv'));
		$select->join($ovTable->sn('ov'), 'ov.OIVGuid=oiv.OIVGuid', array(
			'ov.OrdVerGuid', 'ov.StatusId', 'ov.OrdVerGuid'
			));
		$select->where('ov.OrderGuid=?', $OrderGuid);
		$select->order('oiv.VersionId DESC');
		$select->order('ov.AddTime DESC');
		$select->limit(1);

		$row = $this->one($select);
		
		return $row;
	}
}