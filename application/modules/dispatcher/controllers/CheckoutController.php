<?php

class Dispatcher_CheckoutController extends Msd_Controller_Dispatcher
{
	public function init()
	{
		parent::init();
	}
	
	public function indexAction()
	{
		$this->sessCheck();	
		$this->xmlRoot = 'root';

		$method = $this->getRequest()->getParam('method', 'list');
		
		switch ($method) {
			case 'save':
				$this->_save();
				break;
			default:
				$this->_list();
				break;
		}
		
		$this->output();
	}
	
	protected function _list()
	{
		$t = &$this->t('checkout');
		$orders = &Msd_Dao::table('delivery/order')->checkout(array(
					'DlvManGuid' => $this->user['DlvManGuid']
					));
		
		foreach ($orders as $order) {
			$this->output[$this->xmlRoot]['orders'][] = array(
				'order' => $t->translate($order)
				);
		}
	}
	
	protected function _save()
	{
		$OrderId = trim($this->getRequest()->getParam('OrderId'));
		$BaoXiao = trim($this->getRequest()->getParam('BaoXiao'));
		$FaPiao = trim($this->getRequest()->getParam('FaPiao'));
		$Comment = trim(urldecode($this->getRequest()->getParam('Comment')));
		
		if (!$OrderId) {
			$this->error(2001);
		}
		
		$dao = &Msd_Dao::table('checkout');
		$data = $dao->getByOrderId($OrderId, $this->user['CityGuid']);
		
		if ($data['ID']) {
			$dao->doUpdate(array(
				'BaoXiao' => $BaoXiao,
				'FaPia' => $FaPiao,
				'Comment' => $Comment	
				), $data['ID']);
		} else {
			$dao->insert(array(
				'OrderId' => $OrderId,
				'BaoXiao' => $BaoXiao,
				'FaPiao' => $FaPiao,
				'Comment' => $Comment,
				'CityGuid' => $this->user['CityGuid'],
				'DlvManId' => $this->user['DlvManId']	
				));
		}
		
		$this->xmlRoot = 'result';
		$this->output[$this->xmlRoot] = 'ok';
	}
}