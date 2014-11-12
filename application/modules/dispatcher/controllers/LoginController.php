<?php

class Dispatcher_LoginController extends Msd_Controller_Dispatcher
{
	public function init()
	{
		parent::init();	
	}
	
	public function indexAction()
	{
		$this->xmlRoot = 'root';
		
		$method = $this->getRequest()->getParam('method');
		$account = $this->getRequest()->getParam('account');
		$password = $this->getRequest()->getParam('password');
		$device = $this->getRequest()->getParam('device');
		
		if ($method!='login') {
			$this->error(2100);
		}
		
		if (!$account || !$password) {
			$this->error(1000);
		}
		
		$dao = &Msd_Dao::table('deliveryman');
		$d = $dao->getDlvManId($account);

		if (!$d['DlvManId'] || strtoupper($password)!=strtoupper(md5($d['Password']))) {
			$this->error(1002);
		}
		
		$cacher = &Msd_Cache_Remote::getInstance();
		$data = $cacher->get(Msd_Waimaibao_Order_Dispatcher::dKey().'dmi_'.$account);
		$t = &$this->t('dispatcher');
		
		$this->sess->set('dispatcher_uid', $account);
		$this->sess->set('dispatcher_user', $d);
		
		$orders = array();
		foreach ($data as $order) {
			$order['Changed'] = false;
			$orders[$order['CityId'].$order['OrderId']] = $order;
			$this->output[$this->xmlRoot][] = array(
				'order' => $t->translate($order)	
				);
		}
		
		$cacher->set(Msd_Waimaibao_Order_Dispatcher::dKey().'dmi_'.$account, $orders , MSD_ONE_DAY);
		
		$vars = &Msd_Cache_Loader::Systemvars();
		$version = array(
			'verno' => '',
			'url' => ''	
			);
		switch ($device) {
			case 'j2me':
				$version['verno'] = $vars['symbian_ver'];
				$version['url'] = $vars['symbian_url'];
				break;
			case 'android':
				$version['verno'] = $vars['android_ver'];
				$version['url'] = $vars['android_url'];
				break;
		}
		
		$this->output[$this->xmlRoot]['version'] = $version;
		
		$this->output();
	}
}