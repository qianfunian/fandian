<?php

class Msd_Dao_Table_Web_Vendor_Extend extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'VendorExtend';
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
	
	public function insert(array $params)
	{
		$params['VendorGuid'] = $this->wrapGuid($params['VendorGuid']);
		$params['OrderNo'] || $params['OrderNo'] = '9999';
		return parent::insert($params);
	}
	
	public function topSales($limit=10)
	{
		$select = &$this->s();
		$vTable = &$this->t('vendor');
		
		$select->from($this->sn('ve'), 've.HotRate');
		$select->join($vTable->sn('v'), 've.VendorGuid=v.VendorGuid', array('v.VendorName'));
		
		$select->order('ve.HotRate DESC');
		$select->limit($limit);
		
		$rows = $this->all($select);
		
		return $rows;
	}
}