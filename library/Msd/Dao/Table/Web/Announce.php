<?php

class Msd_Dao_Table_Web_Announce extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'Announce';
		$this->_primary = 'Id';
		
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
		if (!isset($params['PubTime'])) {
			$params['PubTime'] = $this->expr('GETDATE()');
		}
		
		return parent::insert($params);
	}
}