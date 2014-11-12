<?php

class Msd_Dao_Table_Web_Partner_Ordermap extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'PartnerOrderMap';
		$this->_primary = 'OrderGuid';
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
		$params['AddTime'] = $this->expr('GETDATE()');
		
		return parent::insert($params);
	}	
	
	public function getByPartnerId($OrderId, $Partner)
	{
		$select = &$this->s();
		$select->from($this->sn('po'));
		$select->where('PartnerOrderId=?', $OrderId);
		$select->where('Partner=?', $Partner);
		$select->limit(1);

		return $this->one($select);
	}
	
}