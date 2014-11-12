<?php

class Msd_Waimaibao_Order extends Msd_Waimaibao_Base
{
	protected static $status = array();

	/**
	 * 获取OrderGuid到Hash的对应关系
	 * 
	 * @param unknown_type $OrderGuid
	 */
	public static function OHash($OrderGuid)
	{
		$cacher = &Msd_Cache_Remote::getInstance();
		$key = 'OHash_'.md5($OrderGuid);
		$Hash = $cacher->get($key);
		
		if (!$Hash) {
			$d = Msd_Dao::table('order/hash')->Order2Hash($OrderGuid);
			if ($d['Hash']) {
				$rows = Msd_Dao::table('order/hash')->getHashOrders($d['Hash']);
				foreach ($rows as $row) {
					$key = 'OHash_'.md5($row['OrderGuid']);
					$cacher->set($key, $row['Hash']);
				}
				
				$Hash = $d['Hash'];
			}
		}
		
		return $Hash;
	}
	
	/**
	 * 获得配置中的订单状态
	 * 
	 */
	public static function &getStatus()
	{
		if (count(self::$status)==0) {
			$config = &Msd_Config::appConfig()->order->status->toArray();
			foreach ($config as $key=>$val) {
				self::$status[$key] = $val;
			}
		}
		
		return self::$status;
	}
	
	/**
	 * 解析Cookie中记录的网单菜品/数量等数据
	 * 
	 * @param string $cookie_key
	 */
	public static function &parseCookieItems($cookie_key='items', $ServiceName='普通')
	{
		$cookie_key || $cookie_key = 'items';
		$result = $pager = $params = $items = array();
		$tmp = explode(',', trim(urldecode($_COOKIE[$cookie_key])));
		$data = array();
		
		foreach ($tmp as $item) {
			list($ItemGuid, $count) = explode('|', $item);
			$count = (int)$count;
			if ($count>0) {
				$data[$ItemGuid] = $count;	
			}
		}

		$pager['page'] = 1;
		$pager['limit'] = 9999;
		$pager['offset'] = 0;
		
		$params['passby_pager'] = 1;
		$params['ItemGuid'] = $data ? array_keys($data) : false;
		$params['ServiceName'] = $ServiceName;

		if ($data) {
			$rows = &Msd_Dao::table('item')->search($pager, $params);

			foreach ($rows as $row) {
				$ItemGuid = (string)$row['ItemGuid'];
				$VendorGuid = (string)$row['VendorGuid'];
				$row['_count_'] = $data[$ItemGuid];
		
				if ($row['_count_']>0) {
					if (!isset($items[$VendorGuid])) {
						$items[$VendorGuid] = array();
						$items[$VendorGuid]['VendorName'] = $row['VendorName'];
						$items[$VendorGuid]['items'] = array();
		
						if ($_COOKIE['coord_guid']) {
							$_r = Msd_Waimaibao_Freight::calculate($_COOKIE['coord_guid'], $row['VendorGuid'], null, $ServiceName);
		
							$items[$VendorGuid]['Freight'] = $_r['freight'];
							$items[$VendorGuid]['Distance'] = (int)$_r['distance'];
						} else {
							$items[$VendorGuid]['Freight'] = 0;
							$items[$VendorGuid]['Distance'] = 0;
						}
		
						$items[$VendorGuid]['Total'] = $items[$VendorGuid]['Freight'];
						$items[$VendorGuid]['Boxes'] = 0;
						$items[$VendorGuid]['BoxesTotal'] = 0;
					}
						
					$items[$VendorGuid]['items'][] = $row;
					$items[$VendorGuid]['itemids'][] = $ItemGuid;
						
					$Prices = $row['_count_']*$row['UnitPrice'];
					$Boxes = $row['_count_']*$row['BoxQty']/($row['MinOrderQty'] ? (int)$row['MinOrderQty'] : 1);
					$BoxesPrice = $Boxes*$row['BoxUnitPrice'];
						
					$items[$VendorGuid]['Boxes'] += $Boxes;
					$items[$VendorGuid]['BoxesTotal'] += $BoxesPrice;
					$items[$VendorGuid]['Total'] += $Boxes+$Prices;
				}
			}
		}

		return $items;		
	}
	
	/**
	 *	计算订单运费
	 *
	 */
	public static function calExpressCost($distance)
	{
	}
	
