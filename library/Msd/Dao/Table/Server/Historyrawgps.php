<?php

class Msd_Dao_Table_Server_Historyrawgps extends Msd_Dao_Table_Server
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'D_HistoryRawGps';
		$this->_primary = 'AutoId';
		$this->_orderKey = 'AutoId';
		$this->_realPrimary = 'AutoId';
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
		$params['AddTime'] = $this->expr('GETDATE()');
		$params['Flag'] = 0;

		return parent::insert($params);
	}
	
	public function &tobeTrans()
	{
		$rows = array();
		
		$select = &$this->s();
		$select->from($this->sn('hrg'), array(
			'AutoId',
			'Flag',
			'DlvManGuid',
			'DlvManId',
			$this->expr('CONVERT(VARCHAR, AddTime, 120) AS AddTime'),
			'Longitude',
			'Latitude'	
			));
		$select->where('hrg.Flag=?', 0);
		
		$rows = &$this->all($select);
		
		return $rows;
	}
}