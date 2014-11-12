<?php

class Msd_Hook_Partner extends Msd_Hook_Base
{
	protected static $instance = null;
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function NewOrderCreated(array $params)
	{
		$OrderGuid      = $params['OrderGuid'];
		$SalesAttribute = $params['SalesAttribute'];
		$hash           = $params['Hash'];
		$PartnerData    = $params['PartnerData'];
		$cConfig        = &Msd_Config::cityConfig();
		
		if (Msd_Validator::isGuid($SalesAttribute)) {
			switch ($SalesAttribute) {
				case $cityConfig->db->guids->giftcard:
					//	更新生日卡表，写入卡号与订单号的对应关系
					$giftcode = $PartnerData['giftcode'];
					Msd_Dao::table('giftcard')->doUpdate(array(
					'UsedTime' => date('Y-m-d H:i:s'),
					'OrderGuid' => $OrderGuid
					), $giftcode);
					//	写order/hash，保存生日卡号对应的已付金额
					Msd_Dao::table('order/hash')->doUpdate(array(
						'Payed' => 1,
						'PayedMoney' => $PartnerData['giftvalue'],
						'BankApi' => $cityConfig->partner->giftcard->pay_bank
						), $hash);
					break;
				case $cityConfig->db->sales_attr->v12580:
					$data = &Msd_Dao::table('order/version')->getBefore($OrderGuid, 1);
					$PayedMoney = $PartnerData['total_money'];
					
					Msd_Log::getInstance()->debug($PayedMoney);
					$pp = array(
						'Payed' => '1',
						'PayedMoney' => $PayedMoney,
						'BankApi' => $cConfig->partner->v12580->pay_bank
						);
					
					Msd_Dao::table('order/hash')->doUpdate($pp, $hash);
					
					Msd_Dao::table('sales')->doUpdate($params['SalesGuid'], array(
						'Paid' => 1
						));
					
					break;
			}
		}
	}
}
