<?php

/**
 * 内部使用的订单查询
 * @author pang
 * @email pang@fandian.com
 *
 */
class Api_OrdersController extends Msd_Controller_Api
{
	protected $allowedIps = array(
				'61.160.102.78',
				'121.197.10.169',
				'112.25.208.205',
				'112.25.208.206',
				'112.25.208.207',
				'127.0.0.1',
				'192.168.1.55',
				'172.29.40.56'
				);
	
	public function init()
	{
		parent::init();
	
		Zend_Controller_Front::getInstance()->setParam('noViewRenderer', 0);
		
		$ip = Msd_Request::clientIp();
		if (!in_array($ip, $this->allowedIps)) {
			$this->error('error.orders.ip_not_allowed');
		}
	}
	
	public function gogogoAction()
	{
		$this->xmlRoot = 'orders';
		
		$dao = &Msd_Dao::table('order');
		$pager = $this->pager_init();
		$rows = $dao->search($pager, array(), array());
		$data = array();
		foreach ($rows as $row) {
			$data = Msd_Waimaibao_Order::detail($row['OrderGuid']);
			$this->output[$this->xmlRoot][] = array(
				'order' => $this->t('order')->translate($data)	
				);
		}
		
		$this->output();
	}
}