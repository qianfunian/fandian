<?php

class Msd_Dao_Table_Web_Order_Token extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'OrderIphoneToken';
		$this->_primary = 'OrderGuid';
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
	
	public function &getOrderGuidsByToken($token, $limit=10)
	{
		$guids = array();
		
		$select = &$this->s();
		$select->from($this->sn('ot'));
		$select->where('Token=?', $token);
		$select->order('AddTime DESC');
		$select->limit($limit);

		$rows = &$this->all($select);
		foreach ($rows as $row) {
			$guids[] = $row['OrderGuid'];
		}
		
		return $guids;
	}
}