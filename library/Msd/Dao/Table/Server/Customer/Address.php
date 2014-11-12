<?php

class Msd_Dao_Table_Server_Customer_Address extends Msd_Dao_Table_Server_Customer_Base
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'CustomerAddress';
		$this->_primary = 'AddressGuid';
		$this->compatInsert = true;
		
		$this->nullKeys = array(
			'CoordGuid', 'CoordName', 'CoordValue', 'Remark'	
			);
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function &last5Address($CustGuid)
	{
		$rows = array();
		
		$select = &$this->s();
		$select->from($this->_name, array(
				'CustAddress',
				'CoordGuid',
				'CoordName',
				$this->expr('CoordValue.Lat AS Latitude'),
				$this->expr('CoordValue.Long AS Longitude')
		));
		//$select->where('Disabled=?', '0');
		$select->where('CustGuid=?', $CustGuid);
		$select->where('CoordGuid IS NOT NULL');
		$select->order('AddTime DESC');
		$select->limit(5);
		$rows = $this->all($select);
		
		return $rows;	
	}
	
	public function CustLastAddress($CustGuid)
	{
		$select = &$this->s();
		$select->from($this->_name, array(
				'CustAddress',
				'CoordGuid',
				'CoordName',
				$this->expr('CoordValue.Lat AS Latitude'),
				$this->expr('CoordValue.Long AS Longitude')
				));
		//$select->where('Disabled=?', '0');
		$select->where('CustGuid=?', $CustGuid);
		$select->where('CoordGuid IS NOT NULL');
		$select->order('AddTime DESC');
		$select->limit(1);

		return $this->one($select);
	}
	
	public function addressExists($address, $CustGuid)
	{
		$select = &$this->select();
		$select->from($this->_name);
		$select->where('CustGuid=?', $CustGuid);
		$select->where('CustAddress=?', $address);
		$select->limit(1);
		
		$row = $this->one($select);
		
		return isset($row[$this->primary()]) ? $row[$this->primary()] : false;
	}

	public function insert(array $params)
	{
		$params['AddTime'] = $this->expr('GETDATE()');
		//$params['Disabled'] = 0;
		(isset($params['CustGuid']) && !($params['CustGuid'] instanceof Zend_Db_Expr)) && $params['CustGuid'] = $this->wrapGuid($params['CustGuid']);
		
		if (isset($params['CoordGuid']) && Msd_Validator::isGuid($params['CoordGuid'])) {
			$params['CoordGuid'] = $this->wrapGuid($params['CoordGuid']);
		} else {
			unset($params['CoordGuid']);
			unset($params['CoordName']);
			unset($params['CoordValue']);
		}
		
		if (isset($params['Longitude']) || isset($params['Latitude'])) {
			$params['CoordValue'] = $this->wrapPoint((float)$params['Longitude'], (float)$params['Latitude']);
			unset($params['Longitude']);
			unset($params['Latitude']);
		}
		
		trim($params['AddUser']) || $params['AddUser'] = Msd_Config::appConfig()->db->status->name->default;
		
		return parent::insert($params);
	}
}