	/**
	 *	生成新的订单号
	 *
	 */
	public static function newOrderid($CityId='wx')
	{
		if (Msd_Config::cityConfig()->wcf->enabled) {
			$wcf = new Msd_Service_Wcf_Numbersequence();
			$id = $wcf->OrderId();
		} else {
			$id = Msd_Dao::table('order')->newOrderId($CityId);
		}
		
		return $id;
	}
	
	/**
	 *	获取订单详情
	 *
	 */
	public static function &detail($OrderGuid)
	{
		$data = array(
				'order' => array(),
				'sales' => array(),
				'customer' => array(),
				'vendor' => array(),
				'vendor_address' => array(),
				'items' => array(),
				'deliveryman' => array(),
				'coord' => array(),
				'hash' => array(),
				'oslog'=> array()
				);

		if (Msd_Validator::isGuid($OrderGuid)) {
			$cacheKey = 'OrderDetail_'.$OrderGuid;
			$cacher = &Msd_Cache_Remote::getInstance();
			$data = $cacher->get($cacheKey);
			$data = '';

			if (!$data) {
				//	订单主表
				$Order = &Msd_Dao::table('order')->get($OrderGuid);

				if ($Order['OrderGuid']) {
					$data['order'] = &$Order;

					//	销售版本
					$data['sales'] = &Msd_Dao::table('sales')->get($Order['SalesGuid']);

					//	用户资料
					$data['customer'] = &Msd_Dao::table('customer')->get($data['sales']['CustGuid']);

					//	商家资料
					$data['vendor'] = &Msd_Dao::table('vendor')->get($data['order']['VendorGuid']);
					$data['vendor_address'] = &Msd_Dao::table('vendor/address')->get($data['vendor']['VendorGuid']);
					
					//	菜品信息
					$data['items'] = &Msd_Dao::table('order/item')->getOrderItems($Order['OrderGuid']);

					//	地标信息
					if ($data['sales']['CoordGuid']) {
						$data['coord'] = &Msd_Dao::table('coordinate')->get($data['sales']['CoordGuid']);
					} else { 
						$data['coord'] = array();
					}
					
					//	Hash
					$data['hash'] = &Msd_Dao::table('order/hash')->Order2Hash($OrderGuid);
					
					// OrderStatusLog
					$data['oslog'] = &Msd_Dao::table('order/status/log')->getOrderStatusLogs($OrderGuid);
										
					if (Msd_Waimaibao_Order_Status::isDelivered($data['order']['StatusId'])) {
						$cacher->set($cacheKey, $data);
					}
				}
			}
		}

		return $data;
	}
	
	/**
	 * 某个用户是否曾经下单过
	 * 
	 * @param string $CustGuid
	 */
	public static function CustHasOrder($CustGuid)
	{
		$cacher = &Msd_Cache_Remote::getInstance();
		$key = 'inc_'.$CustGuid;
		$result = (int)$cacher->get($key);
		
		if (!$result) {
			$tmp = Msd_Dao::table('order')->CustHasOrder($CustGuid);
			if ($tmp['OrderGuid']) {
				$result = 1;
				$cacher->set($key, $result);
			}
		}
		
		return $result;
	}
	
