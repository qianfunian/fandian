<?php

class Api_V12580Controller extends Msd_Controller_Api
{	
	protected $cancelReasions = array();
	
	public function init()
	{
		parent::init();
		
		Zend_Controller_Front::getInstance()->setParam('noViewRenderer', 0);
		
		$cacher = &Msd_Cache_Remote::getInstance();
		$key = 'cancel_reasons';
		$data = $cacher->get($key);
		if (!$data) {
			$rows = &Msd_Dao::table('cancelreasons')->allReasons();
			foreach ($rows as $row) {
				$data[$row['CancelGuid']] = $row;
			}
			
			$cacher->set($key, $data, MSD_ONE_DAY);
		}
		
		$this->cancelReasions = &$data;
	}
	
	/**
	 * 退款接收
	 * 
	 */
	public function refundAction()
	{
		$this->xmlRoot = 'root';
		$this->output = array(
			$this->xmlRoot => array(
				'result' => 1,
				'message' => '未知错误'	
				)
			);
		
		$p = $this->getRequest()->getParams();
		$config = &Msd_Config::appConfig();
		
		$orderId = urldecode(trim($p['order_id']));
		$money = urldecode(trim($p['money']));
		
		if ($orderId=='') {
			$this->error('error.v12580.order.orderid_required');
		}
		
		$d = Msd_Dao::table('partner/ordermap')->getByPartnerId($orderId, $config->db->partner->v12580);
		if (!isset($d['OrderGuid'])) {
			$this->error('error.v12580.order.orderid_invalid');
		}

		$pager = $where = $sort = array();
		$pager['page'] = 1;
		$pager['limit'] = 1;
		
		$where['OrderGuids'] = array(
			$d['OrderGuid']	
			);
		
		$rows = &Msd_Dao::table('order')->search($pager, $where, $sort);
		if (!is_array($rows) || count($rows)<=0) {
			$this->error('error.v12580.order.orderid_invalid');
		} else {
			$rTable = &Msd_Dao::table('partner/orderrefund');
			
			$data = &$rows[0];
			$allowedRefundStatus = explode('|', $config->db->allow_refund_order_status->v12580);
			$StatusId = $data['StatusId'];
			
			if (!in_array($StatusId, $allowedRefundStatus)) {
				$this->output[$this->xmlRoot]['message'] = '当前订单状态不允许退款';
			}
			
			$rTable->insert(array(
				'PartnerOrderid' => $orderId,
				'RefundMoney' => $money,
				'Partner' => $config->db->partner->v12580	
				));
			
			$this->output[$this->xmlRoot]['result'] = 0;
			$this->output[$this->xmlRoot]['message'] = '退款成功';
		}
		
		$this->output();
	}
	
