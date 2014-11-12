<?php

class Msd_Dao_Table_Web_Partner_Orderrefund extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'PartnerOrderRefund';
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
	
	public function insert(array $params)
	{
		$params['CreateTime'] = $this->expr('GETDATE()');
		
		return parent::insert($params);
	}
}