	/**
	 *	生成新订单
	 *
	 */
	public static function create(array $params)
	{
		//	地标
		//	商家
		//	菜品
		//	用户
		//	运费
		//	销售
		//	订单主表
		//	订单状态日志
		//	Hash
		$result = array();
		$cacher = &Msd_Cache_Remote::getInstance();
		
		self::getStatus();
		$config = &Msd_Config::appConfig();
		$cityConfig = &Msd_Config::cityConfig();				

		$sgCache = &Msd_Cache_Loader::ServiceGroup();
		$sCache = &Msd_Cache_Loader::Services();

		switch ($params['source']) {
			case '12580':
				$defaultName = $config->db->status->name->v12580;
				break;
			default:
				switch(strtolower($params['web_name'])) {
					case 'ios':
						$defaultName = $config->db->status->name->iphone;
						break;
					case 'android':
						$defaultName = $config->db->status->name->android;
						break;
					default:
						$defaultName = $config->db->status->name->default;
						break;
				}
				break;
		}

		$user = $params['user'];
		$details = $params['details'];
		$hash = $params['hash'];
		$partner_data = isset($details['partner_data']) ? $details['partner_data'] : array();
		$SalesSource = isset($params['source']) ? $params['source'] : $config->order->web_source;
		$SalesAttribute = isset($details['sales_attribute']) ? $details['sales_attribute'] : '';
		$SrvGrpGuid = isset($params['SrvGrpGuid']) ? $params['SrvGrpGuid'] : $cityConfig->db->guids->service_group;
		$ServiceGuid = isset($params['ServiceGuid']) ? $params['ServiceGuid'] : $cityConfig->db->guids->service;
		$ServiceName = isset($params['ServiceName']) ? $params['ServiceName'] : $config->db->n->service_name->normal;
		
		$FirstItemName = '';
		$CityId = $params['city_id'] ? $params['city_id'] : $cityConfig->city_id;
		$CityGuid = $params['city_guid'] ? $params['city_guid'] : $cityConfig->db->guids->city;
		$AreaGuid = $params['area_guid'] ? $params['area_guid'] : array_pop($cityConfig->db->guids->area->toArray());
		
		$oTable = &Msd_Dao::table('order');
		$oiTable = &Msd_Dao::table('order/item');
		$sTable = &Msd_Dao::table('sales');
		$vTable = &Msd_Dao::table('vendor');
		$vaTable = &Msd_Dao::table('vendor/address');
		$cpTable = &Msd_Dao::table('customer/phone');
		$caTable = &Msd_Dao::table('customer/address');
		$coTable = &Msd_Dao::table('coordinate');
		$cTable = &Msd_Dao::table('customer');
		$hTable = &Msd_Dao::table('order/hash');
		$opTable = &Msd_Dao::table('order/payment');
		$oslTable = &Msd_Dao::table('order/status/log');

		try {
			$ts = &$oTable->transaction();
			$ts->start();
			$PayMethod = (int)$details['PayMethod'];

			$Distance = 0;
			$Address = $details['Address'];
			$CoordGuid = $details['CoordGuid'];
			$Longitude = 0;
			$Latitude = 0;
			$CoordName = '';
			$StatusId = '';
			$PhoneGuid = '';

			if (Msd_Validator::isGuid($CoordGuid)) {
				$d = $coTable->cget($CoordGuid);
				if ($d['Longitude']) {
					$Longitude = $d['Longitude'];
					$Latitude  = $d['Latitude'];
					$CoordName = $d['CoordName'];
				}
			}
			
			$Category = '';
			$phone = trim($details['Phone']);
			if (!Msd_Validator::isCell($phone)) {
				return false;
				exit;
			}
			
			$IsNewCust = 1;
			
			$cellInfo = $cpTable->OrderCellCheck($phone);
			$userGuid = Msd_Validator::isGuid(trim($cellInfo['CustGuid'])) ? $cellInfo['CustGuid'] : 0;
				
			if ($userGuid) {
				$CustGuid  = $userGuid;
				$PhoneGuid = $cellInfo['PhoneGuid'];
			}else
			{
				$CustGuid = $cTable->genGuid();
				$cTable->insert(array(
						'CustGuid' => $CustGuid,
						'CustName' => $details['Contactor'],
						'Company' => '',
						'Mail' => '',
						'Remark' => '',
						//'Disabled' => 0,
						'AddUser' => $defaultName,
				));
			}
			
			
			if (Msd_Validator::isGuid($CustGuid)) {
				$__row = $cTable->get($CustGuid);
				if ($__row['CtgGroupGuid']==$cityConfig->db->guids->customer->vip) {
					$Category = '一级';
				}
			}
			$IsNewCust = (int)!(bool)self::CustHasOrder($CustGuid);
			
			if (!$PhoneGuid) {
				$PhoneGuid = $cpTable->genGuid();
				$cpTable->insertCell(array(
						'PhoneGuid' => $PhoneGuid,
						'CustGuid' => $CustGuid,
						'PhoneNumber' => $phone,
						'Remark' => '',
						'AddUser' => $defaultName
				));
			}
				
			$CallPhone = $details['Phone'];
			$CustName = $details['Contactor'];
			
			$AddressGuid = $caTable->addressExists($Address, $CustGuid);
			if (!$AddressGuid) {
				$AddressGuid = $caTable->genGuid();

				$caTable->insert(array(
					'AddressGuid' => $AddressGuid,
					'CustGuid' => $CustGuid,
					'CustAddress' => $Address,
					'AddUser' => $defaultName,
					'CoordGuid' => $CoordGuid,
					'Longitude' => $Longitude,
					'Latitude' => $Latitude,
					'CityId' => $CityId,
					'CityGuid' => $CityGuid
					));
			} 
			$CustAddress = $Address;
			
			$SalesGuid = $sTable->genGuid();
			$SalesInserted = false;

			foreach ($details['sitems'] as $sName=>$sitems) {
				$_this_express_setting = $sitems['express_setting'];
				$_this_express = $sitems['express'];
				
				foreach ($sitems['items'] as $VendorGuid=>$items) {
					if (Msd_Validator::isGuid($CoordGuid)) {
						$_tmp = Msd_Waimaibao_Freight::calculate($CoordGuid, $VendorGuid, null, $ServiceName);
						$Freight = (int)$_tmp['freight'];
					} else {
						$Freight = (int)$items['freight'];
					}
					
					$Distance = $items['Distance'];
					
					$box_nums = $total = $freight = $boxes = 0;
					$OrderGuid = $oTable->genGuid();
					
					$Vendor = $vTable->get($VendorGuid);
					$VendorAddress = $vaTable->get($VendorGuid);
					$idx = 0;
					$OrderItemCount = 0;

					$RequestRemark = $details['remark'][(string)$VendorGuid];
					foreach ($items['items'] as $item) {
						$OrdItemGuid = $oiTable->genGuid();
						
						if ($FirstItemName=='' && $item['ItemName']!='米饭') {
							$FirstItemName = $item['ItemName'];
						}
						
						$ItemGuid        = $item['ItemGuid'];
						$count           = (int)$item['_count_'];
						$UnitPrice       = $item['UnitPrice'];
						$OrderItemCount += $count;
						
						$BoxQty     = $item['BoxQty'];
						$BoxAmount  = $item['BoxUnitPrice']*$count*$BoxQty;
						$ItemAmount = $count*$item['UnitPrice'];
						
						$box_nums += $BoxQty*$count;
						$boxes    += $BoxAmount;
						
						$total    += $ItemAmount;
						
						$params = array(
							'OrdItemGuid' => $OrdItemGuid,
							'OrderGuid'   => $OrderGuid,
							'LineIndex'   => $idx+1,
							'ItemGuid'    => $ItemGuid,
							'ItemId'      => $item['ItemId'],
							'ItemName'    => $item['ItemName'],
							'SetMealType' => 0,
							'ItemPrice'   => $item['UnitPrice'],
							'ItemQty'     => $count,
							'MinOrderQty' => $item['MinOrderQty'],
							'ItemUnit'    => $item['UnitName'],
							'ItemAmount'  => $ItemAmount,
							'BoxQty'      => $item['BoxQty']*$count,
							'BoxRatioQty' => $item['BoxQty'],
							'ItemRatioQty'=> $count,
							'BoxPrice'    => $item['BoxUnitPrice'],
							'BoxAmount'   => $BoxAmount,
							'TotalAmount' => $BoxAmount+$ItemAmount,
							'ItemReq'     => '',
							'Remark'      => '',
							'ItemPriceOrigin' => $item['UnitPrice'],
							'ItemPriceLastModified' => $item['UnitPrice'],
							'ItemQtyLastModified'   => $count,
							'AddUser' => $defaultName,
							'CityId'  => $CityId
							);
						$oiTable->insert($params);
						
						$idx++;
					}
		
					//	Sales
					if (!$SalesInserted) {
						//	SalesVersion
						if ($details['express_setting']) {
							//	预订
							$ReqDate = $details['express']['year'].'-'. $details['express']['month'].'-'. $details['express']['day'];
							$_temp = new DateTime($ReqDate.' 10:00:00');
							$ReqDate = date('Y-m-d', $_temp->getTimestamp());
							$ReqTimeStart =  $details['express']['hour'].':'. $details['express']['minute'].':00.000';
							$TimeDirection = 3;
						} else {
							//	尽快
							$ReqDate = date('Y-m-d');
							$ReqTimeStart = '';
							$TimeDirection = 0;
						}

						$sd = array(
							'SalesGuid' => $SalesGuid,
							'SalesSource' => $SalesSource,
							'VersionId' => 0,
							'IsNewCust' => $IsNewCust,
							'CustGuid' => $CustGuid,
							'PhoneGuid' => $PhoneGuid,
							'CallPhone' => $CallPhone,
							'RequestRemark' => $RequestRemark,
							'ReqDate' => $ReqDate,
							'VersionId' => 0,
							'CustName' => $CustName,
							'AddressGuid' => $AddressGuid,
							'CustAddress' => $CustAddress,
							'CoordGuid' => $CoordGuid,
							'CoordName' => $CoordName,
							'Longitude' => $Longitude,
							'Latitude' => $Latitude,
							'SrvGrpGuid' => $SrvGrpGuid,
							'ServiceGuid' => $ServiceGuid,
							'SrvGrpName' => $sgCache[$SrvGrpGuid]['SrvGrpName'],
							'ServiceName' => $sName,
							'CityId' => $CityId,
							'CityGuid' => $CityGuid,
							'AreaGuid' => $AreaGuid,
							'Category' => $Category,
							'Paid' => 0,
							'Invoice' => 0,
							'AddUser' => $defaultName
							);
						
						if (Msd_Validator::isGuid($SalesAttribute)) {
							$attr = Msd_Dao::table('sales/attribute')->cget($SalesAttribute);
							$sd['SalesAttribute'] = $attr['AttributeName'];
						}
						
						$sTable->insert($sd);
						
						$SalesInserted = true;
					}
		
					//	Order
					$OrderId = self::newOrderid($CityId);
					$od = array(
						'OrderGuid' => $OrderGuid,
						'OrderId' => $OrderId,
						'SalesGuid' => $SalesGuid,
						'VersionId' => 0,
						'StatusId' => self::$status['posted'],
						'TotalAmount' => $total+$boxes+$Freight,
						'PaymentMethod' => $PayMethod,
						'TransportMethod' => 0,
						'Distance' => $Distance,
						'FreightOrigin' => $Freight,
						'Freight' => $Freight,
						'ReqTimeStart' => $ReqTimeStart,
						'TimeDirection' => $TimeDirection,
						'CityId' => $CityId,
						'VendorGuid' => $VendorGuid,
						'VendorName' => $Vendor['VendorName'],
						'VendorId' => $Vendor['VendorId'],
						'VendorLongitude' => $VendorAddress['Longitude'],
						'VendorLatitude' => $VendorAddress['Latitude'],
						'ItemCount' => $OrderItemCount,
						'ItemAmount' => $total,
						'BoxQty' => $box_nums,
						'BoxAmount' => $boxes,
						'SumAmount' => $total+$boxes,
						'Remark' => $RequestRemark,
						'AddUser' => $defaultName
						);
					$oTable->insert($od);
					
					//	OrderPayment
					$PaymentGuid = $opTable->genGuid();
					$opTable->insert(array(
						'PaymentGuid' => $PaymentGuid,
						'OrderGuid' => $OrderGuid,
						'Hash' => $hash,
						'BankApi' => '',
						'BankId' => '',
						'PaidMoney' => 0,
						'CallbackSign' => ''
						));
		
					//	OrderStatusLog
					$StatusLogGuid = $oslTable->genGuid();
					$oslTable->insert(array(
							'StatusLogGuid' => $StatusLogGuid,
							'OrderGuid' => $OrderGuid,
							'StatusId' => self::$status['posted'],
							'CityId' => $CityId
							));
					
					$result['OrderGuid'][] = $OrderGuid;
					
					$ckey = 'OHash_'.md5($OrderGuid);
					$cacher->set($ckey, $hash);
					
					$hTable->insert(array(
						'Hash' => $hash,
						'OrderGuid' => $OrderGuid,
						'PayMethod' => $PayMethod,
						'Payed' => 0,
						'BankApi' => '',
						'BankId' => '',
						'CityId' => $CityId
						));

					$HookParams = array(
						'OrderGuid' => $OrderGuid,
						'OrderId' => $OrderId,
						'CoordGuid' => $CoordGuid,
						'CoordName' => $CoordName,
						'CustAddress' => $CustAddress,
						'CustName' => $CustName,
						'VendorName' => $Vendor['VendorName'],
						'VendorGuid' => $Vendor['VendorGuid'],
						'FirstItemName' => $FirstItemName,
						'Hash' => $hash,
						'SalesAttribute' => $SalesAttribute,
						'PartnerData' => $partner_data,
						'SalesGuid' => $SalesGuid,
						'CityId' => $CityId
						);
					Msd_Hook::run('NewOrderCreated', $HookParams);
				}
			}

			$ts->commit();
		} catch (Exception $e) {
			try {
				$ts->rollback();
			} catch (Exception $e1) {}
			
			Msd_Log::getInstance()->order($e->getMessage()."\n".$e->getTraceAsString());
		}

		return $result;
	}
	
	/**
	 * 是否可以进行网上支付了
	 * 
	 * @param string $StatusId
	 * @param integer $PayMethod
	 */
	public static function opReady($StatusId, $PayMethod)
	{
		return ($StatusId=='Confirmed' && $PayMethod=='1') ? true : false;
	}

	public static function isCanceled($StatusId)
	{
		return preg_match('/^canceled/i', $StatusId);
	}
}
