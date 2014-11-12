<?php

class Msd_Dao_Table_Server_Order_Issue extends Msd_Dao_Table_Server_Order_Base
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'OrderIssue';
		$this->_primary = 'IssueGuid';
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
}