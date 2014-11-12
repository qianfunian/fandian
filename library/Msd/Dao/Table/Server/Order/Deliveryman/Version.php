<?php

class Msd_Dao_Table_Server_Order_Deliveryman_Version extends Msd_Dao_Table_Server_Order_Base
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'OrderDeliverymanVersion';
		$this->_primary = 'OdmVersionGuid';
		
		$this->nullKeys = array(
			'Remark', 'AddUser'	
			);
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
}