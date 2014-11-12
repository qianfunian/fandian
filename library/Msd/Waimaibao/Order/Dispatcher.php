<?php

/**
 * 调度手机端的服务处理
 * 
 * @author panglei
 *
 */

class Msd_Waimaibao_Order_Dispatcher
{
	const CZ_ADD = 'add';
	const CZ_DEL = 'del';
	const CZ_UPDATE = 'update';
	const C_KEY = 'dispatcher_orders';
	
	protected static $dKey = '';
	
	/**
	 * 生成缓存key
	 * 
	 * @return string
	 */
	public static function dKey()
	{
		if (self::$dKey=='') {
			$hour = (int)date('H');
			
			if ($hour<4) {
				self::$dKey = date('Ymd', time()-MSD_ONE_DAY);
			} else {
				self::$dKey = date('Ymd', time());
			}
			
			self::$dKey .= '_dispatch_';
		}
		
		return self::$dKey;
	}
	
	public static function &load4Somebody($DlvManGuid, $LastHeartBit=0)
	{
		$data = array();
		$cacher = &Msd_Cache_Remote::getInstance();
		$cacheKey = 'dispatcher_orders';
		$sess = &Msd_Session::getInstance();
		$cache = &$cacher->get($cacheKey);
		$cache || $cache = array();
		$sessData = &$sess->get($cacheKey);
		$sessData || $sessData = array();
		$cacheData = array();
		
		if (isset($cache[$DlvManGuid])) {
			$cacheData = &$cache[$DlvManGuid];
		}
		
		$sessKeys = array_keys($sessData);
		$cacheKeys = array_keys($cacheData);
		$OrderGuids = array_merge($sessKeys, $cacheKeys);
		foreach ($OrderGuids as $OrderGuid) {
			if (!isset($sessData[$OrderGuid])) {
				//	new
				$sessData[$OrderGuid] = $cacheData[$OrderGuid];
				$sessData[$OrderGuid]['_HEARTBIT_'] = time();
			} else if (isset($sessData[$OrderGuid]) && !isset($cacheData[$OrderGuid])) {
				$sessData[] = '';
			}
		}
		
		return $data;
	}
	
	protected static function &_load()
	{
		$cacheKey = 'dispatcher_orders';
		$config = &Msd_Config::appConfig();
		$cacher = &Msd_Cache_Remote::getInstance();
		$doTable = &Msd_Dao::table('delivery/order');
		$cpTable = &Msd_Dao::table('customer/phone');
		$dmTable = &Msd_Dao::table('deliveryman');
		$cTable = &Msd_Dao::table('chat');
		
		$now = time();
		if ($hour<4) {
			$start = date('Y-m-d 05:00:00', $now-MSD_ONE_DAY);
			$end = date('Y-m-d 03:00:00', $now);
		} else {
			$start = date('Y-m-d 05:00:00', $now);
			$end = date('Y-m-d 03:00:00', $now+MSD_ONE_DAY);
		}
		
		$banks = &Msd_Waimaibao_Enum::get('支付银行');
		$PaymentMethods = &Msd_Waimaibao_Enum::get('支付方式');
		$rows = $dao->toDispatch(array(
				'start' => $start,
				'end' => $end
			));		
		foreach ($rows as $row) {
			$DlvManGuid = (string)$row['DlvManGuid'];
			!isset($data[$DlvManGuid]) && $data[$DlvManGuid] = array(
				'orders' => array(),
				'chats' => array()	
				);
			
			$key = $row['CityId'].$row['OrderId'];
			$cp = $cpDao->cacheGetOtherCell($row['CustGuid'], $row['CallPhone']);
				
			$remarks = array();
			trim($row['Remark']) && $remarks[] = trim($row['Remark']);
			trim($row['CommonComment']) && $remarks[] = trim($row['CommonComment']);
			trim($row['RequestRemark']) && $remarks[] = trim($row['RequestRemark']);
			trim($row['SalesAttribute']) && $remarks[] = trim($row['SalesAttribute']);
			foreach($PaymentMethods as $pm) {
				if ($pm['ElementValue']==$row['PaymethodMethod']) {
					$remarks[] = $row['ElementValue'];
				}
			}
			
			if ((int)$row['PaymethodMethod']==1) {
				foreach ($banks as $bk) {
					if ($bk['ElementValue']==$row['PayedVia']) {
						$remarks[] = '已支付: '.$row['PayedMoney'];
					}
				}
			}
				
			$cp!='empty' && $remarks[] = '其他号码:'.$cp;
			$row['Remarks'] = trim(implode(' ', $remarks));
			
			$data[$DlvManGuid]['orders'][$key] = $row;		
		}
		
		$cacher->set($cacheKey, $data, MSD_ONE_DAY);
		
		return $data;
	}
	
