<?php

class Msd_Dao_Table_Server_Customer extends Msd_Dao_Table_Server
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'Customer';
		$this->_primary = 'CustGuid';
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
		$params['AddUser'] || $params['AddUser'] = Msd_Config::appConfig()->db->status->name->default;
		if (isset($params['CustId'])) {
			unset($params['CustId']);
		}
		
		$params['CustName'] || $params['CustName'] = Msd_Config::appConfig()->db->status->name->default;

		return parent::insert($params);
	}
	
	public function newCustId()
	{
		$select = &$this->s();
		$select->from($this->_name, array(
				'CustId'
				));
		$select->where('CustId LIKE ?', 'W'.date('ymd').'%');
		$select->order('CustId DESC');
		$select->limit(1);
		
		$row = $this->one($select);
		if ($row) {
			$suffix = '';

			$CustId = intval(substr($row['CustId'], -6))+1;
			$suffix = str_repeat('0', 6-strlen($CustId)).$CustId;
			$CustId = 'W'.date('ymd').$suffix;
		} else {
			$CustId = 'W'.date('ymd').'000001';
		}
		
		return $CustId;
	}
}