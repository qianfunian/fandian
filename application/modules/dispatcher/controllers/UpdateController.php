<?php

class Dispatcher_UpdateController extends Msd_Controller_Dispatcher
{
	const CONFIRM = 'confirm';
	const SENDING = 'sending';
	const OVER = 'over';
	
	public function init()
	{
		parent::init();	
	}
	
	public function indexAction()
	{
		$this->sessCheck();
		
		$this->xmlRoot = 'result';
		
		$method = $this->getRequest()->getParam('method');
		$OrderId = $this->getRequest()->getParam('no');
		$status = $this->getRequest()->getParam('status');
		$cacher = &Msd_Cache_Remote::getInstance();
		$orders = &$cacher->get($this->cKey);
		
		if ($method!='change') {
			$this->error(2100);
		}
		
		$oKey = $this->user['CityId'].$OrderId;
		$order = &$orders[$oKey];
		
		$result = false;
		
		switch ($status) {
			case self::CONFIRM:
				$result = Msd_Waimaibao_Order_Dispatcher::confirm($order);
				break;
			case self::SENDING:
				$result = Msd_Waimaibao_Order_Dispatcher::sending($order);
				break;
			case self::OVER:
				$result = Msd_Waimaibao_Order_Dispatcher::over($order);
				break;
		}
		
		if ($result) {
			$this->output[$this->xmlRoot] = 'ok';
		}
		
		$this->output();
	}
}