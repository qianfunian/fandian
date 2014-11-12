<?php

/**
 * 下单相关
 * 
 * @author pang
 *
 */

class OrderController extends Msd_Controller_Default
{
	protected $ServiceName = '普通';
	protected $cPrefix = '';
	protected $Services = array(
		'普通' => '',
		'夜宵' => 'y_',
		'下午茶' => 'x_',
		'生日卡' => 'gift_',
		'年夜饭' => 'new_year_'
		);
	
	public function init()
	{
		parent::init();
		
		$config = &Msd_Config::appConfig();
		$this->ServiceName = trim(urldecode($this->getRequest()->getParam('service', $config->db->n->service_name->normal)));
		switch ($this->ServiceName) {
			case $config->db->n->service_name->night:
			case $config->db->n->service_name->noon:	
			case '夜宵':
			case '下午茶':		
			case '生日卡':
			case '年夜饭':		
				break;
			default:
				$this->ServiceName = $config->db->n->service_name->normal;
				break;
		}
		
		$this->view->ServiceName = $this->ServiceName;
		$this->view->cPrefix = $this->cPrefix = $this->Services[$this->ServiceName];
	}
	
	public function historyAction()
	{
		if ($this->member->uid()) {
			$this->_helper->getHelper('Redirector')->gotoUrl($this->baseUrl.'member/order');
			exit(0);
		}
		
		$OrderGuids = array();
		$ogs = explode(',', trim(urldecode($this->sess->get('oids'))));
		foreach ($ogs as $guid) {
			if (Msd_Validator::isGuid($guid)) {
				$OrderGuids[] = $guid;
			}
		}
		
		$this->pager = array(
			'page' => 1,
			'limit' => 10,
			'skip' => 0	
			);
		$table = &Msd_Dao::table('order');
			
		$params = $sort = array();
		$params['OrderGuids'] = $OrderGuids;
		$params['guest'] = true;

		$rows = $table->search($this->pager, $params, $sort);
		$oids = array();
		foreach ($rows as $row) {
			$oids[] = $row['OrderGuid'];
		}
		if ($oids!=$ogs) {
			$this->sess->set('oids', implode(',', $oids));
		}
		
		$this->view->rows = $rows;
		$this->view->page_links = $this->page_links();
		$this->view->request = $_REQUEST;		
	}
	
	/**
	 * 执行网上支付重定向
	 * 
	 * @throws Msd_Exception
	 */
	public function doonlinepayAction()
	{
		$bank_api = $this->getRequest()->getParam('bank_api');
		$bank_no = $this->getRequest()->getParam('bankno', '0');
		$hash = $this->getRequest()->getParam('hash', '');
		$config = &Msd_Config::appConfig();
		$cConfig = &Msd_Config::cityConfig();
		
		$hashOrders = Msd_Dao::table('order/hash')->getHashOrders($hash);
		$OrderGuids = array();
		foreach ($hashOrders as $row) {
			$OrderGuids[] = $row['OrderGuid'];
		}
		
		$this->view->callback_base = $this->scriptUrl;

		switch ($bank_api) {
			case 'cmb':
				$bank = $cConfig->onlinepay->bankcmb->enum_value;;
				$this->view->bank_no = $bank_no;
				$form_action = $cConfig->onlinepay->bankcmb->redirect_url;
				break;
			default:
				$bank = $cConfig->onlinepay->bankcomm->enum_value;
				$this->view->bank_no = $bank_no;
				$form_action = $cConfig->onlinepay->bankcomm->redirect_url;
				break;
		}

		$this->view->form_action = $form_action;
		$this->view->total = 0;
		$this->view->dps = array();
		$this->view->bids = array();

		$this->view->total = 0;
		$this->view->bids = array();
		$this->view->order_time = '';
		$this->view->order_date = '';
		$this->view->order_id;
		foreach ($OrderGuids as $OrderGuid) {
			$_detail = &Msd_Waimaibao_Order::detail($OrderGuid);
			
			if ($_detail['order']['StatusId']==$config->order->status->confirmed) {
				$this->view->bids[] = $OrderGuid;
				$this->view->total += $_detail['order']['TotalAmount'];
			}
			
			$addtime = $_detail['order']['AddTime'];
			$dt = new DateTime($addtime);
			$addtime = date('Y-m-d H:i:s', $dt->getTimestamp());
		
			$this->view->order_time = substr($addtime, 11, 8);
			$this->view->order_date = substr($addtime, 0, 10);
			$this->view->order_id = $_detail['order']['OrderId'];
		}

		if ($this->view->total>0) {
			Msd_Dao::table('order/hash')->doUpdate(array(
				'BankApi' => $bank,
				'BankId' => $this->view->bank_no	
				), $hash);
		} else {
			throw new Msd_Exception('对不起，支付失败，请稍后<a href="javascript:window.location.reload();">重试</a>，如果您还是无法正常完成支付，请拨打400-114-7777进行电话点餐<br /><br />给您带来的不便深表歉意');
		}
		
		echo $this->view->render('order/doonlinepay_'.Msd_Service_Pay::bankEnum2String($bank).'.phtml');
		exit(0);
	}
	
