<?php

class Msd_Dao_Table_Server_Order_Onlinepay extends Msd_Dao_Table_Server_Order_Base
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'OrderOnlinePay';
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
	
	public function doUpdate(array $params, $keyVal)
	{
		$params['AddTime'] = $this->expr('GETDATE()');
		
		return parent::doUpdate($params, $keyVal);
	}
}