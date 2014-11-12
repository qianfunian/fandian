<?php

class Msd_Dao_Table_Web_Order_Comment extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'OrderComments';
		$this->_primary = 'AutoId';
		$this->_orderKey = 'CreateTime';
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function &getByOrderGuid($OrderGuid)
	{
		$select = &$this->s();
		$select->from($this->sn('oc'));
		$select->where('OrderGuid=?', $OrderGuid);
		
		$row = $this->one($select);

		return $row;
	}

	public function insert(array $params)
	{
		if (!isset($params['CreateTime'])) {
			$params['CreateTime'] = $this->expr('GETDATE()');
		}
		
		return parent::insert($params);
	}
	
	
	public function &search(&$pager, array $where=array(), array $order=array())
	{
		$rows = array();
		$count = $pager['limit'] ? (int)$pager['limit'] : 20;
		$page = $pager['page'] ? (int)$pager['page'] : 1;
		$offset = $pager['offset'] ? (int)$pager['offset'] : 0;
	
		$oTable = &$this->t('order');
		$olvTable = &$this->t('order/lastversion');
		$ovTable = &$this->t('order/version');
		$sTable = &$this->t('sales');
		$svTable = &$this->t('sales/version');
		$osTable = &$this->t('order/status');
		$cTable = &$this->t('customer');
		$vTable = &$this->t('vendor');
		$vaTable = &$this->t('vendor/address');
		$oivTable = &$this->t('order/itemversion');
		$fTable = &$this->t('freight/version');
		$odmTable = &$this->t('order/deliveryman');
		$odmvTable = &$this->t('order/deliveryman/version');
		$dmTable = &$this->t('deliveryman');
	
		$select = $this->s();
		$cSelect = $this->s();
	
		$select->from($this->sn('oc'), array(
			'oc.CreateTime', 'oc.Content'	
			));
		$cSelect->from($this->sn('oc'), 'COUNT(*) AS total');
		
		$select->join($oTable->sn('o'), 'oc.OrderGuid=o.OrderGuid', 'o.*');
		$cSelect->join($oTable->sn('o'), 'oc.OrderGuid=o.OrderGuid', '');
	
		$select->join($ovTable->sn('ov'), 'ov.OrderGuid=o.OrderGuid', array());
		$cSelect->join($ovTable->sn('ov'), 'ov.OrderGuid=o.OrderGuid', '');
	
		$select->join($oivTable->sn('oiv'), 'ov.OIVGuid=oiv.OIVGuid', array(
				'oiv.ItemCount', 'oiv.ItemAmount', 'oiv.BoxQty', 'oiv.BoxAmount', 'oiv.SumAmount'
		));
		$cSelect->join($oivTable->sn('oiv'), 'ov.OIVGuid=oiv.OIVGuid', '');
	
		$select->join($olvTable->sn('olv'), 'olv.OrdVerGuid=ov.OrdVerGuid', '');
		$cSelect->join($olvTable->sn('olv'), 'olv.OrdVerGuid=ov.OrdVerGuid', '');
	
		$select->join($sTable->sn('s'), 's.SalesGuid=o.SalesGuid', '');
		$cSelect->join($sTable->sn('s'), 's.SalesGuid=o.SalesGuid', '');
	
		$select->join($svTable->sn('sv'), 'sv.SalesVerGuid=ov.SalesVerGuid', array(
				'sv.CustName', 'sv.CustAddress',
		));
		$cSelect->join($svTable->sn('sv'), 'sv.SalesVerGuid=ov.SalesVerGuid', '');
	
		$select->join($osTable->sn('os'), 'os.StatusId=ov.StatusId', array(
				'os.StatusId', 'os.StatusName'
		));
		$cSelect->join($osTable->sn('os'), 'os.StatusId=ov.StatusId', '');
	
		$select->join($cTable->sn('c'), 'c.CustGuid=s.CustGuid', '');
		$cSelect->join($cTable->sn('c'), 'c.CustGuid=s.CustGuid', '');

		$select->join($vTable->sn('v'), 'v.VendorGuid=p.VendorGuid', array(
				'v.VendorName'
		));
		$cSelect->join($vTable->sn('v'), 'v.VendorGuid=p.VendorGuid', '');
	
		$select->join($vaTable->sn('va'), 'v.VendorGuid=va.VendorGuid', array(
				$this->expr('va.Address AS vaAddress'), $this->expr('va.Longitude AS vaLongitude'), $this->expr('va.Latitude AS vaLatitude')
		));
		$cSelect->join($vaTable->sn('va'), 'v.VendorGuid=va.VendorGuid', '');
	
		$select->join($fTable->sn('f'), 'f.FrtVerGuid=ov.FrtVerGuid', array(
				'f.PaymentMethod', 'f.Distance', 'f.Freight'
		));
		$cSelect->join($fTable->sn('f'), 'f.OrderGuid=o.OrderGuid', '');
		
		if ($where['VendorGuid']) {
			$select->where('v.VendorGuid=?', $where['VendorGuid']);
			$cSelect->where('v.VendorGuid=?', $where['VendorGuid']);
		}
		
		if ($where['CustGuid']) {
			$select->where('oc.CustGuid=?', $where['CustGuid']);
			$cSelect->where('oc.CustGuid=?', $where['CustGuid']);
		}
		
		if (count($order)>0) {
			foreach ($order as $key=>$val) {
				$select->order($key.' '.$val);
			}
		} else {
			$select->order($this->_orderKey.' DESC');
		}

		$select->limitPage($page, $count);

		$result = $this->all($select);
		$i = 0;
		foreach ($result as $row) {
			$row['_seq'] = $offset + 1 + ($i++);
			$rows[] = $row;
		}
	
		$tmp = $this->one($cSelect);
		$pager['total'] = $tmp['total'];
	
		return $rows;
	}	
}