<?php

class Msd_Waimaibao_Coordinate extends Msd_Waimaibao_Base
{
	public static function &searchAddress($address)
	{
		$row = array();
		
		$cacher = &Msd_Cache_Remote::getInstance();
		$cacheKey = 'coordinate_name_'.md5($address);
		$data = $cacher->get($cacheKey);
		
		if (!$data) {
			$table = &Msd_Dao::table('coordinate');
			$row = $table->searchAddress($address);

			if ($row) {
				$data = array(
						'longitude' => $row['Longitude'],
						'latitude' => $row['Latitude']
						);
				$cacher->set($cacheKey, $data);
			}
		}
		
		return $data;
	}
}