	public function orderhashAction()
	{
		$hash = trim(urldecode($this->getRequest()->getParam('hash', '')));
		
		$this->_helper->getHelper('Redirector')->gotoUrl($this->scriptUrl.'order/hash?hash='.$hash);
		exit(0);
	}
	
	/**
	 * 网上下单后的一个订单汇总显示
	 * 
	 * @throws Msd_Exception
	 */
	public function hashAction()
	{
		//	调查
		$config = &Msd_Config::appConfig();
// 		$votes = array();
// 		$data = (array)Msd_Votes::getModuleVotes('订餐');
// 		foreach ($data as $row) {
// 			$votes[] = Msd_Cache_Loader::Vote($row['AutoId']);
// 		}
		
// 		$this->view->votes = $votes;
				
		$this->view->errorRedirectTimer = 300;
		
		$hash = trim(urldecode($this->getRequest()->getParam('hash', '')));
		$op_ready = 0;
		$valid = false;
		$d = &Msd_Dao::table('order/hash')->getHashOrders($hash);
		$hashData = array(
			'PayedMoney' => 0,
			'Payed' => 0	
			);

		$hasValidOrderStatus = false;
		$ServiceName = $config->db->n->service_name->normal;

		if (is_array($d) && count($d)>0) {
			$valid = true;
			$Orders = $OrderGuids = array();
			
			$config = &Msd_Config::appConfig();
			$firstVendor = '';
			$op_ready_status = array(
				$config->order->status->confirmed
				);
			foreach ($d as $_row) {
				$OrderGuid = $_row['OrderGuid'];
				$OrderGuids[] = $OrderGuid;
				
				$_detail = &Msd_Waimaibao_Order::detail($OrderGuid);
				$coordName = $_detail['sales']['CoordName'];

				if (Msd_Waimaibao_Order::isCanceled($_detail['order']['StatusId'])) {
					$op_ready ++;
				} else if ($_detail['order']['PaymentMethod'] && in_array($_detail['order']['StatusId'], $op_ready_status)) {
					$op_ready ++;
					$hasValidOrderStatus = true;
				}
				
				if (!$firstVendor) {
					$firstVendor = $_detail['vendor']['VendorGuid'];
				}
				
				$Orders[$OrderGuid] = &$_detail;

				$hashData['Payed'] = $_row['Payed'];
				$hashData['PayedMoney'] = $hashData['PayedMoney']+$_row['PayedMoney'];
				
				$ServiceName = $_detail['sales']['ServiceName'];
			}
			
			$this->view->coordName = $coordName;
			$this->view->op_config = Msd_Config::cityConfig()->onlinepay->toArray();
			$this->view->op_ready = ($hasValidOrderStatus && $valid && $op_ready==count($d)) ? '1' : '0';
			$this->view->hash = $hash;
			$this->view->data = $Orders;
			$this->view->user = $this->member->uid() ? $this->member->extend() : array();		
			$this->view->nbids = implode(',', $OrderGuids);
			$this->view->hashData = $hashData;
			$this->view->firstVendor = $firstVendor;
			$this->view->hasValidOrderStatus = $hasValidOrderStatus;
			$this->view->ServiceName = $ServiceName;
		} else {
			throw new Msd_Exception('参数错误');
		}
	}
	
