<?php

class Msd_Dao_Table_Server_Sales extends Msd_Dao_Table_Server
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'Sales';
		$this->_primary = 'SalesGuid';
		
		$this->nullKeys = array(
				'PhoneGuid', 'CallPhone'
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
		(isset($params['CustGuid']) && !($params['CustGuid'] instanceof Zend_Db_Expr)) && $params['CustGuid'] = $this->wrapGuid($params['CustGuid']);
		(isset($params['PhoneGuid']) && !($params['PhoneGuid'] instanceof Zend_Db_Expr)) && $params['PhoneGuid'] = $this->wrapGuid($params['PhoneGuid']);
		(isset($params['AddressGuid']) && !($params['AddressGuid'] instanceof Zend_Db_Expr)) && $params['AddressGuid'] = $this->wrapGuid($params['AddressGuid']);
		
		if (isset($params['CoordGuid'])) {
			if (Msd_Validator::isGuid($params['CoordGuid'])) {
				$params['CoordGuid'] = $this->wrapGuid($params['CoordGuid']);
			} else {
				unset($params['CoordGuid']);
				unset($params['CoordName']);
			}
		}
		
		if (isset($params['CoordGuid'])) {
			$params['CoordValue'] = $this->wrapPoint($params['Longitude'], $params['Latitude']);
		}else{
			$params['CoordValue'] = NULL;
		}
		unset($params['Longitude']);
		unset($params['Latitude']);
		
		$params['CreatedTime'] = $this->expr('GETDATE()');
		$params['AddTime'] = $this->expr('GETDATE()');
		$params['AddUser'] || $params['AddUser'] = Msd_Config::appConfig()->db->status->name->default;

		return parent::insert($params);
	}
	
	public function &last5address($CustGuid)
	{
		$rows = array();
		
		$cTable = &$this->t('coordinate');
		$select = &$this->s();
		$select->from($this->sn('s'), array(
			's.CustAddress', 's.CoordGuid'	
			));
		$select->join($cTable->sn('c'), 'c.CoordGuid=s.CoordGuid', array(
			'c.Longitude', 'c.Latitude'	
			));
		$select->where('c.Disabled=?', '0');
		$select->where('s.CustGuid=?', $CustGuid);
		$select->order('s.AddTime DESC');
		$select->limit(5);
		
		$rows = $this->all($select);
		
		return $rows;
	}
}