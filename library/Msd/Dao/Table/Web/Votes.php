<?php

class Msd_Dao_Table_Web_Votes extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'Votes';
		$this->_primary = 'AutoId';
		$this->_primaryIsGuid = false;
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
		$count = $pager['limit'] ? (int)$pager['limit'] : 20;
		$page = $pager['page'] ? (int)$pager['page'] : 1;
		$offset = $pager['offset'] ? (int)$pager['offset'] : 0;
		$rows = array();
		
		$select = &$this->s();
		$cSelect = &$this->s();
		
		$select->from($this->sn('v'));
		$cSelect->from($this->sn('v'), 'COUNT(*) AS total');
		
		if ($where['VoteTitle']) {
			$select->where('v.VoteTitle LIKE ?', '%'.$where['VoteTitle'].'%');
			$cSelect->where('v.VoteTitle LIKE ?', '%'.$where['VoteTitle'].'%');
		}
		
		if ($where['Module']) {
			$select->where('v.Module=N?', $where['Module']);
			$cSelect->where('v.Module=N?', $where['Module']);
		}
		
		if (count($order)>0) {
			foreach ($order as $key=>$val) {
				$select->order($key.' '.$val);
			}
		} else {
			$select->order('v.OrderNo ASC');
			$select->order('v.AutoID DESC');
		}
		
		$select->limitPage($page, $count);

		$result = $this->all($select);
		$i = 0;
		foreach ($result as $row) {
			$row['_seq'] = $offset + 1 + ($i++);
			$rows[] = $row;
		}
		
		if (!isset($where['passby_pager'])) {
			$tmp = $this->one($cSelect);
			$pager['total'] = $tmp['total'];
		}
			
		return $rows;
	}
	
	public function insert(array $params)
	{
		$params['AddTime'] = $this->expr('GETDATE()');
		
		return parent::insert($params);
	}
}