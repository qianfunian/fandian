<?php

/**
 * 订单评论
 * 
 * @author pang
 *
 */

class Msd_Waimaibao_Order_Comment
{
	public static function &load($OrderGuid, $CustGuid)
	{
		$row = Msd_Dao::table('order/comment')->getByOrderGuid($OrderGuid);
		if ($row['CustGuid']!=$CustGuid) {
			$row = array();
		}
		
		return $row;
	}
	
	public static function &forVendor($VendorGuid)
	{
		$rows = array();
		
		$cacher = &Msd_Cache_Remote::getInstance();
		$key = 'oc_'.$VendorGuid;
		$rows = $cacher->get($key);
		if (!$rows) {
			$pager = array(
				'page' => 1,
				'limit' => 5,
				'offset' => 0	
				);
			$where = $order = array();
			$where['VendorGuid'] = $VendorGuid;
			
			$rows = &Msd_Dao::table('order/comment')->search($pager, $where, $order);
			
			$cacher->set($key, $rows, MSD_ONE_DAY);
		}
		
		return $rows;
	}
}