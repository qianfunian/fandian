<?php

class Dispatcher_HeartbitController extends Msd_Controller_Dispatcher
{
	protected $gps = '';
	
	function __destruct()
	{
		if ($this->user['DlvManGuid']) {
			$data = array();
			list($lat, $lng) = explode(',', $this->gps);
			if (Msd_Validator::isLngLat($lng, $lat)) {
				Msd_Dao::table('historyrawgps')->insert(array(
					'DlvManGuid' => $this->user['DlvManGuid'],
					'DlvManId' => $this->user['DlvManId'],
					'Longitude' => (float)$lng,
					'Latitude' => (float)$lat
					));
			}
			
			Msd_Dao::table('deliveryman')->doUpdate(array(
				'LastHeartBeat' => date('Y-m-d H:i:s').'.000'
				), $this->user['DlvManGuid']);
		}
	}
	
	public function init()
	{
		parent::init();	
	}
	
	public function indexAction()
	{
		$this->sessCheck();
		
		$this->xmlRoot = 'root';
		$method = $this->getRequest()->getParam('method');
		
		if ($method!='polling') {
			$this->error(2100);
		}		
		
		$this->output[$this->xmlRoot] = array(
			'orders' => '',
			'chats' => array()	
			);
		
		$this->gps = trim(urldecode($this->getRequest()->getParam('gps', '')));
		
		$lastId = (int)$this->getRequest()->getParam('lastid', 0);
		$list = explode(',', trim(urldecode($this->getRequest()->getParam('list', ''))));
		$sync = trim($this->getRequest()->getParam('sync', ''));
		$now = time();
		
		if ($sync!='yes' && ($now-$this->lastSync)>3) {
			$this->sess->set('last_sync', $now);
		}
		
		$cacher = &Msd_Cache_Remote::getInstance();
		$orders = &$cacher->get($this->cKey);
		$t = &$this->t('dispatcher');
		
		if (is_array($orders) && count($orders)>0) {
			$os = array();
			foreach ($orders as $order) {
				$Changed = (bool)$order['Changed'];
		
				if ($Changed && !in_array($order['OrderId'], $list)) {
					$os[] = $order['OrderId'];
					$this->output[$this->xmlRoot][] = array(
							'order' => $t->translate($order)
					);
				}
				
				($sync=='yes' && ($now-$this->lastSync)>3) && $this->output[$this->xmlRoot]['orders'] = implode(',', $os);
			}
		}
		
		if (count($list)>0) {
			foreach ($list as $OrderId) {
				if ($OrderId) {
					$oKey = $this->user['CityId'].$OrderId;
					$orders[$oKey]['Changed'] = false;
				}
			}
				
			$cacher->set($this->cKey, $orders, MSD_ONE_DAY, 1);
		}
		
		$chats = &$cacher->get($this->sKey);
		$ct = &$this->t('chat');
		if (is_array($chats) && count($chats)>0) {
			foreach ($chats as $chat) {
				if ($chat['ID']>=$lastId) {
					$this->output[$this->xmlRoot]['chats'][] = array(
							'chat' => $ct->translate($chat)
					);
				}
			}
		}
		
		$this->output();
	}
}