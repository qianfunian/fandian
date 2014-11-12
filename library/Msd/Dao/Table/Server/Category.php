<?php

class Msd_Dao_Table_Server_Category extends Msd_Dao_Table_Server
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'Category';
		$this->_primary = 'CtgGuid';
		$this->_orderKey = 'CtgName';
		$this->_realPrimary = 'CtgGuid';
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
	
		$stdTable = $this->t('category/standard');
	
		$select = $this->s();
		$cSelect = $this->s();
	
		$select->from($this->sn('c'), '*');
		$select->join($stdTable->sn('std'), 'c.CtgStdGuid=std.CtgStdGuid', 'std.CtgStdName');
	
		$cSelect->from($this->sn('c'), 'COUNT(*) AS total');
		$cSelect->join($stdTable->sn('std'), 'c.CtgStdGuid=std.CtgStdGuid', '');
	
		if ($where['CtgStdName']) {
			$select->where('std.CtgStdName=?', $where['CtgStdName']);
			$cSelect->where('std.CtgStdName=?', $where['CtgStdName']);
		}
	
		if (count($order)>0) {
			foreach ($order as $key=>$val) {
				$select->order($key.' '.$val);
			}
		} else {
			$select->order('c.'.(is_array($this->_orderKey) ? $this->_realPrimary : $this->_orderKey).' DESC');
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
	
	public function &Categories($CtgGroupGuid)
	{
		$rows = array();
		$select = &$this->s();
		
		$csTable = &$this->t('category/standard');
		$cgmTable = &$this->t('category/groupmember');
		
		$select->from($this->sn('c'), array(
				'c.CtgStdGuid', 'c.CtgName'
				));
		$select->join($cgmTable->sn('cgm'), 'cgm.CtgGuid=c.CtgGuid', array());
		
		$select->where('cgm.CtgGroupGuid=?', $CtgGroupGuid);

		$tmp = $this->all($select);
		if ($tmp) {
			foreach ($tmp as $row) {
				$rows[$row['CtgStdGuid']] = $row['CtgName'];
			}
		}
		
		return $rows;
	}
	
	public function &NCategories($CtgGroupGuid)
	{
		$rows = array();
		$select = &$this->s();
		
		$csTable = &$this->t('category/standard');
		$cgmTable = &$this->t('category/groupmember');
		
		$select->from($this->sn('c'), array(
			'c.CtgStdGuid', 'c.CtgName'
			));
		$select->join($csTable->sn('cs'), 'cs.CtgStdGuid=c.CtgStdGuid', array(
			'cs.CtgStdName'
			));
		$select->join($cgmTable->sn('cgm'), 'cgm.CtgGuid=c.CtgGuid', array());
		
		$select->where('cgm.CtgGroupGuid=?', $CtgGroupGuid);

		$tmp = $this->all($select);
		if ($tmp) {
			foreach ($tmp as $row) {
				$rows[$row['CtgStdName']] = $row['CtgName'];
			}
		}
		
		return $rows;
	}
}