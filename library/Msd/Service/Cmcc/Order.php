<?php

class Msd_Service_Cmcc_Order extends Msd_Service_Cmcc_Base
{
	
	/**
	 * 更新订单状态到12580
	 * 
	 * @param string $OrderGuid
	 */
	public static function updateOrderStatus($OrderGuid)
	{
		$config = &Msd_Config::cityConfig();
		$url = $config->service->v12580->order->url;
		$params = array();
		
		$d = Msd_Dao::table('partner/ordermap')->get($OrderGuid);
		if (isset($d['PartnerOrderId'])) {
			$params['order_id'] = $OrderId = $d['PartnerOrderId'];
			$params['city'] = $city = $config->zone;
			
			$pager = $where = $sort = array();
			$pager['page'] = $pager['limit'] = 1;
			$where['OrderGuids'] = array(
				$OrderGuid	
				);
			
			$rows = &Msd_Dao::table('order')->search($pager, $where, $sort);
			if (is_array($rows) && count($rows)>0) {
				$data = &$rows[0];
				
				//	已付款金额
				$hash = Msd_Dao::table('order/hash')->Order2Hash($OrderGuid);
				$params['money'] = 0;
				
				$SumAmount = $data['SumAmount'];
				$moneyOffset = $SumAmount - $hash['PayedMoney'];
				$params['increment'] = $moneyOffset>0 ? $moneyOffset : (0 - $moneyOffset);
				$params['decrease'] = $moneyOffset>0 ? (0-$moneyOffset) : $moneyOffset;
				
				$params['delivery_status'] = self::transOrderStatus($data['StatusId']);
			}
		}
	}
	
	protected static function transOrderStatus($StatusId)
	{
		$status = 'unknown';
		$config = &Msd_Config::appConfig()->db->status;
		
		switch ($StatusId) {
			case $config->confirmed:
				$status = 'sending';
				break;
			case $config->delivered:
			case $config->invoiced:
				$status = 'sent';
				break;
			default:
				if (preg_match('/^cancel/i', $StatusId)) {
					$status = 'cancel';
				}
				break;
		}
		
		return $status;
	}
	
}