	public function rhashAction()
	{
		$hash = $this->sess->get('hash');

		$value = $this->_request->getParam('value','0');
		if ($value==0) {
			$this->_helper->getHelper('Redirector')->gotoUrl($this->scriptUrl.'order/hash?hash='.$hash);
		} else {
			$this->_helper->getHelper('Redirector')->gotoUrl($this->scriptUrl.'order/hash?hash='.$hash.'&value='.$value);
		}

		exit(0);
	}
	
	/**
	 * 提交订单
	 * 
	 * @throws Msd_Exception
	 */
	public function submitAction()
	{
		$cityConfig = &Msd_Config::cityConfig();
		$from = $this->getRequest()->getParam('from', '');
		$giftcode = $this->getRequest()->getParam('giftcode', '');
		$sales_attr = '';
		$partner_data = array();
		
		if($giftcode!='') {
			$table = &Msd_Dao::table('giftcard');
			$row = $table->get($giftcode);
			if($row==null||$row['UsedTime'] != null) {
				$this->_helper->getHelper('Redirector')->gotoUrl($this->scriptUrl);
				exit(0);
			} else {
				$partner_data['giftvalue'] = $row['Value'];
			}
		}
		
		switch ($from) {
			case 'special':
			case 'tuan':
				$cookieKey = 'tuan_items';
				break;
			case 'gift':
				$sales_attr = $cityConfig->db->guids->giftcard;
				$partner_data['giftcode'] = $giftcode;	
				$cookieKey = 'items';
				break;
			default:
				$cookieKey = 'items';
				break;
		}
		
		$cookieKey = $this->cPrefix.$cookieKey;
		
		$tmpd = &Msd_Waimaibao_Order::parseCookieItems($cookieKey, $this->ServiceName);
		
		$sgCache = &Msd_Cache_Loader::ServiceGroup();

		$SrvGrpGuid = $cityConfig->db->guids->service_group;
		
		$svp['SrvGrpGuid'] = $SrvGrpGuid;
		$svp['SrvGrpName'] = $sgCache[$SrvGrpGuid]['SrvGrpName'];
		
		$sitems = array();
		$sitems[$this->ServiceName]['items'] = &$tmpd;
		$sitems[$this->ServiceName]['express_setting'] = (int)Msd_Cookie::oc('express_setting', $this->cPrefix);
		$sitems[$this->ServiceName]['express'] = array(
				'year' => (int)Msd_Cookie::oc('pre_year', $this->cPrefix),
				'month' => (int)Msd_Cookie::oc('pre_month', $this->cPrefix),
				'day' => (int)Msd_Cookie::oc('pre_day', $this->cPrefix),
				'hour' => (int)Msd_Cookie::oc('pre_hour', $this->cPrefix),
				'minute' => (int)Msd_Cookie::oc('pre_minute', $this->cPrefix)	
				);
		
		$data = array(
			'items' => &$tmpd	,
			'sitems' => &$sitems,
			'Contactor' => $_COOKIE['contactor'],
			'Phone' => $_COOKIE['phone'],
			'Address' => $_COOKIE['address'],
			'PayMethod' => (int)Msd_Cookie::oc('paymethod', $this->cPrefix),
			'CoordGuid' => $_COOKIE['coord_guid'],
			'express_setting' => (int)Msd_Cookie::oc('express_setting', $this->cPrefix),
			'express' => array(
				'year' => (int)Msd_Cookie::oc('pre_year', $this->cPrefix),
				'month' => (int)Msd_Cookie::oc('pre_month', $this->cPrefix),
				'day' => (int)Msd_Cookie::oc('pre_day', $this->cPrefix),
				'hour' => (int)Msd_Cookie::oc('pre_hour', $this->cPrefix),
				'minute' => (int)Msd_Cookie::oc('pre_minute', $this->cPrefix)	
				),
			'ServiceName' => $this->ServiceName,
			'ServiceGuid' => Msd_Validator::isGuid($_COOKIE['service']) ? $_COOKIE['service'] : $cityConfig->db->guids->service,
			'SrvGrpGuid' => $cityConfig->db->guids->service_group
			);
		$hash = sha1(uniqid(mt_rand()));
		$this->sess->set('hash', $hash);
		
		$remarks = array();
		$tmp = explode('[]', Msd_Cookie::oc('remarks', $this->cPrefix));
		
		if (count($tmp)>0) {
			foreach ($tmp as $row) {
				list($remark, $_VendorGuid) = explode('{}', $row);
				$remark = trim($remark);
				$remarks[$_VendorGuid] = $remark;
			}
		}
		$data['remark'] = &$remarks;

		if ($data['items']) {
			$output = array(
				'success' => 0	
				);

			if (!$data['Contactor']) {
				$this->view->message = $output['exception'] = '请返回检查您填写的订单联系人';		
			} else if (!$data['Phone']) {
				$this->view->message = $output['exception'] = '请返回检查您填写的电话号码';
			} else if (!$data['Address']) {
				$this->view->message = $output['exception'] = '请返回检查您填写的送餐地址';
			} else {
				$OrderParams = array(
						'details' => &$data,
						'user' => &$this->member,
						'hash' => $hash
						);		
				if ($sales_attr) {
					$data['sales_attribute'] = &$sales_attr;
					$data['partner_data'] =  &$partner_data;
				}
				
				$result = Msd_Waimaibao_Order::create($OrderParams);
				
				if ($result) {
					Msd_Cookie::set($cookieKey, null);
					Msd_Cookie::set($this->cPrefix.'items', null);
					Msd_Cookie::set($this->cPrefix.'express_setting', null);
					Msd_Cookie::set($this->cPrefix.'pre_year', null);
					Msd_Cookie::set($this->cPrefix.'pre_month', null);
					Msd_Cookie::set($this->cPrefix.'pre_day', null);
					Msd_Cookie::set($this->cPrefix.'pre_hour', null);
					Msd_Cookie::set($this->cPrefix.'pre_minute', null);
					Msd_Cookie::set($this->cPrefix.'paymethod', null);
					
					$sc = $this->sess->get('order_params');
					$sc || $sc = array();
					$sc['UsedAddress'] || $sc['UsedAddress'] = array();
					
					$sc['Contactor'] = $data['Contactor'];
					$sc['Address'] = $data['Address'];
					$sc['Phone'] = $data['Phone'];
					$sc['UsedAddress'][] = array();
					
					$this->sess->set('order_params', $sc);
					
					$order_ids = explode(',', $this->sess->get('oids'));
					$order_ids || $order_ids = array();
					foreach ($result['OrderGuid'] as $OrderGuid) {
						$order_ids[] = $OrderGuid;
					}
					
					$this->sess->set('oids', implode(',', $order_ids));
					
					$output['success'] = 1;
					$this->ajaxOutput($output);
				} else {
					throw new Msd_Exception('下单失败');
				}
			}
		} else {
			throw new Msd_Exception('参数错误'.__LINE__);
		}
	}
	
