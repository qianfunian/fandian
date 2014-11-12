<?php

class Msd_Dao_Table_Server_Order_Status_Log extends Msd_Dao_Table_Server_Order_Base
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'OrderStatusLog';
		$this->_primary = 'StatusLogGuid';
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
		$params['AddUser'] || $params['AddUser'] = Msd_Config::appConfig()->db->status->name->default;
		$params['AddTime'] = $this->expr('GETDATE()');
		
		return parent::insert($params);
	}
	
	public function getStatusTime($OrderGuid, $StatusId)
	{
		$select = &$this->s();
		$select->from($this->sn('osl'), array(
			'osl.StatusLogGuid',
			'osl.CityId',
			'osl.OrderGuid',
			'osl.StatusId',
			'osl.Remark',
			'osl.AddUser',
			$this->expr('CONVERT(NVARCHAR, osl.AddTime, 120) AS AddTime')	
			));
		$select->where('OrderGuid=?', $OrderGuid);
		$select->where('StatusId=?', $StatusId);
		$select->order('AddTime ASC');
		$select->limit(1);

		return $this->one($select);
	}
	
	public function getLastStatusTime($OrderGuid, $StatusId)
	{
		$select = &$this->s();
		$select->from($this->sn('osl'), array(
			'osl.StatusLogGuid',
			'osl.CityId',
			'osl.OrderGuid',
			'osl.StatusId',
			'osl.Remark',
			'osl.AddUser',
			$this->expr('CONVERT(NVARCHAR, osl.AddTime, 120) AS AddTime')	
			));
		$select->where('OrderGuid=?', $OrderGuid);
		$select->where('StatusId=?', $StatusId);
		$select->order('AddTime DESC');
		$select->limit(1);

		return $this->one($select);
	}
	
	public function &getOrderStatusLogs($OrderGuid)
	{
		$rows = array();
		
		$osTable = &Msd_Dao::table('order/status');
		
		$select = &$this->s();
		$select->from($this->sn('osl'));
		$select->join($osTable->sn('os'), 'os.StatusId=osl.StatusId', array(
			'os.StatusName',
			'os.PublicName'	
			));
		$select->where('osl.OrderGuid=?', $OrderGuid);
		$select->order('osl.AddTime ASC');
		
		$rows = $this->all($select);
		
		return $rows;
	}
}