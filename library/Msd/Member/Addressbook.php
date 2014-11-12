<?php

class Msd_Member_Addressbook extends Msd_Member_Base
{
	protected static $instances = array();
	protected static $dao = null;
	
	protected function __construct($uid)
	{
		parent::__construct($uid);
		if (self::$dao==null) {
			self::$dao = &Msd_Dao::table('addressbook');
		}
	}
	
	public static function &getInstance($uid)
	{
		if (!isset(self::$instances[$uid])) {
			self::$instances[$uid] = new self($uid);
		}
		
		return self::$instances[$uid];
	}
	
	public function &all()
	{
		$pager = array(
				'limit' => 5,
				);
		
		$rows = self::$dao->search($pager, array(
					'CustGuid' => $this->uid
				), array(
					'OrderNo' => 'ASC'
				));
		
		return $rows;
	}
	
	public function &get($id)
	{
		return self::$dao->getForMember($id, $this->uid);
	}
	
	public function update(array $params, $id)
	{
		return self::$dao->updateForMember($params, $id, $this->uid);
	}
	
	public function delete($id)
	{
		$row = $this->get($id);
		$result = self::$dao->deleteFormMember($id, $this->uid);
		
		if ($row['IsDefault']) {
			$rows = $this->all();
			if (isset($rows[0]['Title'])) {
				$this->resetDefault($row[0]['ABGuid']);
			}
		}
		
		return $result;
	}
	
	public function add(array $params)
	{
		$params['CityId'] || $params['CityId'] = Msd_Config::cityConfig()->city_id;
		return self::$dao->insertForMember($params, $this->uid);
	}
	
	public function resetDefault($id, $params=array())
	{
		Msd_Cookie::set('contactor', $params['Contactor']);
		Msd_Cookie::set('address', $params['Address']);
		Msd_Cookie::set('phone', $params['Address']);
		
		if ($params['CoordGuid']) {
			Msd_Cookie::set('coord_guid', $params['CoordGuid']);
			
			$row = &Msd_Dao::table('coordinate')->get($params['CoordGuid']);
			Msd_Cookie::set('coord_name', $row['CoordName']);
			Msd_Cookie::set('latitude', $params['Latitude']);
			Msd_Cookie::set('longitude', $params['Longitude']);
		}

		return self::$dao->resetDefault($id, $this->uid);
	}
	
	public function getDefault()
	{
		return self::$dao->getDefaultForMember($this->uid);
	}
}