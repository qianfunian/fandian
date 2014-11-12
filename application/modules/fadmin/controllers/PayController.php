<?php

class Fadmin_PayController extends Msd_Controller_Fadmin
{
	public function init()
	{
		parent::init();
	}
	
	public function linkAction()
	{
		$this->auth('paylink');
		$doSearch = (int)$this->getRequest()->getParam('do_search', '0');
		if ($doSearch) {
			$OrderId = trim($this->getRequest()->getParam('OrderId', ''));
			if ($OrderId) {
				$order = &Msd_Dao::table('order')->getByOrderId($OrderId);
				if ($order) {
					$hash = &Msd_Dao::table('order/hash')->order2hash($order['OrderGuid']);
					$this->view->data = $hash;
				}
			}	
		}	
	}
	
	public function checkAction()
	{
		$this->auth('pay_check');
		
		$doSearch = (int)$this->getRequest()->getParam('do_search', '0');
		if ($doSearch) {
			$OrderId = trim($this->getRequest()->getParam('OrderId', ''));
			if ($OrderId) {
				$config = &Msd_Config::cityConfig();
				
				$data = array(
						'cmb' => '没有查询到支付结果',
						'comm' => '没有查询到支付结果'
					);

				$id = substr($OrderId, 1, 10);
				$date = '2013'.substr($id, 2, 4);

				$cmbUrl = $config->onlinepay->bankcmb->query_url.'?date='.$date.'&billno='.$id;
				$commUrl = $config->onlinepay->bankcomm->query_url.'?orders='.$id;

				$result = @file_get_contents($cmbUrl);
				$arr = explode("\n", trim($result));
				if (count($arr)>3 && $arr[0]=='ok') {
					$data['cmb'] = '已支付: '.$arr[count($arr)-1];
				}

				$result = @file_get_contents($commUrl);
				$result = strip_tags(trim($result));
				$arr = explode('|', $result);

				if (count($arr)>10 && $arr[1]==$id) {
					$data['comm'] = '已支付: '.$arr[5];
				}		

				$this->view->data = $data;		
			}
		}
	}
	
	public function historyAction()
	{
		$this->auth('pay');
		
		$this->pager_init();
		 
		$table = &Msd_Dao::table('order');
		 
		$params = $sort = array();
		$params['s_date'] = urldecode(trim($this->getRequest()->getParam('s_date', date('Y-m-d'))));
		$params['e_date'] = urldecode(trim($this->getRequest()->getParam('e_date', date('Y-m-d'))));
		$params['bank'] = trim($this->getRequest()->getParam('bank', ''));
		$params['AreaGuid'] = Msd_Config::cityConfig()->db->guids->area->toArray();
		
		$sort['AddTime'] = 'DESC';
		
		$rows = $table->payHistory($this->pager, $params, $sort);

		$this->view->rows = $rows;
		$this->view->page_links = $this->page_links();
		$this->view->data = array();
		$this->view->request = $_REQUEST;
		 
		$this->log(array(
				'type' => 'browse',
				'message' => '浏览网上支付历史',
		));		
	}
}