<?php

class Msd_Dao_Table_Web_Systemlog extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'SystemLog';
		$this->_primary = 'AutoId';
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
		$params['ActionTime'] = $this->expr('GETDATE()');
		return parent::insert($params);
	}
	
	public function &search(&$pager, array $where=array(), array $order=array())
	{
		$rows = array();
		$count = $pager['limit'] ? (int)$pager['limit'] : 20;
		$page = $pager['page'] ? (int)$pager['page'] : 1;
		$offset = $pager['offset'] ? (int)$pager['offset'] : 0;
		
		$uTable = &Msd_Dao::table('sysuser');
		
		$select = $this->s();
		$cSelect = $this->s();
		
		$select->from($this->sn('l'));
		$select->joinleft($uTable->sn('u'), 'l.Uid=u.AutoId', 'u.Username');
		
		$cSelect->from($this->sn('l'), 'COUNT(*) AS total');
		$cSelect->joinleft($uTable->sn('u'), 'l.Uid=u.AutoId', '');
		
		if ($where['Username']) {
			$select->where('u.Username LIKE ?', '%'.$where['Username'].'%');
			$cSelect->where('u.Username LIKE ?', '%'.$where['Username'].'%');
		}
		
		if ($where['from']) {
			$select->where('l.ActionTime>=?', $where['from']);
			$cSelect->where('l.ActionTime>=?', $where['from']);
		}		
		
		if ($where['to']) {
			$select->where('l.ActionTime<=?', $where['to']);
			$cSelect->where('l.ActionTime<=?', $where['to']);
		}
		
		if ($where['ActionType']) {
			$select->where('l.ActionType=?', $where['ActionType']);
			$cSelect->where('l.ActionType=?', $where['ActionType']);
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
}