<?php

class Msd_Api_Translator_Member extends Msd_Api_Translator_Base
{
	protected static $instance = null;

	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	public function translate(array $params)
	{
		$result = array();
		if ($params['CustGuid']) {
			$result['code'] = $params['CustGuid'];
			$result['username'] = $params['UserName'];
			$result['realname'] = $params['RealName'];
			$result['cellphone'] = $params['Cell'];
			$result['address'] = $params['Address'];
			$result['email'] = $params['Email'];
			$result['avatar'] = $params['Avatar'];
			
			$ab = $params['AddressBook'];
			if(!empty($ab))
			{			
				$result['placemark']['id'] = $ab['CoordGuid'];
				$row = &Msd_Dao::table('coordinate')->get($ab['CoordGuid']);
				$result['placemark']['name'] =$row['CoordName'];
				$result['placemark']['position']['longitude'] = $row['Longitude'];
				$result['placemark']['position']['latitude'] = $row['Latitude'];
			}
			
		}
		
		return $result;
	}
}