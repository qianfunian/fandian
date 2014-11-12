<?php

class Msd_Dao_Table_Web_Article_Category extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'ArticleCategory';
		$this->_primary = 'CategoryId';
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
	
		if ($where['CategoryName']) {
			$select->where('CategoryName LIKE ?', '%'.$where['CategoryName'].'%');
			$cSelect->where('CategoryName LIKE ?', '%'.$where['CategoryName'].'%');
		}
		
		if ($where['CityId']) {
			$select->where('CityId=?', $where['CityId']);
			$cSelect->where('CityId=?', $where['CityId']);
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