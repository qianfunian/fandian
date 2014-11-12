<?php

class Msd_Dao_Table_Server_Chat extends Msd_Dao_Table_Server
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'D_Chat';
		$this->_primary = 'ID';
		$this->_orderKey = 'ID';
		$this->_realPrimary = 'ID';
		$this->_primaryIsGuid = false;
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	public function &toDispatch(array $params)
	{
		$rows = array();
		
		$start = $params['start'];
		$end = $params['end'];
		
		$select = &$this->s();
		$select->from($this->sn('c'));
		$select->where('c.[SendTime]>=?', $start);
		$select->where('c.[SendTime]<?', $end);
		$select->order('c.ID DESC');
		
		$rows = &$this->all($select);
		
		return $rows;
	}

	public function insert(array $params)
	{
		$params['SendTime'] = $this->expr('GETDATE()');

		return parent::insert($params);
	}
}