	/**
	 * 加载订单及聊天信息
	 * 
	 * @return array
	 */
	public static function &load()
	{
		$config = &Msd_Config::appConfig();
		$cacher = &Msd_Cache_Remote::getInstance();
		$hour = (int)date('H');
		$dao = &Msd_Dao::table('delivery/order');
		$cpDao = &Msd_Dao::table('customer/phone');
		$dmDao = &Msd_Dao::table('deliveryman');
		$cDao = &Msd_Dao::table('chat');

		$now = time();
		if ($hour<4) {
			$start = date('Y-m-d 05:00:00', $now-MSD_ONE_DAY);
			$end = date('Y-m-d 03:00:00', $now);
		} else {
			$start = date('Y-m-d 05:00:00', $now);
			$end = date('Y-m-d 03:00:00', $now+MSD_ONE_DAY);
		}
		
		$banks = &Msd_Waimaibao_Enum::get('支付银行');
		$PaymentMethods = &Msd_Waimaibao_Enum::get('支付方式');
		$rows = $dao->toDispatch(array(
				'start' => $start,
				'end' => $end
				));
		foreach ($rows as $row) {
			$key = $row['CityId'].$row['OrderId'];
			$cp = $cpDao->cacheGetOtherCell($row['CustGuid'], $row['CallPhone']);
			
			$remarks = array();
			trim($row['Remark']) && $remarks[] = trim($row['Remark']);
			trim($row['CommonComment']) && $remarks[] = trim($row['CommonComment']);
			trim($row['RequestRemark']) && $remarks[] = trim($row['RequestRemark']);
			trim($row['SalesAttribute']) && $remarks[] = trim($row['SalesAttribute']);
			foreach($PaymentMethods as $pm) {
				if ($pm['ElementValue']==$row['PaymethodMethod']) {
					$remarks[] = $row['ElementValue'];
				}
			}

			if ((int)$row['PaymethodMethod']==1) {
				foreach ($banks as $bk) {
					if ($bk['ElementValue']==$row['PayedVia']) {
						$remarks[] = '已支付: '.$row['PayedMoney'];
					}
				}
			}
			
			$cp!='empty' && $remarks[] = '其他号码:'.$cp;
			$row['Remarks'] = trim(implode(' ', $remarks));
			$row['Changing'] = $row['ItemStatus'] ? true : false;
			
			$cachedOrder = &$cacher->get($row['CityId'].((string)$row['OrderId']));
			$DlvManId = $row['DlvManId'];
			$manOrders = &$cacher->get(self::dKey().'dmi_'.$DlvManId);
			$manOrders || $manOrders = array();
			
			if ($cachedOrder) {
				$oldManOrders = &$cacher->get(self::dKey().'dmi_'.$cachedOrder['DlvManId']);
				$row = self::compare($cachedOrder, $row, $oldManOrders, $manOrders);	
			} else {
				$row['items'] = &self::loadItems($row['OrderGuid']);
				$row['cz'] = self::CZ_ADD;
				$row['Changed'] = true;
				Msd_Log::getInstance()->dispatcher("New Order Loaded: ".$row['CityId'].$row['OrderId'].", ".$DlvManId.", Items: ".count($row['items']));
			}
			
			$manOrders[$key] = $row;
			$cacher->set($row['CityId'].((string)$row['OrderId']), $row, MSD_ONE_DAY, 1);
			$cacher->set(self::dKey().'dmi_'.$DlvManId, $manOrders, MSD_ONE_DAY, 1);
		}
		
		$cTime = date('Y-m-d H:i:s');
		$chatsStart = $cacher->get(self::dKey().'chats_start');
		$chatsStart || $chatsStart = $start; 
		$chats = $cDao->toDispatch(array(
					'start' => $chatsStart,
					'end' => $end
					));
		foreach ($chats as $chat) {
			$Receiver = $chat['Receiver'];
			$Sender = $chat['Sender'];
			$DlvManId = $Receiver=='dd' ? $Sender : $Receiver;
			
			$manChats = &$cacher->get(self::dKey().'dmc_'.$DlvManId);
			$manChats || $manChats = array();
			$manChats[$chat['ID']] = $chat;
			
			Msd_Log::getInstance()->dispatcher('New Chats Loaded: '.$chat['Message'].', from '.$chat['Sender'].', to '.$chat['Receiver']);
			$cacher->set(self::dKey().'dmc_'.$DlvManId, $manChats, MSD_ONE_DAY);
		}
		
		$cacher->set(self::dKey().'chats_start', $cTime, MSD_ONE_DAY);
		
		return $rows;
	}
	
