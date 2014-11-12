<?php

class Msd_Dao_Table_Sms_Sendtask extends Msd_Dao_Table_Sms
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'SendTask';
		$this->_primary = 'TaskID';
		$this->_orderKey = 'TaskID';
		$this->_realPrimary = 'TaskID';
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
		$params['SendFlag'] = 0;
		$params['SendPriority'] = 16;
		$params['SendTime'] = $this->expr('GETDATE()');
		
		return parent::insert($params);
	}
}