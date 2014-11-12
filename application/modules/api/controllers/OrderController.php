<?php

class Api_OrderController extends Msd_Controller_Api
{
	public function init()
	{
		parent::init();
	}
	
	/**
	 * 根据token获取最后10个订单
	 * 
	 */
	public function last10bytokenAction()
	{
		$this->xmlRoot = 'orders';
		$token = trim($this->getRequest()->getParam('token', ''));
		if (strlen($token)) {
			$t = &$this->t('order_concise');
			$guids = &Msd_Dao::table('order/token')->getOrderGuidsByToken($token);

			foreach ($guids as $OrderGuid) {
				$d = Msd_Waimaibao_Order::detail($OrderGuid);
				$this->output[$this->xmlRoot][] = array(
						'order_concise' => $t->translate($d)
					);				
			}
		}
		
		$this->output();
	}
	
	/**
	 * 新建订单
	 * 
	 */
	public function createAction()
	{
		$this->auth();
		$this->needPost();
		$this->xmlRoot = 'order';
		
		$p = $this->getRequest()->getParams();
		
		$config = &Msd_Config::appConfig();
		$iTable = &Msd_Dao::table('item');
		
		$address = urldecode(trim($p['address']));
		$expressTime = trim($p['request_time']);
		$contactor = urldecode(trim($p['contactor']));
		$phone = urldecode(trim($p['cellphone']));
		$remark = urldecode(trim($p['note']));
		$items = urldecode(trim($p['products']));
		$placemark = urldecode(trim($p['placemark']));
		$token = urldecode(trim($p['token']));
		$VendorGuid = urldecode(trim($p['id']));
		
		if ($items=='') {
			$this->error('error.order.items_required');
		} else if ($address=='') {
			$this->error('error.order.address_required');
		} else if ($contactor=='') {
			$this->error('error.order.contactor_required');
		} else if ($phone=='') {
			$this->error('error.order.phone_required');
		}
		
		$ci = $this->clientInfo();

		$params = array();
		$params['web_name'] = $ci['os'];
		$params['details']['PayMethod'] = '';
		$params['details']['Address'] = $address;
		$params['details']['CoordGuid'] = Msd_Validator::isGuid($placemark) ? $placemark : '';
		$params['details']['Longitude'] = '';
		$params['details']['Latitude'] = '';
		$params['details']['CoordName'] = '';
		$params['details']['Contactor'] = $contactor;
		$params['details']['Phone'] = $phone;
		
		$expressSetting = 0;
		$express = array();
		
		if (strlen($expressTime)) {
			try {
				$edt = new DateTime($expressTime);
				$express = array(
					'year' => date('Y', $edt->getTimestamp()),
					'month' => date('m', $edt->getTimestamp()),
					'day' => date('d', $edt->getTimestamp()),
					'hour' => date('H', $edt->getTimestamp()),
					'minute' => date('i', $edt->getTimestamp())
					);
				$expressSetting = 1;
			} catch (Exception $e) {
				
			}
		}

		$data = explode(',', $items);
		
		$oitems = array();
		$ItemGuids = array();
		foreach ($data as $d) {
			list($ItemGuid, $count) = explode('|', $d);
			$count = (int)$count;
			
			if (!Msd_Validator::isGuid($ItemGuid)) {
				$this->error('error.order.items_not_valid');
			} else if ($count<=0) {
				$this->error('error.order.items_count_not_valid');
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
			'ItemGuid' => array_keys($oitems),
			'ServiceName' => $config->db->n->service_name->normal
			);
		$sort = array();
		$tmp = &Msd_Dao::table('item')->search($pager, $where, $sort);
		$i2 = array();

		foreach ($tmp as $row) {
			$row['_count_'] = (int)$oitems[(string)$row['ItemGuid']];
			
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
				
				$params['details']['remark'][(string)$row['VendorGuid']] = $remark;
			}
		}
		
		if (count($i2)>1) {
			$this->error('error.order.only_one_vendor_permitted');
		}
		
		$params['details']['sitems'] = array(
			$config->db->n->service_name->normal => array(
					'items' => &$i2,
					'express' => &$express,
					'express_setting' => &$expressSetting
					)
			);
		$params['hash'] = sha1(uniqid(mt_rand()));

		$result = Msd_Waimaibao_Order::create($params);

		if (is_array($result['OrderGuid']) && count($result['OrderGuid'])>0) {
			$d = &Msd_Waimaibao_Order::detail($result['OrderGuid'][0]);
			$this->output[$this->xmlRoot] = &$this->t('order')->translate($d);
			
			if ($token) {
				Msd_Service_Apple_Apns::bindOrderToken($result['OrderGuid'][0], $token);
			}
		} else {
			$this->error('error.order.fatal_error');
		}
		
		$this->output();
	}
	
	/**
	 * 显示订单详情
	 * 
	 */
	public function showAction()
	{
		$this->auth();
		$this->xmlRoot = 'order';
		
		$OrderGuid = trim($this->getRequest()->getParam('order_id', ''));
		
		if (!Msd_Validator::isGuid($OrderGuid)) {
			$this->error('error.general.parameter_invalid');
		}
		
		$d = &Msd_Waimaibao_Order::detail($OrderGuid);
		
		if (!$d) {
			$this->error('error.general.parameter_invalid');	
		}
		
		if ($this->uid && $d['customer']['CustGuid'] && $d['customer']['CustGudi']!=$this->uid) {
		//	$this->error('error.general.forbidden');
		}
		
		$config = &Msd_Config::appConfig();
		$vendor = &Msd_Waimaibao_Vendor::Detail($d['vendor']['VendorGuid']);
		$d['vendor'] = array(
			'VendorGuid' => $vendor['basic']['VendorGuid'],
			'VendorName' => $vendor['basic']['VendorName'],
			'CtgName' => $vendor['groups'][$config->db->n->ctg_std_name->vendor],
			'Remark' => $vendor['basic']['Remark'],
			'Longitude' => $vendor['address']['Longitude'],
			'Latitude' => $vendor['address']['Latitude'],
			'Address' => $vendor['address']['Address'],
			'ServiceTimeString' => Msd_Dao::table('vendor')->getServiceTimeString($vendor['basic']['VendorGuid'])
			);
		
		$this->output[$this->xmlRoot] = Msd_Api_Translator::getInstance()->t('order')->translate($d);
		
		$this->output();
	}
}