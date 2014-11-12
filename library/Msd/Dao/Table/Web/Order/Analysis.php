<?php

class Msd_Dao_Table_Web_Order_Analysis extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'OrderAnalysis';
		$this->_primary = 'OrderGuid';
		$this->_primaryIsGuid = true;
		
		$this->nullKeys = array(
				'LastChangeTime', 'ReqDateTime'
				);
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function lastOrderTime()
	{
		$select = &$this->s();
		$select->from($this->sn('oa'), array(
			$this->expr('MAX(RealAddTime) AS RealAddTime')	
			));
		$row = $this->one($select);
		
		return $row['RealAddTime'] ? $row['RealAddTime'] : 0;
	}
	
	public function &nullVendorRows()
	{
		$rows = array();
		
		$select = &$this->s();
		$select->from($this->sn('oa'));
		$select->where('VendorName IS NULL OR VendorName=?', '');
		
		$rows = $this->all($select);
		
		return $rows;
	}
	
	public function &search(&$pager, $params=array(), $sort=array())
	{
		$rows = array();		
		
		switch ($params['s_date_key']) {
			case 'assigned':
				$s_date_key = 'AssignedTime';
				break;
			case 'issued':
				$s_date_key = 'InformTime';
				break;
			default:
				$s_date_key = 'AddTime';
				break;
		}

		$timeout = (int)$params['timeout'];
		
		$s_time = $params['s_date'].' '.$params['s_hour'].':'.$params['s_minute'].':00';
		$e_time = $params['e_date'].' '.$params['e_hour'].':'.$params['e_minute'].':59';
		
		$oTable = &$this->t('order');
		$select = &$this->s();
		$select->from($this->sn('oa'), array(
			'oa.*',
			$this->expr('DATEDIFF(MINUTE, oa.'.$s_date_key.', oa.DeliveryedTime) AS Costs')	,
			$this->expr('oa.'.$s_date_key.' AS ThisTime')
			));
		$select->join($oTable->sn('o'), 'o.OrderGuid=oa.OrderGuid', array());
		
		if ($params['without_pre']) {
			$select->where('oa.ReqDateTime IS NULL');
		}
		
		if ($params['without_chg']) {
			$select->where('oa.LastChangeTime IS NULL');
		}
		
		if ($params['deliver']) {
			$select->where('oa.Deliver=?', $params['deliver']);
		}
		
		if (trim($params['is_vip'])) {
			$select->where('oa.IsVip=?', (int)$params['is_vip']);
		}
		
		if ($params['city_id']) {
			$select->where('o.CityId=?', $params['city_id']);
		}
		
		if ($params['freight']) {
			$freight = (int)$params['freight'];
			switch ($freight) {
				case 8:
					$select->where('oa.Distance<=?', 3000);
					break;
				case 15:
					$select->where('oa.Distance>3000 AND oa.Distance<=5000');
					break;
				case 18:
					$select->where('oa.Distance>5000 AND oa.Distance<=6000');
					break;
			}
		}

// 		if ($timeout>0) {
// 			$select->where('DATEDIFF(MINUTE, oa.'.$s_date_key.', oa.DeliveryedTime)>?', $timeout);
// 		}
		
		if (isset($params['IsTimeout'])) {
			$select->where('IsTimeout=?', (int)$params['IsTimeout']);
		}
		
		$select->where('oa.RealAddTime>=?', $s_time);
		$select->where('oa.RealAddTime<=?', $e_time);
		
		$select->order('Costs DESC');

		$result = $this->all($select);

		$i = 0;
		foreach ($result as $row) {
			$row['_seq'] = $offset + 1 + ($i++);
			$rows[] = $row;
		}

		$pager['total'] = $this->_summary($params);
		
		return $rows;
	}
	
	protected function _summary(array $params)
	{
		$timeout = (int)$params['timeout'];
		
		$s_time = $params['s_date'].' '.$params['s_hour'].':'.$params['s_minute'].':00';
		$e_time = $params['e_date'].' '.$params['e_hour'].':'.$params['e_minute'].':59';
		
		$oTable = &$this->t('order');
		$select = &$this->s();
		$select->from($this->sn('oa'), 'COUNT(*) AS total');
		$select->join($oTable->sn('o'), 'o.OrderGuid=oa.OrderGuid', array());
		
		if ($params['without_pre']) {
			$select->where('oa.ReqDateTime IS NULL');
		}
		
		if ($params['without_chg']) {
			$select->where('oa.LastChangeTime IS NULL');
		}
		
		if ($params['deliver']) {
			$select->where('oa.Deliver=?', $params['deliver']);
		}
		
		if (strlen($params['is_vip'])) {
			$select->where('oa.IsVip=?', (int)$params['is_vip']);
		}
		
		if ($params['city_id']) {
			$select->where('o.CityId=?', $params['city_id']);
		}
		
		if ($params['freight']) {
			$freight = (int)$params['freight'];
			switch ($freight) {
				case 8:
					$select->where('oa.Distance<=?', 3000);
					break;
				case 15:
					$select->where('oa.Distance>3000 AND oa.Distance<=5000');
					break;
				case 18:
					$select->where('oa.Distance>5000 AND oa.Distance<=6000');
					break;
			}
		}
		
		switch ($params['s_date_key']) {
			case 'assigned':
				$s_date_key = 'AssignedTime';
				break;
			case 'issued':
				$s_date_key = 'InformTime';
				break;
			default:
				$s_date_key = 'AddTime';
				break;
		}		
		
		if ($timeout>0) {
			$select->where('DATEDIFF(MINUTE, oa.'.$s_date_key.', DeliveryedTime)>?', $timeout);
		}
		
		if (isset($params['IsTimeout'])) {
			$select->where('IsTimeout=?', (int)$params['IsTimeout']);
		}

		$select->where('oa.RealAddTime>=?', $s_time);
		$select->where('oa.RealAddTime<=?', $e_time);

		$row = $this->one($select);

		return (int)$row['total'];
	}
	
	public function summary(array $params)
	{
		$data = array();
		
		$p1 = $p2 = $p3 = $p4 = $p5 = $params;
		$p1['timeout'] = 0;
		$p1['without_pre'] = 0;
		$p1['without_chg'] = 0;
		$p1['is_vip'] = '';
		$data['total'] = $this->_summary($p1);
		
		$p2['freight'] = 8;
		$p2['IsTimeout'] = 1;
		$data['freight_8'] = $this->_summary($p2);
		
		$p3['freight'] = 15;
		$p3['IsTimeout'] = 1;
		$data['freight_15'] = $this->_summary($p3);
		
		$p4['freight'] = 18;
		$p4['IsTimeout'] = 1;
		$data['freight_18'] = $this->_summary($p4);
		
		$p5['IsTimeout'] = 1;
		$data['timeout_total'] = $this->_summary($p5);

		return $data;		
	}
}