	/**
	 * 订单接收
	 * 
	 */
	public function orderAction()
	{
		$this->xmlRoot = 'root';
		
		$this->needPost();
		
		$p = $this->getRequest()->getParams();
		
		$config = &Msd_Config::appConfig();
		$iTable = &Msd_Dao::table('item');
		
		$orderId = urldecode(trim($p['order_id']));
		$createTime = $p['create_time'];
		$totalMoney = (float)$p['total_money'];
		$paymentStatus = trim($p['payment_status']);
		$address = urldecode(trim($p['address']));
		$expressTime = trim($p['express_time']);
		$contactor = urldecode(trim($p['contactor']));
		$phone = urldecode(trim($p['phone']));
		$remark = urldecode(trim($p['remark']));
		$items = urldecode(trim($p['items']));
		
		$params = array(
			'hash' => sha1(uniqid(mt_rand())),
			'user' => '',
			'details' => array(),
			'partner_data' => array(),
			'sales_attribute' => Msd_Config::cityConfig()->db->sales_attr->v12580
			);
		$itemsToDo = array();
		
		if ($orderId=='') {
			$this->error('error.v12580.order.orderid_required');
		}
		$params['partner_data']['order_id'] = $orderId;
		
		if ($createTime=='') {
			$this->error('error.v12580.order.createtime_required');
		} else {
			try {
				$ct = new DateTime($createTime);
				if ((time()-$ct->getTimestamp())>MSD_ONE_DAY*3) {
					$this->error('error.v12580.order.createtime_too_old');
				}
			} catch (Exception $e) {
				$this->error('error.v12580.order.createtime_not_valid');
			}
		}
		$params['partner_data']['create_time'] = $createTime;
		
		if ($totalMoney<=0) {
			$this->error('error.v12580.order.totalmoney_not_valid');
		}
		$params['partner_data']['total_money'] = $totalMoney;
		
		if ($paymentStatus=='') {
			$this->error('error.v12580.order.paymentstatus_required');
		} else {
			$paymentStatus = (int)$paymentStatus;
		}
		$params['partner_data']['payment_status'] = $paymentStatus;
		$params['details']['PayMethod'] = $config->db->payment->v12580;
		
		if ($address=='') {
			$this->error('error.v12580.order.address_required');
		}
		$params['details']['Address'] = $address;
		$params['details']['CoordGuid'] = '';
		$params['details']['Longitude'] = '';
		$params['details']['Latitude'] = '';
		$params['details']['CoordName'] = '';
		
		if ($expressTime!='') {
			try {
				$et = new DateTime($expressTime);
			} catch(Exception $e) {
				$this->error('error.v12580.order.expresstime_not_valid');
			}
		}
		
		if ($contactor=='') {
			$this->error('error.v12580.order.contactor_required');
		}
		$params['details']['Contactor'] = $contactor;
		
		if ($phone=='') {
			$this->error('error.v12580.order.phone_required');
		} else if (!preg_match('/^([0-9,]+)$/i', $phone)) {
			$this->error('error.v12580.order.phone_not_valid');
		}
		$params['details']['Phone'] = $phone;
		
		$_Order = &Msd_Dao::table('partner/v12580pushlog')->get($orderId);
		if ($_Order['OrderId']) {
			Msd_Dao::table('partner/v12580pushlog')->doDelete($_Order['OrderId']);
		}
		
		if ($items=='') {
			$this->error('error.v12580.order.items_required');
		} else {
			$data = explode(',', $items);
			
			$oitems = array();
			$ItemGuids = array();
			foreach ($data as $d) {
				list($ItemGuid, $count) = explode('|', $d);
				$count = (int)$count;
				
				if (!Msd_Validator::isGuid($ItemGuid)) {
					$this->error('error.v12580.order.items_not_valid');
				} else if ($count<=0) {
					$this->error('error.v12580.order.items_count_not_valid');
				} else {
					$oitems[$ItemGuid] = $count;
				}
			}
			
			$pager = array(
				'page' => 1,
				'limit' => 999,
				'skip' => 0	
				);
			$where = array(
				'Disabled' => '0',
				'ItemGuid' => array_keys($oitems)	
				);
			$sort = array();
			$tmp = &Msd_Dao::table('item')->search($pager, $where, $sort);
			$i2 = array();

			foreach ($tmp as $row) {
				$row['_count_'] = (int)$oitems[$row['ItemGuid']];
				
				if ($row['_count_']>0) {
					if (!isset($i2[$row['VendorGuid']])) {
						$i2[$row['VendorGuid']] = array();
						$i2[$row['VendorGuid']]['VendorName'] = $row['VendorName'];
						$i2[$row['VendorGuid']]['items'] = array();

						$i2[$row['VendorGuid']]['Freight'] = 0;
						$i2[$row['VendorGuid']]['Distance'] = 0;

						$i2[$row['VendorGuid']]['Total'] = $i2[$row['VendorGuid']]['Freight'];
						$i2[$row['VendorGuid']]['Boxes'] = 0;
						$i2[$row['VendorGuid']]['BoxesTotal'] = 0;
					}
					
					$i2[$row['VendorGuid']]['items'][] = $row;
					$i2[$row['VendorGuid']]['itemids'][] = $row['ItemGuid'];
					
					$Prices = $row['_count_']*$row['UnitPrice'];
					$Boxes = $row['_count_']*$row['BoxQty']/($row['MinOrderQty'] ? (int)$row['MinOrderQty'] : 1);
					$BoxesPrice = $Boxes*$row['BoxUnitPrice'];
					
					$i2[$row['VendorGuid']]['Boxes'] += $Boxes;
					$i2[$row['VendorGuid']]['BoxesTotal'] += $BoxesPrice;
					$i2[$row['VendorGuid']]['Total'] += $Boxes+$Prices;
					
					$params['details']['remark'][$row['VendorGuid']] = $remark;
				}
			}
			
			if (count($i2)>1) {
				$this->error('error.v12580.order.only_one_vendor_permitted');
			}
			
			$params['details']['sitems'] = array(
				$config->db->n->service_name->normal => array(
					'items' => &$i2	
					)	
				);
		}

		$result = Msd_Waimaibao_Order::create($params);

		if (is_array($result['OrderGuid']) && count($result['OrderGuid'])>0) {
			foreach ($result['OrderGuid'] as $OrderGuid) {
				Msd_Dao::table('partner/ordermap')->insert(array(
					'OrderGuid' => $OrderGuid,
					'Partner' => $config->db->partner->v12580,
					'PartnerOrderId' => $orderId	
					));
				
				Msd_Dao::table('partner/v12580pushlog')->insert(array(
					'OrderId' => $orderId	
					));
			}
			
			$this->output[$this->xmlRoot] = array(
				'result' => 0	,
				'message' => '下单成功',
				);
		} else {
			$this->output[$this->xmlRoot] = array(
				'result' => 1,
				'message' => '下单异常，请与服务器端管理员联系',	
				);
		}
		
		$this->output();
	}
	
