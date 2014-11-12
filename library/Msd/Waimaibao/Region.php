<?php

class Msd_Waimaibao_Region extends Msd_Waimaibao_Base
{
	/**
	 * 获取当前站点下属的区域
	 * 
	 */
	public static function &GetSiteRegions($parent='')
	{
		$regions = array();
		
		if (!$parent) {
			$parent = Msd_Config::cityConfig()->db->guids->root_region;
		}
		$table = &Msd_Dao::table('region');
		$pager = array();
		$tmp = $table->getSubRegions($parent);
		foreach ($tmp as $row) {
			$row['subs'] = $table->getSubRegions($row['RegionGuid']);
			$regions[] = $row;
		}

		return $regions;
	}
	
	public static function &RegionGuids()
	{
		$guids = array();
		$cConfig = &Msd_Config::cityConfig();
		$guids[] = $cConfig->db->guids->root_region;
		
		$_Regions = &Msd_Cache_Loader::Regions();
		foreach ($_Regions as $_region) {
			$guids[] = $_region['RegionGuid'];
		}
		
		return $guids;
	}
}