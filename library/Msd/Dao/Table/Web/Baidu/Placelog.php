<?php

class Msd_Dao_Table_Web_Baidu_Placelog extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'BaiduPlaceLog';
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
		$params['AddTime'] = $this->expr('GETDATE()');
		
		return parent::insert($params);
	}	
	
	public function &searchAddress($address)
	{
		$select = &$this->s();
		$select->from($this->_name);
		$select->where('Address LIKE ?', $address.'%');
		$select->orwhere('Name LIKE ?', $address.'%');
		$select->limit(1);

		$row = $this->one($select);
		
		return $row ? $row : array();
	}
}