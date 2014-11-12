<?php

class Msd_Hook_Cookie extends Msd_Hook_Base
{
	protected static $instance = null;
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
// 	public function NewOrderCreated(array $params=array())
// 	{
// 		$CoordGuid = $params['CoordGuid'];
// 		$CoordName = $params['CoordName'];
// 		$Address   = $params['CustAddress'];
		
// 		if (Msd_Validator::isGuid($CoordGuid) && $Address) {
// 			$ads = explode('||', $_COOKIE['pre_adds']);
// 			foreach ($ads as $ad) {
// 				$ads[] = $CoordGuid.'|'.$Address;
// 			}
			
// 			Msd_Cookie::set('pre_adds', trim(implode('||', $ads), '||'));
// 		}
// 	}	
	
	public function MemberLogin(array $params=array())
	{
		$uid = $params['uid'];

		$defaultAB = &Msd_Member_Addressbook::getInstance($uid)->getDefault();
		
		if ($defaultAB) {
			Msd_Cookie::set('contactor', $defaultAB['Contactor']);
			Msd_Cookie::set('phone', $defaultAB['Phone']);
			Msd_Cookie::set('address', $defaultAB['Address']);
			
			if ($defaultAB['CoordGuid']) {
				Msd_Cookie::set('coord_guid', $defaultAB['CoordGuid']);
				$row = &Msd_Dao::table('coordinate')->get($defaultAB['CoordGuid']);
				
				if ($row) {
					Msd_Cookie::set('coord_name', $row['CoordName']);
					Msd_Cookie::set('latitude', $row['Latitude']);
					Msd_Cookie::set('longitude', $row['Longitude']);
				}
			}
		}
	}
}