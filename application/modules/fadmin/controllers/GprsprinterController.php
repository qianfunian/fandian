<?php

class Fadmin_GprsprinterController extends Msd_Controller_Fadmin
{
	public function init()
	{
		parent::init();
		
		$this->auth('vendor');
	}
	
	public function delAction()
	{
		$VendorGuid = $this->getRequest()->getParam('VendorGuid');
		$table = &Msd_Dao::table('vendor/gprsprinter');
		$table->doDelete($VendorGuid);
		
		$this->redirect($this->scriptUrl.'gprsprinter');
	}
	
	public function doeditAction()
	{
		$VendorGuid = $this->getRequest()->getParam('VendorGuid');
		$_VendorGuid = '';
		
		$VendorName = trim(urldecode($this->getRequest()->getParam('VendorName', '')));
		$Sn1 = $this->getRequest()->getParam('Sn1');
		$Sn2 = $this->getRequest()->getParam('Sn2');
		$Key = $this->getRequest()->getParam('Key');
		$Cell = $this->getRequest()->getParam('Cell');
		
		$gTable = &Msd_Dao::table('vendor/gprsprinter');
		$vTable = &Msd_Dao::table('vendor');
		
		$p = &$_POST;
		$error = array();
		
		if (!$VendorGuid && $VendorName=='') {
			$error['VendorName'] = '请输入打印机所属的商家名';
		} else if (!$VendorGuid) {
			$Vendor = $vTable->getByName($VendorName);
			if (!$Vendor['VendorGuid']) {
				$error['VendorName'] = '没有找到这个商家';
			} else {
				$_VendorGuid = $Vendor['VendorGuid'];
			}
		}

		if (count($error)>0) {
			$this->view->error = $error;
			$this->editAction();
			$this->view->data = $p;
			
			echo $this->view->render('vendor/edit.phtml');
			exit(0);
		}
		
		$data = array(
			'Sn1' => $Sn1,
			'Sn2' => $Sn2,
			'Key' => $Key,
			'Cell' => $Cell
			);
		
		if ($VendorGuid) {
			$gTable->doUpdate($data, $VendorGuid);
			
			$this->log(array(
				'type' => 'update',
				'message' => '更新GPRS打印机资料',	
				));
		} else {
			$data['VendorGuid'] = $_VendorGuid;
			$gTable->insert($data);
			
			$this->log(array(
				'type' => 'insert',
				'message' => '新增GPRS打印机',	
				));
		}

		$this->redirect($this->scriptUrl.'gprsprinter');
	}
	
	public function editAction()
	{
		$VendorGuid = $this->getRequest()->getParam('VendorGuid');
		$data = $vendor = array();
		$table = &Msd_Dao::table('vendor/gprsprinter');
		$vTable = &Msd_Dao::table('vendor');
		
		if ($VendorGuid) {
			$data = $table->get($VendorGuid);
			$vendor = $vTable->get($VendorGuid);
		}
		
		$this->view->data = $data;
		$this->view->vendor = $vendor;
	}
	
	public function indexAction()
	{
		$this->pager_init();

		$table = &Msd_Dao::table('vendor/gprsprinter');
		 
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
				'message' => '浏览GPRS打印机',
			));		
	}
}