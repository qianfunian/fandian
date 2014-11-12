<?php

class Dispatcher_ChatController extends Msd_Controller_Dispatcher
{
	public function init()
	{
		parent::init();
	}
	
	public function indexAction()
	{
		$this->sessCheck();
		
		$this->xmlRoot = 'root';

		$lastId = (int)$this->getRequest()->getParam('lastid', 0);
		$msg = trim(urldecode($this->getRequest()->getParam('msg', '')));
		$cacher = &Msd_Cache_Remote::getInstance();
		$chats = &$cacher->get($this->sKey);
		$t = &$this->t('chat');
		
		foreach ($chats as $chat) {
			$this->output[$this->xmlRoot][] = array(
				'chat' => $t->translate($chat)
			);			
		}
		
		if ($msg) {
			Msd_Dao::table('chat')->insert(array(
				'Sender' => $this->uid,
				'Receiver' => 'dd',
				'Message' => $msg,
				'CityGuid' => $this->user['CityGuid']
				));
		}
		
		$this->output();
	}
}