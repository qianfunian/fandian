<?php

class Msd_Waimaibao_Order_Status
{
	public static function &all()
	{
		$rows = array();
		
		$tmp = &Msd_Dao::table('order/status')->fetchAll();
		foreach ($tmp as $row) {
			$rows[$row['StatusId']] = $row->toArray();
		}

		return $rows;
	}
	
	public static function publicStatusName($StatusId)
	{
		$data = &Msd_Cache_Loader::OrderStatus();

		return isset($data[$StatusId]['PublicName']) ? $data[$StatusId]['PublicName'] : '未知<!--'.$StatusId.'-->';
	}
	
	public static function isConfirmed($StatusId)
	{
		return ($StatusId==Msd_Config::appConfig()->order->status->confirmed) ? true : false;
	}
	
	public static function isDelivered($StatusId)
	{
		return ($StatusId==Msd_Config::appConfig()->order->status->delivered) ? true : false;
	}
}