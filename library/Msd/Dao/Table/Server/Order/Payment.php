<?php

class Msd_Dao_Table_Server_Order_Payment extends Msd_Dao_Table_Server_Order_Base
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'OrderPayment';
		$this->_primary = 'PaymentGuid';
		
		$this->nullKeys = array(
				'BankApi', 'BankId', 'PaidMoney', 'CallbackSign'
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
		(isset($params['OrderGuid']) && !($params['OrderGuid'] instanceof Zend_Db_Expr)) && $params['OrderGuid'] = $this->wrapGuid($params['OrderGuid']);
		$params['AddTime'] = $this->expr('GETDATE()');

		return parent::insert($params);
	}
}