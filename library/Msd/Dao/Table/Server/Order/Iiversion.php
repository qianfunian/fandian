<?php

class Msd_Dao_Table_Server_Order_Iiversion extends Msd_Dao_Table_Server_Order_Base
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'OIV_Item';
		$this->_primary = 'OIVGuid';
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
		(isset($params['OrdItemGuid']) && !($params['OrdItemGuid'] instanceof Zend_Db_Expr)) && $params['OrdItemGuid'] = $this->wrapGuid($params['OrdItemGuid']);
		
		return parent::insert($params);
	}
}