<?php

class Msd_Dao_Table_Web_Order_Announce extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'OrderAnnounce';
		$this->_primary = 'AutoId';
		$this->_orderKey = 'AutoId';
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
		$params['AddTime'] = $this->expr('GETDATE()');
		
		return parent::insert($params);
	}	
	
	public function &last21(array $Regions)
	{
		$select = &$this->s();
		$select->from($this->sn('oa'));
		$select->where('RegionGuid IN (?)', $Regions);
		$select->order($this->_orderKey.' DESC');
		$select->limit(20);
		
		$rows = &$this->all($select);
		
		return $rows;
	}
}