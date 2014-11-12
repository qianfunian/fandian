<?php
class Msd_Waimaibao_Category extends Msd_Waimaibao_Base {
	public static function &Vendor($cityId = MSD_FORCE_CITY) {
		$cConfig = &Msd_Config::cityConfig ( $cityId );
		$pager = array (
				'page' => 1,
				'limit' => 999,
				'skip' => 0 
		);
		$config = &Msd_Config::appConfig ();
		$cacher = &Msd_Cache_Remote::getInstance ( $cityId );
		
		$key = 'vendor_categories';
		$rows = $cacher->get ( $key );
		
		if (! $rows) {
			$tmp = &Msd_Dao::table ( 'vendor' )->Categories ( $cConfig->city_id );
			$rows = array ();
			foreach ( $tmp as $row ) {
				if ($row ['CtgName'] != $config->db->n->service_name->afternoon && $row ['CtgName'] != $config->db->n->service_name->night) {
					$rows [] = $row;
				}
			}
			$cacher->set ( $key, $rows, MSD_ONE_DAY );
		}
		
		return $rows;
	}
}
