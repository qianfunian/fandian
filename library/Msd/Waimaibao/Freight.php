<?php

class Msd_Waimaibao_Freight extends Msd_Waimaibao_Base
{
	public static function calculateByLL($lng, $lat, $VendorGuid)
	{
		$data = array();
		$vaTable = &Msd_Dao::table('vendor/address');
		$frTable = &Msd_Dao::table('freight');
		
		$va = $vaTable->cget($VendorGuid);
		$data['Longitude'] = $va['Longitude'];
		$data['Latitude'] = $va['Latitude'];
		
		$data['distance'] = (int)Msd_Lbs::getDistance($lng, $lat, $va['Longitude'], $va['Latitude']);
		$data['freight'] = $frTable->calculate($data['distance']);
		
		return $data;
	}
	
	public static function calculate($distance, $VendorGuid, $st=null, $ServiceName='普通')
	{
		$data = 0;
		
		if ($distance && $VendorGuid) {
			$cacher = &Msd_Cache_Remote::getInstance();
			$key = 'scv_'.md5($distance.'-'.$VendorGuid);
			//$data = $cacher->get($key);
			if (!$data) {
				//$coTable = &Msd_Dao::table('coordinate');
				//$vTable = &Msd_Dao::table('vendor');
				//$vaTable = &Msd_Dao::table('vendor/address');
				
				//$c = $coTable->get($CoordGuid);
				//$v = $vTable->get($VendorGuid);
				//$va = $vaTable->get($VendorGuid);
				
				//$cLongitude = $c['Longitude'];
				//$cLatitude = $c['Latitude'];
				
				//$vaLongitude = $va['Longitude'];
				//$vaLatitude = $va['Latitude'];
				
				//$distance = Msd_Lbs::getDistance($cLongitude, $cLatitude, $vaLongitude, $vaLatitude);

				$Services = &Msd_Cache_Loader::Services();
				foreach ($Services as $row) {
					$ServiceName==$row['SrvName'] && $FrtGrpGuid = $row['FrtGrpGuid'];
					break;
				}
	
				$data = Msd_Dao::table('freight')->calculate($distance, $FrtGrpGuid);
				
				$cacher->set($key, $data, 7*MSD_ONE_DAY);
			}
		}
		
		return $data;
	}
}
