<?php

/**
 * Apple推送服务相关
 * 
 * @author pang
 *
 */

class Msd_Service_Apple_Apns extends Msd_Service_Apple_Base
{
	public static function bind($CustGuid, $token)
	{
		$table = &Msd_Dao::table('apple/apns');
		$table->doDelete($CustGuid);
		$table->insert(array(
			'CustGuid' => $CustGuid,
			'Token' => $token	
			));
	}
	
	public static function unbind($CustGuid)
	{
		$table = &Msd_Dao::table('apple/apns');
		$table->doDelete($CustGuid);
	}
	
	public static function getTokens(array $uids)
	{
		
	}
	
	/**
	 * 绑定订单token
	 * 
	 * @param unknown $OrderGuid
	 * @param unknown $token
	 */
	public static function bindOrderToken($OrderGuid, $token)
	{
		$table = &Msd_Dao::table('order/token');
		$table->doDelete($OrderGuid);
		$table->insert(array(
			'OrderGuid' => $OrderGuid,
			'Token' => $token	
			));
	}
}