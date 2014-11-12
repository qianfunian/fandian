<?php

class Msd_Dao_Table_Server_Region extends Msd_Dao_Table_Server
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'Region';
		$this->_primary = 'RegionGuid';
		$this->_orderKey = 'RegionName';
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	public function &getSubRegions($region)
	{
		$pager = array(
			'limit' => 9999,
			'page' => 1,
			'offset' => 0
			);
		
		return $this->search($pager, array(
				'ParentRegion' => $region,
				'_passby_pager' => true
				), array(
				'RegionName' => 'ASC'
				));
	}
	
	public function &search(&$pager, array $where=array(), array $order=array())
	{
		$rows = array();
		$count = $pager['limit'] ? (int)$pager['limit'] : 20;
		$page = $pager['page'] ? (int)$pager['page'] : 1;
		$offset = $pager['offset'] ? (int)$pager['offset'] : 0;
		
		$select = $this->s();
		$cSelect = $this->s();
		
		$select->from($this->sn('r'));
		$cSelect->from($this->sn('r'), 'COUNT(*) AS total');
		
		if ($where['RegionName']) {
			$select->where('RegionName LIKE ?', '%'.$where['RegionName'].'%');
			$cSelect->where('RegionName LIKE ?', '%'.$where['RegionName'].'%');
		}
		
		if ($where['ParentRegion']) {
			$select->where('ParentRegion=?', $where['ParentRegion']);
			$cSelect->where('ParentRegion=?', $where['ParentRegion']);
		}
		
		if (count($order)>0) {
			foreach ($order as $key=>$val) {
				$select->order($key.' '.$val);
			}
		} else {
			$select->order($this->_orderKey.' DESC');
		}

		$select->limitPage($page, $count);

		$result = $this->all($select);
		$i = 0;
		foreach ($result as $row) {
			$row['_seq'] = $offset + 1 + ($i++);
			$rows[] = $row;
		}
		
		(isset($where['_passby_pager']) && $where['_passby_pager']) || $pager['total'] = $this->one($cSelect);

		return $rows;
	}
}