<?php
class Msd_Dao_Table_Server_Zvendorexpand extends Msd_Dao_Table_Server
{
	protected static $instance = null;

	public function __construct()
	{
		parent::__construct();

		$this->_name = $this->prefix.'ZVendorExpand';
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

	public function doUpdate(array $params, $keyVal)
	{
		return parent::doUpdate($params, $keyVal);
	}
	
}