<?php

class Msd_Dao_Table_Web_Vendor_Gprsprinter extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'VendorGprsPrinter';
		$this->_primary = 'VendorGuid';
		$this->_primaryIsGuid = true;
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
	
		return self::$instance;
	}
	
	public function validateSn($Sn1, $Sn2)
	{
		$row = array();
		
		$select = &$this->s();
		$select->from($this->sn('g'));
		$select->where('Sn1=?', $Sn1);
		$select->where('Sn2=?', $Sn2);
		
		$row = $this->one($select);
		
		return $row;
	}
	
	public function insert(array $params)
	{
		$params['VendorGuid'] = $this->wrapGuid($params['VendorGuid']);
		$params['AddTime'] = $this->expr('GETDATE()');
		$params['AddUser'] || $params['AddUser'] = 'WebUser';
		
		return parent::insert($params);
	}
	
	public function &getOrder($VendorGuid)
	{
		$row = array();
		
		$opTable = &$this->t('order/printed');
		$oTable = &$this->t('order');
		$ovTable = &$this->t('order/version');
		$olvTable = &$this->t('order/lastversion');
		$oivTable = &$this->t('order/itemversion');
		
		$select = &$this->s();
		$select->from($this->sn('g'));
		$select->join($oTable->sn('o'), 'o.VendorGuid=g.VendorGuid', array(
			'o.OrderGuid', 'o.OIVGuid'	
			));
		$select->join($olvTable->sn('olv'), 'olv.OrderGuid=o.OrderGuid', array());
		$select->join($ovTable->sn('ov'), 'ov.OrdVerGuid=olv.OrdVerGuid', array());
		$select->join($oivTable->sn('oiv'), 'oiv.OIVGuid=ov.OIVGuid', array());
		$select->join($opTable->sn('op'), 'op.OIVGuid=oiv.OIVGuid', array());
		$select->where('g.VendorGuid=?', $VendorGuid);
		$select->where('op.Printed=?', '0');
		$select->limit(1);
		
		$row = $this->one($select);
		
		return $row;
	}
	
	public function &search(&$pager, $where=array(), $order=array())
	{
		
		$rows = array();
		$count = $pager['limit'] ? (int)$pager['limit'] : 20;
		$page = $pager['page'] ? (int)$pager['page'] : 1;
		$offset = $pager['offset'] ? (int)$pager['offset'] : 0;
		
		$vTable = &Msd_Dao::table('vendor');
		
		$select = $this->s();
		$cSelect = $this->s();
		
		$select->from($this->sn('g'));
		$cSelect->from($this->sn('g'), 'COUNT(*) AS total');
		
		$select->join($vTable->sn('v'), 'v.VendorGuid=g.VendorGuid', 'v.VendorName');
		$cSelect->joinleft($vTable->sn('v'), 'v.VendorGuid=g.VendorGuid', array());

		if ($where['VendorName']) {
			$select->where('v.VendorName LIKE ?', '%'.$where['VendorName'].'%');
			$cSelect->where('v.VendorName LIKE ?', '%'.$where['VendorName'].'%');
		}
		
		if (count($order)>0) {
			foreach ($order as $key=>$val) {
				$select->order($key.' '.$val);
			}
		} else {
			$select->order($this->primary().' DESC');
		}
		
		$select->limitPage($page, $count);
		
		$result = $this->all($select);
		$i = 0;
		foreach ($result as $row) {
			$row['_seq'] = $offset + 1 + ($i++);
			$rows[] = $row;
		}
		
		if (!isset($where['passby_pager'])) {
			$tmp = $this->one($cSelect);
			$pager['total'] = $tmp['total'];
		}
		
		return $rows;		
	}
}
