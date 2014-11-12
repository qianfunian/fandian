<?php

class Dispatcher_GetordersController extends Msd_Controller_Dispatcher
{
	public function init()
	{
		parent::init();
	}
	
	public function indexAction()
	{
		$this->sessCheck();	
		$this->xmlRoot = 'root';

		$method = $this->getRequest()->getParam('method');
		$list = (array)explode(',', trim(urldecode($this->getRequest()->getParam('list', ''))));
		Msd_Log::getInstance()->debug(var_export(getallheaders(), true));
		if ($method!='getorders' || count($list)<=0) {
			$this->error(2100);
		}
		
		$this->output[$this->xmlRoot] = array();
		
		$cacher = &Msd_Cache_Remote::getInstance();
		$data = $cacher->get(Msd_Waimaibao_Order_Dispatcher::dKey().'dmi_'.$this->uid);
		$t = &$this->t('dispatcher');
		
		$orders = array();
		foreach ($data as $order) {
			if ($order['CityId']==$this->user['CityId'] && in_array($order['OrderId'], $list)) {
				$orders[] = $order['OrderId'];
				$this->output[$this->xmlRoot][] = array(
					'order' => $t->translate($order)
					);
			}
		}
		
		$this->output();
	}
}