	/**
	 * 修改订单状态为“已送出”
	 * 
	 * @param array $order
	 * @return boolean
	 */
	public static function sending(&$order)
	{
		$result = false;
		
		$config = &Msd_Config::appConfig();
		$cacher = &Msd_Cache_Remote::getInstance();
		$DlvManId = $order['DlvManId'];
		$data = &$cacher->get(self::dKey().'dmi_'.$DlvManId);
		
		$oDao = &Msd_Dao::table('order');
		$oDao->doUpdate(array(
			'StatusId' => $config->order->status->received	
			), $order['OrderGuid']);
		
		$osDao = &Msd_Dao::table('order/status/log');
		$osDao->insert(array(
			'CityId' => $order['CityId'],
			'OrderGuid' => $order['OrderGuid'],
			'StatusId' => $config->order->status->received	
			));
		
		$oKey = $order['CityId'].$order['OrderId'];
		$data[$oKey]['StatusId'] = $config->order->status->received;
		
		$cacher->set(self::dKey().'dmi_'.$DlvManId, $data, MSD_ONE_DAY);
		$cacher->set(self::dKey().'oi_'.$oKey, 1, MSD_ONE_DAY);
		$result = true;
		
		return $result;
	}
	
	/**
	 * 修改订单状态为“已查看”
	 * 
	 * @param array $order
	 * @return boolean
	 */
	public static function confirm(&$order)
	{
		$result = false;
		
		$now = date('Y-m-d H:i:s');
		$DlvManId = $order['DlvManId'];
		$cacher = &Msd_Cache_Remote::getInstance();
		$data = &$cacher->get(self::dKey().'dmi_'.$DlvManId);
		$dao = &Msd_Dao::table('delivery/order');
		$dao->confirm($order['OrderGuid'], $now);
		
		$oKey = $order['CityId'].$order['OrderId'];
		$data[$oKey]['AcceptTime'] = $now;
		$cacher->set(self::dKey().'dmi_'.$DlvManId, $data);
		
		$cOrder = &$cacher->get($order['CityId'].((string)$order['OrderId']));
		$cOrder['AcceptTime'] = $now;
		$cacher->set($order['CityId'].((string)$order['OrderId']), MSD_ONE_DAY);
		
		$cacher->set(self::dKey().'oi_'.$oKey, 1, MSD_ONE_DAY);
		$result = true;
		
		Msd_Log::getInstance()->dispatcher($order['CityId'].$order['OrderId'].' confirmed');
		
		return $result;
	}
	
	/**
	 * 修改订单状态为“已送达”
	 * 
	 * @param array $order
	 * @return boolean
	 */
	public static function over(&$order)
	{
		$result = false;
		
		$config = &Msd_Config::appConfig();
		$cacher = &Msd_Cache_Remote::getInstance();
		$DlvManId = $order['DlvManId'];
		$data = &$cacher->get(self::dKey().'dmi_'.$DlvManId);
		
		$oDao = &Msd_Dao::table('order');
		$oDao->doUpdate(array(
			'StatusId' => $config->order->status->delivered	
			), $order['OrderGuid']);
		
		$osDao = &Msd_Dao::table('order/status/log');
		$osDao->insert(array(
			'CityId' => $order['CityId'],
			'OrderGuid' => $order['OrderGuid'],
			'StatusId' => $config->order->status->delivered	
			));
		
		$oKey = $order['CityId'].$order['OrderId'];
		$data[$oKey]['StatusId'] = $config->order->status->delivered;
		
		$cacher->set(self::dKey().'dmi_'.$DlvManId, $data, MSD_ONE_DAY, 1);
		$cacher->set(self::dKey().'oi_'.$oKey, 1, MSD_ONE_DAY);
		$result = true;
		
		return $result;
	}
	
