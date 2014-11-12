<?php

class Msd_Dao_Table_Server_Vendor_Confirm extends Msd_Dao_Table_Server
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'VendorConfirm';
		$this->_primary = 'ConfirmGuid';
		
		$this->nullKeys = array(
				'CompletionTime', 'Remark'
				);
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
		$params['ConfirmGuid'] = $this->genGuid();
		$params['AddTime'] = $this->expr('GETDATE()');
		$params['AddUser'] = 'system';
		
		return parent::insert($params);
	}
	
	public function last($OrderGuid)
	{
		$select = &$this->s();
		$select->from($this->sn('vc'));
		$select->where('OrderGuid=?', $OrderGuid);
		$select->order('AddTime DESC');
		$select->limit(1);
		
		return $this->one($select);
	}
}