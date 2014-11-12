<?php

class Msd_Dao_Table_Server_Deliveryman_Heartbeat extends Msd_Dao_Table_Server
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'DeliverymanHeartBeat';
		$this->_primary = 'AutoId';
		$this->_primaryIsGuid = false;
		
		$this->nullKeys = array('Longitude', 'Latitude', 'CoordValue');
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
}