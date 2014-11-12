<?php

class Fadmin_ApiController extends Msd_Controller_Fadmin
{
	public function init()
	{
		parent::init();
		
		$this->auth('api');
	}
	
	public function delAction()
	{
		$Id = (int)$this->getRequest()->getParam('Id', '');
		
		if ($Id) {
			Msd_Dao::table('api/keys')->doDelete($Id);
			
			Msd_Hook::run('ApiKeysChanged');
		}
		
		$this->redirect($this->scriptUrl.'api');
	}
	
	public function doeditAction()
	{
		$p = &$_POST;
		$error = array();
		$table = &Msd_Dao::table('api/keys');
		$Id = (int)$p['Id'];
		
		if (trim($p['Owner'])=='') {
			$error['Owner'] = '请填写Api所有人';
		}
		
		if ($p['ApiKey'] && $table->CheckKeyExists($p['ApiKey'], $Id)) {
			$error['ApiKey'] = '这个Api已经存在了';
		}
		
		if (count($error)>0) {
			$this->view->error = $error;
			$this->editAction();
			$this->view->data = $p;
			
			$this->view->render('api/edit.phtml');
			exit(0);
		}
		
		$params = array(
				'ApiKey' => $p['ApiKey'],
				'Owner' => $p['Owner'],
				'VisitsPerHour' => (int)$p['VisitsPerHour'],
				'VisitsPerDay' => (int)$p['VisitsPerDay']
				);
		if ($Id) {
			$table->doUpdate($params, $Id);
			
			$this->log(array(
					'type' => 'update',
					'message' => '更新Api, Id: '.$Id
					));
		} else {
			$table->insert($params);
			
			$this->log(array(
					'type' => 'insert',
					'message' => '新增Api, Key: '.$params['ApiKey']
					));
		}
			
		Msd_Hook::run('ApiKeysChanged');
		
		$this->redirect($this->scriptUrl.'api');
	}
	
	public function editAction()
	{
		$Id = (int)$this->getRequest()->getParam('Id', 0);
		$data = array();
		$table = &Msd_Dao::table('api/keys');
		
		if ($Id) {
			$data = $table->get($Id);
		} else {
			$data = array(
					'VisitsPerHour' => 999,
					'VisitsPerDay' => 9999,
					'ApiKey' => Msd_Api_Key::GenNewKey()
					);
		}
		
		$this->view->data = $data;
	}
	
	public function indexAction()
	{
		$this->pager_init();

		$table = &Msd_Dao::table('api/keys');
		 
		$params = $sort = array();
		$orderKey = trim($this->getRequest()->getParam('order_key', ''));
		$searchKey = trim(urldecode($this->getRequest()->getParam('search_key', '')));
		$searchVal = trim(urldecode($this->getRequest()->getParam('search_val', '')));
		 
		if (strlen($searchKey) && strlen($searchVal)) {
			$params[$searchKey] = $searchVal;
		}
		 
		if ($orderKey!='') {
			$sort[$orderKey] = 'DESC';
		}

		$rows = $table->search($this->pager, $params, $sort);

		$this->view->rows = $rows;
		$this->view->page_links = $this->page_links();
		$this->view->data = array();
		$this->view->request = $_REQUEST;
		 
		$this->log(array(
				'type' => 'browse',
				'message' => '浏览Api',
		));		
	}
	
}