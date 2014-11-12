<?php

class Msd_Dao_Table_Web_Order_Hash extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'OrderHash';
		$this->_primary = 'Hash';
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function UpdateOrderStatus($params, $OrderGuid)
	{
		$where = $this->db->quoteInto('OrderGuid=?', $OrderGuid);
		
		return $this->update($params, $where);
	}
	
	public function Order2Hash($OrderGuid)
	{
		$select = &$this->s();
		$select->from($this->sn('oh'));
		$select->where('OrderGuid=?', $OrderGuid);
		
		return $this->one($select);
	}
	
	public function &getHashOrders($Hash)
	{
		$rows = array();
		
		$select = &$this->s();
		$select->from($this->sn('oh'));
		$select->where('Hash=?', $Hash);
		
		$rows = $this->all($select);
		
		return $rows;
	}
	
	public function OrderToHash($OrderGuid)
	{
		$select = $this->db->select();
		$select->from($this->_name);
		$select->where('OrderIds LIKE ?', '%'.$OrderGuid.'%');
		$select->limit(1);
		
		return $this->one($select);
	}
	
	public function insert(array $params)
	{
		$params['CreateTime'] = $this->expr('GETDATE()');
		
		return parent::insert($params);
	}	
	
	public function toHash($OrderId)
	{
		$select = &$this->s();
		$select->from($this->sn('o'));
		$select->where('OrderIds LIKE ?', '%'.$OrderId.'%');
		$select->limit(1);
		
		$row = $this->fetchRow($select);
		
		return $row;
	}
}