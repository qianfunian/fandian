<?php

class Msd_Dao_Table_Web_Partner_V12580pushlog extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'PartnerV12580PushLog';
		$this->_primary = 'OrderId';
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
		$params['LastPushTime'] = $this->expr('GETDATE()');
	
		return parent::insert($params);
	}
	
	public function &OrderHasUpdate()
	{
		$rows = array();
		
		$mTable = &$this->t('partner/ordermap');
		$oTable = &$this->t('order');
		$ovTable = &$this->t('order/version');
		$olvTable = &$this->t('order/lastversion');
		$sTable = &$this->t('sales');
		$svTable = &$this->t('sales/version');
		$fTable = &$this->t('freight/version');
		
		$sql = "SELECT l.*, om.OrderGuid
			FROM [dbo].[W_PartnerV12580PushLog] AS l
				INNER JOIN W_PartnerOrderMap AS om ON l.OrderId=om.PartnerOrderId
				INNER JOIN [Order] AS o ON o.OrderGuid=om.OrderGuid
				INNER JOIN [Sales] AS s ON s.SalesGuid=o.SalesGuid 
				INNER JOIN (
					SELECT MAX(AddTime) AS AddTime,OrderGuid
						FROM [OrderStatusLog] 
					GROUP BY OrderGuid
				) AS log ON log.OrderGuid=o.OrderGuid
			WHERE (
				s.AddTime>l.LastPushTime
				OR
				o.AddTime>l.LastPushTime	
				OR
				log.AddTime>l.LastPushTime			
			)
		";

		$rows = &$this->all($sql);
		
		return $rows;
	}
	
	public function updateLastPushTime($OrderId, $LastPushData='')
	{
		$params = array(
			'LastPushTime' => $this->expr('GETDATE()'),
			'LastPushData' => $LastPushData
			);
		
		return $this->doUpdate($params, $OrderId);
	}
}