	/**
	 * 加载菜品数据
	 * 
	 * @param string $OrderGuid
	 * @return array
	 */
	protected static function &loadItems($OrderGuid)
	{
		$config = &Msd_Config::appConfig();
		$items = &Msd_Dao::table('order/item')->getOrderItems($OrderGuid, array(
			$config->order->status->issued,
			$config->order->status->confirmed,
			$config->order->status->assigned,
			$config->order->status->received,
			$config->order->status->delivered
			));
		
		return $items;
	}
	
	/**
	 * 比较订单变化
	 * 
	 * @param array $old
	 * @param array $new
	 * @param array $oldMan
	 * @param array $newMan
	 * @return array
	 */
	protected static function &compare($old, $new, $oldMan, &$newMan)
	{
		$key = $new['CityId'].$new['OrderId'];
		$new['items'] = &self::loadItems($new['OrderGuid']);
		$cacher = &Msd_Cache_Remote::getInstance();
		$oKey = $new['CityId'].$new['OrderId'];
		$newMan[$key]['items'] = &$new['items'];
		$newMan[$key]['Changing'] = $new['Changing'];
		
		if ($old['VersionId']!=$new['VersionId']) {
			$newMan[$key] = &$new;
			$newMan[$key]['Changed'] = true;
			$newMan[$key]['cz'] = self::CZ_UPDATE;
			Msd_Log::getInstance()->dispatcher($new['CityId'].$new['OrderId'].' VersionId changed: from '.$old['VersionId'].' to '.$new['VersionId']);
		} else if ($old['StatusId']!=$new['StatusId']) {
			$newMan[$key] = &$new;
			$newMan[$key]['Changed'] = (bool)$cacher->set(self::dKey().'oi_'.$oKey) ? false : true;
			$newMan[$key]['cz'] = self::CZ_UPDATE;
			Msd_Log::getInstance()->dispatcher($new['CityId'].$new['OrderId'].' StatusId changed: from '.$old['StatusId'].' to '.$new['StatusId']);
			$cacher->set(self::dKey().'oi_'.$oKey);
		} else if ($old['VendorName']!=$new['VendorName']) {
			$newMan[$key] = &$new;
			$newMan[$key]['Changed'] = true;
			$newMan[$key]['cz'] = self::CZ_UPDATE;
			Msd_Log::getInstance()->dispatcher($new['CityId'].$new['OrderId'].' VendorName changed: from '.$old['VendorName'].' to '.$new['VendorName']);
		} else if ($old['Remarks']!=$new['Remarks']) {
			$newMan[$key] = &$new;
			$newMan[$key]['Changed'] = true;
			$newMan[$key]['cz'] = self::CZ_UPDATE;
			Msd_Log::getInstance()->dispatcher($new['CityId'].$new['OrderId'].' Remarks changed: from '.$old['Remarks'].' to '.$new['Remarks']);
		} else if ($old['DlvManId']!=$new['DlvManId']) {
			$newMan[$key] = &$new;
			$oldMan[$key]['cz'] = self::CZ_DEL;
			$newMan[$key]['cz'] = self::CZ_ADD;
			$oldMan[$key]['Changed'] = true;
			$newMan[$key]['Changed'] = true;
			
			$cacher->set(self::dKey().'dmi_'.$old['DlvManId']);
			Msd_Log::getInstance()->dispatcher($new['CityId'].$new['OrderId'].' DlvManId changed: from '.$old['DlvManId'].' to '.$new['DlvManId']);
		} else if ($old['ItemCount']!=$new['ItemCount'] || ($old['ItemChanged']==0 && $new['ItemChanged']==1) || ($old['Changing']==true && $new['Chaning']==false)) {
			$newMan[$key] = &$new;
			$newMan[$key]['Changed'] = true;
			$newMan[$key]['cz'] = self::CZ_UPDATE;
			
			Msd_Log::getInstance()->dispatcher($new['CityId'].$new['OrderId'].' ItemChanged : from '.$old['ItemChanged'].' to '.$new['ItemChanged']);
		} else {
			$newMan[$key]['cz'] = $new['cz'] = $old['cz'];	
		}

		return $newMan[$key];
	}
}