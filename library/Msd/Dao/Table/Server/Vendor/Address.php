<?php

class Msd_Dao_Table_Server_Vendor_Address extends Msd_Dao_Table_Server
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'VendorAddress';
		$this->_primary = 'VendorGuid';
		
		$this->nullKeys = array(
				'Longitude', 'Latitude', 'CoordValue'
				);
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	public function &getDistance($vendorGuid, $long, $lat)
	{
		$sql = "SELECT [dbo].Fn_GetDistance(v.CoordValue, ".$this->q($long).", ".$this->q($lat).") AS Distance 
FROM VendorAddress AS v  where v.VendorGuid = ".$this->q($vendorGuid);
		$row = $this->db->fetchRow($sql);
		return $row['Distance'];
	}
}