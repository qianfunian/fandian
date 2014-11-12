<?php

class Member_AddressbookController extends Msd_Controller_Member
{
	protected $handler = null;
	protected $all = array();
	
	public function init()
	{
		parent::init();
		
		$this->AuthRedirect();
		$this->handler = &Msd_Member_Addressbook::getInstance($this->member->uid());
		$this->all = &$this->handler->all();
	}	

	public function delAction()
	{
		$ABGuid = $this->getRequest()->getParam('ABGuid', '');
		if ($ABGuid) {
			$this->handler->delete($ABGuid);
		} 
		
		$this->redirect('addressbook');
	}
	
	public function indexAction()
	{
		$this->view->rows = $this->all;	
	}

	public function doAction()
	{
		$p = $this->getRequest()->getPost();
		$ABGuid = $p['ABGuid'];
		$error = array();
		
		$data = $this->handler->get($ABGuid);
		if (!$data['ABGuid']) {
			$ABGuid = $p['ABGuid'] = 0;
		}
		
		if (trim($p['Title'])=='') {
			$error['Title'] = '请填写地址簿的标题';
		}
		
		if (trim($p['Address'])=='' || !Msd_Validator::isGuid($p['CoordGuid'])) {
			$error['Address'] = '请填写地址并设定地标';
		}
		
		if (trim($p['Contactor'])=='') {
			$error['Contactor'] = '请填写联系人';
		}
		
		if (trim($p['Phone'])=='') {
			$error['Phone'] = '请填写电话';
		}
		
		if (count($error)>0) {
			$this->view->error = $error;
			$this->view->request = $p;
		} else {
			$params = array(
					'Title' => $p['Title'],
					'IsDefault' => $p['IsDefault'],
					'Address' => $p['Address'],
					'Contactor' => $p['Contactor'],
					'Phone' => $p['Phone'],
					'CoordGuid' => $p['CoordGuid'],
					'OrderNo' => '9999'
					);
			if ($ABGuid) {
				$this->handler->update($params, $ABGuid);
			} else {
				$ABGuid = $this->handler->add($params);
			}
			
			if ($p['IsDefault']) {
				$this->handler->resetDefault($ABGuid, $params);
			}
		}
	}
}

