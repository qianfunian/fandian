<?php

class Msd_Dao_Table_Server_Customer_Phone extends Msd_Dao_Table_Server_Customer_Base
{
	protected static $instance = null;
	public static $cellPhoneType = '1';
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'CustomerPhone';
		$this->_primary = 'PhoneGuid';
		$this->compatInsert = true;
		
		$this->nullKeys = array('Remark');
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function &cacheGetOtherCell($CustGuid, $Cell)
	{
		$cacher = &Msd_Cache_Remote::getInstance();
		$cKey = md5('cgoc_'.$CustGuid.'_'.$Cell);
		$gotted = $cacher->get($cKey);
		
		if (!$gotted) {
			$select = &$this->s();
			$select->from($this->sn('cp'));
			$select->where('PhoneType=?', '1');
			$select->where('CustGuid=?', $CustGuid);
			$select->where('PhoneNumber!=?', $Cell);
			$select->limit(1);
			
			$row = $this->one($select);
			$gotted = $row['PhoneNumber'] ? $row['PhoneNumber'] : 'empty';
		}
		
		return $gotted;
	}
	
	public function &getCellRow($CustGuid)
	{
		$where = $this->db->quoteInto('CustGuid=?', $CustGuid)
			.$this->db->quoteInto('AND PhoneType=?', self::$cellPhoneType);
		$row = $this->fetchRow($where);
		
		return $row;
	}
	
	public function &CellLogin($cell)
	{
		$uTable = &$this->t('user');
		
		$select = &$this->s();
		$select->from($this->sn('cp'), 'cp.CustGuid');
		$select->join($uTable->sn('u'), 'u.CustGuid=cp.CustGuid', '');
		
		$where = $this->db->quoteInto('PhoneType=?', self::$cellPhoneType);
		$where .= ' AND ('.$this->db->quoteInto('PhoneNumber=?', $cell).' OR '.$this->db->quoteInto('PhoneNumber=?', '0'.$cell).')';
		
		$select->where($where);
		$select->limit(1);

		return $this->one($select);
	}
	
	public function &OrderCellCheck($cell)
	{
		$select = &$this->s();
		$select->from($this->sn('cp'), 'cp.*');
		
		$where = $this->db->quoteInto('PhoneType=?', self::$cellPhoneType);
		$where .= ' AND ('.$this->db->quoteInto('PhoneNumber=?', $cell).' OR '.$this->db->quoteInto('PhoneNumber=?', '0'.$cell).')';
		
		$select->where($where);
		$select->limit(1);

		return $this->one($select);
	}
	
	public function &getGuidByCell($cell, $web=false)
	{
		$select = $this->db->select();
		$select->from($this->name());
		
		$where = $this->db->quoteInto('PhoneType=?', self::$cellPhoneType);
		$where .= ' AND ('.$this->db->quoteInto('PhoneNumber=?', $cell).' OR '.$this->db->quoteInto('PhoneNumber=?', '0'.$cell).')';
		
		$select->where($where);
		
		$web && $select->where('AddUser=?', Msd_Config::appConfig()->db->status->name->default);
	
		return $this->one($select);
	}
	
	public function &getGuidByNumber($Number)
	{
		$select = $this->db->select();
		$select->from($this->name());
		
		$where = ' ('.$this->db->quoteInto('PhoneNumber=?', $Number).' OR '.$this->db->quoteInto('PhoneNumber=?', '0'.$Number).')';
		
		$select->where($where);

		return $this->one($select);
	}
	
	public function cellInfo($cell)
	{
		$select = &$this->s();
		$select->from($this->_name);
		$select->where('PhoneType=?', self::$cellPhoneType);
		$select->where('PhoneNumber=?', $cell);
		
		$row = $this->one($select);
		return $row;
	}
	
	public function cellExists($cell, $CustGuid)
	{
		$select = &$this->s();
		$select->from($this->_name);
		$select->where('PhoneType=?', self::$cellPhoneType);
		$select->where('PhoneNumber=?', $cell);
		$select->where('CustGuid=?', $CustGuid);
		
		$row = $this->one($select);
		return isset($row['PhoneNumber']) ? $row[$this->primary()] : false;
	}
	
	public function updateCustomerCell($params, $CustGuid)
	{
		$where = $this->db->quoteInto('CustGuid=?', $CustGuid);
		$where .= ' AND '.$this->db->quoteInto('PhoneType=?', '1');
		
		$result = $this->db->update($this->name(), $params, $where);
		if (!$result) {
			$result = $this->insert(array(
					'CustGuid' => $CustGuid,
					'PhoneType' => self::$cellPhoneType,
					'PhoneNumber' => $params['PhoneNumber'],
					'Disabled' => '0',
					'AddUser' => ''
					));
		}
		
		$where2 = $this->db->quoteInto('PhoneType=?', self::$cellPhoneType).' AND ';
		$where2 .= $this->db->quoteInto('PhoneNumber=?', $params['PhoneNumber']).' AND ';
		$where2 .= $this->db->quoteInto('CustGuid!=?', $CustGuid);
		
		$this->db->update($this->name(), array(
			'CustGuid' => $CustGuid	
			), $where2);
		
		return $result;
	}
	
	public function cellType()
	{
		return self::$cellPhoneType;
	}

	public function insert(array $params)
	{
		$params['AddTime'] = $this->expr('GETDATE()');
		//$params['Disabled'] = 0;
		(isset($params['CustGuid']) && !($params['CustGuid'] instanceof Zend_Db_Expr)) && $params['CustGuid'] = $this->wrapGuid($params['CustGuid']);
		$params['AddUser'] = Msd_Config::appConfig()->db->status->name->default;
		
		return parent::insert($params);
	}
	
	public function insertCell(array $params)
	{
		$params['PhoneType'] = self::$cellPhoneType;
		return $this->insert($params);
	}
}