	/**
	 * 提交订单前的确认界面
	 * 
	 * @throws Msd_Exception
	 */
	public function confirmAction()
	{
		if (Msd_Useragent::getInstance()->is_bot()) {
			$this->_helper->getHelper('Redirector')->gotoUrl($this->scriptUrl);
			exit(0);
		}

		$giftcode = $this->_request->getParam('giftcode','0');
		
		if($giftcode != '0')
		{
		    $table = &Msd_Dao::table('giftcard');
			$row = $table->get($giftcode);
			if($row==null||$row['UsedTime'] != null)
			{
				$this->_helper->getHelper('Redirector')->gotoUrl($this->scriptUrl);
				exit(0);
			}else
			{
				$this->view->over_value=$row['Value'];
			}
		}
		
		$arr = array(
			'contactor' , 'phone', 'address'
			);
		foreach ($arr as $_k) {
			$cv = $_COOKIE[$_k];
			if (!$cv) {
				$Vendor = Msd_Dao::table('vendor')->get($firstVendor);
				$this->_helper->getHelper('Redirector')->gotoUrl($this->scriptUrl.'vendor/'.$Vendor['VendorName'].'/service/'.$this->ServiceName);
				break;
			}
		}
		
		$from = $this->getRequest()->getParam('from', '');
		switch ($from) {
			case 'special':
			case 'tuan':
				$cookieKey = 'tuan_items';
				break;
			default:
				$cookieKey = 'items';
				break;
		}
		
	    $cookieKey = $this->cPrefix.$cookieKey;

		$parsedData = &Msd_Waimaibao_Order::parseCookieItems($cookieKey, $this->ServiceName);

		if (!$parsedData) {
			throw new Msd_Exception('参数错误, Line:'.__LINE__);
		}
		
		$cacher = &Msd_Cache_Remote::getInstance();
		$data = $cacher->get('Enums');

		$firstVendor = '';
		foreach ($parsedData as $VendorGuid=>$bar) {
			if (!$firstVendor) {
				$firstVendor = $VendorGuid;
				break;
			}
		}

		$this->view->data = $parsedData;
		$this->view->user = $this->member->uid() ? $this->member->extend() : array();
		$this->view->firstVendor = $firstVendor;
		$this->view->last_vendor_name = $this->sess->get('last_vendor_name');
	
		$remarks = $_remarks = $_items = $order_items = array();
		$tmp = explode(',', Msd_Cookie::oc('items', $this->cPrefix));
		
		if (count($tmp)>0) {
			foreach ($tmp as $row) {
				list($ItemGuid, $count) = explode('|', $row);
				$count = (int)$count;
				if ($count>0) {
					$order_items[$ItemGuid] = array(
							'ItemGuid' => $ItemGuid,
							'count' => $count
					);
					$_items[] = array(
							'ItemGuid' => $ItemGuid,
							'count' => $count
					);
				}
			}
		}
		
		$tmp = explode('[]', Msd_Cookie::oc('remarks', ''));

		if (count($tmp)>0) {
			foreach ($tmp as $row) {
				list($remark, $_VendorGuid) = explode('{}', $row);
				$remark = trim($remark);
		
				$_remarks[] = array(
						'VendorGuid' => $_VendorGuid,
						'remark' => $remark
				);
				$remarks[$_VendorGuid] = $remark;
			}
		}
		
		$this->view->citems = $_items;
		$this->view->cremarks = $_remarks;
		$this->view->remark = $remarks;		
		$this->view->ssdata = array();
	}

