<?php

class Msd_Dao_Table_Web_Order_Printed extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'OrderPrinted';
		$this->_primary = 'OrderGuid';
		$this->_primaryIsGuid = true;
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function lastPrintedVersion($OrderGuid)
	{
		$oTable = &$this->t('order');
		$oivTable = &$this->t('order/itemversion');
		
		$select = &$this->s();
		$select->from($this->sn('op'));
		$select->join($oivTable->sn('oiv'), 'oiv.OIVGuid=op.OIVGuid', array(
			'oiv.VersionId'	
			));
		$select->where('op.OrderGuid=?', $OrderGuid);
		$select->where('op.PrintTime IS NOT NULL');
		$select->where('op.SendTime IS NOT NULL');
		$select->order('op.AddTime DESC');
		$select->limit(1);
		
		$row = $this->one($select);
		
		return (int)$row['VersionId'];
	}
	
	public function getOIV($OrderGuid, $OIVGuid)
	{
		$select = &$this->s();
		$select->from($this->sn('op'));
		$select->where('OrderGuid=?', $OrderGuid);
		$select->where('OIVGuid=?', $OIVGuid);
		$select->limit(1);
		
		$row = $this->one($select);
		
		return $row;
	}
	
	public function updateSendTime($OrderGuid, $OIVGuid)
	{
		$params = array(
			'SendTime' => $this->expr('GETDATE()')	
			);
		
		$where = $this->db->quoteInto('OrderGuid=?', $OrderGuid);
		$where .= ' AND '.$this->db->quoteInto('OIVGuid=?', $OIVGuid);
		
		return $this->update($params, $where);
	}
	
	public function isFirstVersion($OrderGuid)
	{
		$select = &$this->s();
		$select->from($this->sn('op'), array(
			$this->expr('COUNT(*) AS total')	
			));
		$select->where('OrderGuid=?', $OrderGuid);
		$row = $this->one($select);
		
		return $row['total']==1 ? true : false;
	}
	
	public function updateUnuse($OrderGuid, $AddTime)
	{
		$params = array(
			'SendTime' => $this->expr('GETDATE()')	
			);
		$where = $this->db->quoteInto('AddTime<=?', $AddTime);
		$where .= ' AND SendTime IS NULL';
		$where .= ' AND '.$this->db->quoteInto('OrderGuid=?', $OrderGuid);
		
		return $this->update($params, $where);
	}
	
	public function setPrintedByAddTime($AddTime)
	{
		$params = array(
			'PrintTime' => $this->expr('GETDATE()')	
			);
		
		$where = $this->db->quoteInto('AddTime<=?', $AddTime);
		
		return $this->update($params, $where);
	}
	
	public function setPrintedByOIV($OIVGuid)
	{
		$params = array(
			'PrintTime' => $this->expr('GETDATE()')	
			);
		
		$where = $this->db->quoteInto('OIVGuid=?', $OIVGuid);
		
		return $this->update($params, $where);
	}
	
	public function popupUnprinted($OrderGuid)
	{
		$oivTable = &$this->t('order/itemversion');
		
		$select = &$this->s();
		$select->from($this->sn('op'));
		$select->join($oivTable->sn('oiv'), 'oiv.OIVGuid=op.OIVGuid', array(
			'oiv.VersionId'	
			));
		$select->where('op.OrderGuid=?', $OrderGuid);
		$select->order('op.AddTime ASC');
		$select->where('op.PrintTime IS NULL');
		$select->limit(1);

		$row = $this->one($select);

		return $row;
	}
}