<?php

class Msd_Dao_Table_Server_Item_Soldout extends Msd_Dao_Table_Server
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'ItemSoldOut';
		$this->_primary = 'SoldOutGuid';
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	public function &VendorSoldOutItems($VendorGuid)
	{
		$ItemGuids = array();
		
		$select = &$this->s();
		$select->from($this->sn('so'));
		$select->where('VendorGuid=?', $VendorGuid);
		$select->where('StartTime<GETDATE()');
		$select->where('EndTime>GETDATE()');
		
		$rows = $this->all($select);
		foreach ($rows as $row) {
			$ItemGuids[] = $row['ItemGuid'];
		}
		
		return $ItemGuids;
	}
}