	public function indexAction()
	{
		$pager = $this->pager_init();
		$table = &Msd_Dao::table('feedback');
		
		$rows = $table->search($pager, array(
				'DisplayFlag' => '1'
		), array(
				'OrderNo' => 'ASC'
		));
		
		$this->view->rows = $rows;
		$this->view->page_links = $this->page_links();	
	}
	
	/**
	 * 订单支付的银行端回调
	 * 
	 */
	public function opcallbackAction()
	{
		$params = $this->getRequest()->getParams();
		
		$cConfig = &Msd_Config::cityConfig();
		$gateway = Msd_Service_Pay::getGatewayName($params);
		$payHandler = Msd_Service_Pay::factory($gateway);

		$opTable = &Msd_Dao::table('order/onlinepay');
		$result = $payHandler->parseCallback($params);
		$total = 0;

		if ($result['trans_result']) {
			$od = &Msd_Dao::table('order')->getByOrderId($result['_bid'], $cConfig->city_id);

			$row = &Msd_Dao::table('order/hash')->Order2Hash($od['OrderGuid']);

			if ($row && !$row['Payed'] && $result['trans_money']) {
				$PayedMoney = $result['trans_money'];
				$rows = &Msd_Dao::table('order/hash')->getHashOrders($row['Hash']);
				$rowsCount = count($rows);

				foreach ($rows as $_row) {
					$OrderGuid = $_row['OrderGuid'];
					
					$d = Msd_Waimaibao_Order::detail($OrderGuid);
					if ($d['order'] && !Msd_Waimaibao_Order::isCanceled($d['order']['StatusId'])) {
						$TotalAmount = $d['order']['TotalAmount'];
						$ThisMoney = ($rowsCount==1) ? $PayedMoney : (($PayedMoney - $total - $TotalAmount)>=0 ? $TotalAmount : ($PayedMoney - $total));
						$total += $TotalAmount;

						$pay = $opTable->get($OrderGuid);
						$payInfo = array(
							'PayedMoney' => $ThisMoney, 
							'PayedVia' => $gateway
							);

						if ($pay) {
							$opTable->doUpdate($payInfo, $OrderGuid);
						} else {
							$payInfo['OrderGuid'] = $OrderGuid;
							$opTable->insert($payInfo);
						}
						
						$hashInfo = array(
							'Payed' => '1',
							'BankApi' => $gateway,	
							'PayedMoney' => $ThisMoney
							);
						Msd_Dao::table('order/hash')->UpdateOrderStatus($hashInfo, $OrderGuid);
					}
				}
				
				$this->view->total = $total;
				
				$this->_helper->getHelper('Redirector')->gotoUrl($this->scriptUrl.'order/hash?hash='.$row['Hash']);
				exit(0);
			} else if ($row && $row['Payed']) {
				$this->_helper->getHelper('Redirector')->gotoUrl($this->scriptUrl.'order/hash?hash='.$row['Hash']);
				exit(0);
			} else {
				throw new Msd_Exception('Callback failed. Line:'.__LINE__);
			}
		} else {
			throw new Msd_Exception('Callback failed.'.__LINE__);
		}
	}
	
