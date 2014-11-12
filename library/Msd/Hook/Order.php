<?php

class Msd_Hook_Order extends Msd_Hook_Base
{
	protected static $instance = null;
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function MemberLogin(array $params=array())
	{
		$uid = $params['uid'];
		$member = Msd_Member::getInstance($uid);
		$sess = &Msd_Session::getInstance();

		$extend = &$member->extend();
		$CoordGuid = $Address = '';
		if ($extend['AddressBook']['Address']) {
			$Address = $extend['AddressBook']['Address'];
			$CoordGuid = $extend['AddressBook']['CoordGuid'];
		} else if ($extend['Address']) {
			$Address = $extend['Address'];
			if ($extend['Coord']['CoordGuid']) {
				$CoordGuid = $extend['Coord']['CoordGuid'];
			}
		} else {
			$Address = $member->Address;
		}
		
		//Msd_Cookie::set('contactor', $extend['RealName']);
		//Msd_Cookie::set('phone', $extend['cell']);
		//Msd_Cookie::set('address', $Address);

		if ($CoordGuid) {
			//Msd_Cookie::set('coord_guid', $CoordGuid);
		}
	}
}