<?php

class Msd_Dao_Table_Web_Api_Keys extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'ApiKeys';
		$this->_primary = 'Id';
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
	
		return self::$instance;
	}

	public function &search(&$pager, array $where=array(), array $order=array())
	{
		$rows = array();
		$count = $pager['limit'] ? (int)$pager['limit'] : 20;
		$page = $pager['page'] ? (int)$pager['page'] : 1;
		$offset = $pager['offset'] ? (int)$pager['offset'] : 0;
	
		$select = $this->s();
		$cSelect = $this->s();
	
		$select->from($this->_name);
		$cSelect->from($this->_name, 'COUNT(*) AS total');
	
		if ($where['Owner']) {
			$select->where('Owner LIKE ?', '%'.$where['Owner'].'%');
			$cSelect->where('Owner LIKE ?', '%'.$where['Owner'].'%');
		}
		
		if ($where['ApiKey']) {
			$select->where('ApiKey LIKE ?', '%'.$where['ApiKey'].'%');
			$cSelect->where('ApiKey LIKE ?', '%'.$where['ApiKey'].'%');
		}
	
		if (count($order)>0) {
			foreach ($order as $key=>$val) {
				$select->order($key.' '.$val);
			}
		} else {
			$select->order($this->primary().' DESC');
		}
	
		$select->limitPage($page, $count);
	
		$result = $this->all($select);
		$i = 0;
		foreach ($result as $row) {
			$row['_seq'] = $offset + 1 + ($i++);
			$rows[] = $row;
		}
	
		$tmp = $this->one($cSelect);
		$pager['total'] = $tmp['total'];
	
		return $rows;
	}	

	public function insert(array $params)
	{
		$params['AddTime'] = $this->expr('GETDATE()');
		return parent::insert($params);
	}
	
	public function GetMax()
	{
		$select = $this->db->select();
		$select->from($this->_name, $this->expr('MAX(Id) AS MaxId'));
		$row = $this->one($select);

		return (int)$row;
	}
	
	public function CheckKeyExists($key, $id)
	{
		$exists = false;
		
		$row = $this->getByKey($key);

		if ($row['ApiKey'] && $row['Id']!=$id) {
			$exists = true;
		}
		
		return $exists;
	}
	
	public function &getByKey($key)
	{
		$select = &$this->s();
		$select->from($this->_name);
		$select->where('ApiKey=?', $key);
		
		return $this->one($select);
	}
}