	/**
	 * 下单前的订单操作，如加菜减菜、地址修改等
	 */
	public function actionAction()
	{
		$act = $this->getRequest()->getParam('act', '');
		$result = array(
				'success' => 0
				);
		$cacher = &Msd_Cache_Remote::getInstance();
		
		switch ($act) {
			//	支付状态轮询
			case 'op_status':
				$hash = trim(urldecode($this->getRequest()->getParam('hash', '')));
				$key = 'ops_'.$hash;
				$cdata = $cacher->get($key);
				
				if (!$cdata) {
					$payed = 0;
					$valid = false;
					
					$d = &Msd_Dao::table('order/hash')->getHashOrders($hash);
					if (is_array($d) && count($d)>0) {
						$valid = true;
						foreach ($d as $row) {
							if ($row['Payed']) {
								$payed ++;
							}
						}
					}
	
					$result['success'] = ($valid && $payed==count($d)) ? '1' : '0';
					$cdata = array(
						'success' => (int)$result['success']	
						);
					$cacher->set($key, $cdata, 30);
				} else {
					$result['success'] = (int)$cdata['success'];
				}
				break;
			
			//	检查订单待支付状态
			case 'op_ready':
				$hash = trim(urldecode($this->getRequest()->getParam('hash', '')));
				$key = 'opr_'.$hash;
				$cdata = $cacher->get($key);
				
				if (!$cdata) {
					$op_ready = 0;
					$rows = &Msd_Dao::table('order/hash')->getHashOrders($hash);
					$config = &Msd_Config::appConfig();
					$valid = false;
					
					if (is_array($rows) && count($rows)>0) {
						$valid = true;
						foreach ($rows as $row) {
							$OrderGuid = $row['OrderGuid'];
							$_detail = &Msd_Waimaibao_Order::detail($OrderGuid);
							if ($_detail['order']['PaymentMethod'] && $_detail['order']['StatusId']==$config->order->status->confirmed) {
								$op_ready++;
							}
						}
					}
	
					$result['success'] = ($valid && $op_ready==count($rows)) ? '1' : '0';
					$cdata = array(
						'success' => $result['success']	
						);
					$cacher->set($key, $cdata, 10);
				} else {
					$result['success'] = (int)$cdata['success'];
				}
				break;
		}
		
		$this->ajaxOutput($result);
	}
	
	/**
	 * 下单初始化
	 */
	protected function _initAction()
	{
	}
}

