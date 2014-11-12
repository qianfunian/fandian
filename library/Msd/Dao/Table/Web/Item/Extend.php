<?php

class Msd_Dao_Table_Web_Item_Extend extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'ItemExtend';
		$this->_primary = 'ItemGuid';
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
		$params['ItemGuid'] = $this->wrapGuid($params['ItemGuid']);
		$params['Sales'] = 0;
		return parent::insert($params);
	}
	
	public function increaseSales($ItemGuid, $SalesAdded)
	{
		$sql = "UPDATE ".$this->_name." SET Sales=Sales+".((int)$SalesAdded)." WHERE ItemGuid='".$ItemGuid."'";
		return $this->db->query($sql);
	}
}