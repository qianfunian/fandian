<?php

class Msd_Dao_Table_Server_Cancelreasons extends Msd_Dao_Table_Server
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'CancelReason';
		$this->_primary = 'CancelGuid';
		$this->_orderKey = 'Reason';
		$this->_realPrimary = 'CancelGuid';
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	public function &allReasons()
	{
		$rows = array();
		
		$select = &$this->s();
		$select->from($this->sn('cr'));
		
		$rows = $this->all($select);
		
		return $rows;
	}
}