	/**
	 * 菜品列表
	 * 
	 */
	public function itemsAction()
	{
		$this->xmlRoot = 'items';
		$this->pager_init();
		
		$table = &Msd_Dao::table('item');
		
		$where = $sort = array();
		$where['Vendor_Disabled'] = '0';
		
		$sort['ItemGuid'] = 'ASC';
		
		$rows = $table->v12580($this->pager);
		foreach ($rows as $row) {
			$this->output[$this->xmlRoot][] = array(
				'item' => array(
					'vendor_code'=> $row['VendorGuid'],
					'category' => $row['CtgName'],
					'code' => $row['ItemGuid'],
					'name' => $row['ItemName'],
					'price' => $row['UnitPrice'],
					'unit' => $row['UnitName'],
					'description' => $row['Description'],
					'image_url' => $this->view->Itemurl($row, $this->staticUrl.'design/product/nopic.jpg?'.FANDIAN_APP_VER),
					'package' => $row['BoxQty'],
					'package_price' => $row['BoxUnitPrice'],	
					'disabled' => $row['Disabled'] ? '1' : '0'
					),	
				);
		}
		
		$this->output();
	}
	
	/**
	 * 商家列表
	 * 
	 */
	public function vendorsAction()
	{
		$this->xmlRoot = 'vendors';
		$this->pager_init();
		
		$config = &Msd_Config::appConfig();
		$table = &Msd_Dao::table('vendor');
		
		$where = $sort = array();
		
		$where['service'] = $config->db->n->service_name->normal;
		$where['exclude_mini'] = true;
		
		$sort['HotRate'] = 'DESC';

		$rows = $table->search($this->pager, $where, $sort);

		foreach ($rows as $row) {
			$logo = Msd_Waimaibao_Vendor::imageUrl(array(
				'VendorGuid' => $row['VendorGuid']
				));
			
			$this->output[$this->xmlRoot][] = array(
				'vendor' => array(
					'code' => $row['VendorGuid'],
					'name' => $row['VendorName'],
					'category' => $row['CtgName'],
					'address' => $row['Address'],
					'service_time' => $this->view->Vendorservicetime($row['VendorGuid']),
					'logo' => $logo,
					'logo_small' => $logo,
					'intro' => strip_tags($row['Description']),
					'position' => $this->vendorPosition($row['VendorGuid']),
					'average_cost' => (int)$row['AverageCost'],
					'express_range' => 5000,
					'disabled' => $row['Disabled'] ? '1' : '0'
					)	
				);
		}
		
		$this->output();
	}
	
	protected function vendorPosition($VendorGuid)
	{
		$config = &Msd_Config::cityConfig();
		
		$data = array(
			'longitude' => 	$config->longitude,
			'latitude' => $config->latitude
			);
		
		$cacher = &Msd_Cache_Remote::getInstance();
		$key = 'vp12580_'.$VendorGuid;
		$x = $cacher->get($key);
		
		if (!$x) {
			$tmp = Msd_Dao::table('vendor/address')->get($VendorGuid);
			if ($tmp) {
				$data['longitude'] = $tmp['Longitude'];
				$data['latitude'] = $tmp['Latitude'];
			}
		} else {
			$data = $x;
		}
		
		return $data;
	}
	
	protected function checkOrderIdExists($OrderId)
	{
		$d = &Msd_Dao::table('partner/ordermap')->getByPartnerId($OrderId, Msd_Config::appConfig()->db->partner->v12580);
		
		return isset($d['OrderGuid']) ? true : false;
	}
}
