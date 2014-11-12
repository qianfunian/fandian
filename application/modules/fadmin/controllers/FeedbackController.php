<?php

class Fadmin_FeedbackController extends Msd_Controller_Fadmin
{
	protected $Categories = array();
	
	public function init()
	{
		parent::init();
		
		$this->auth('feedback');
	}
	
	public function doeditAction()
	{
		$AutoId = (int)$this->getRequest()->getParam('AutoId', 0);

		$table = &Msd_Dao::table('feedback');
		
		if ($AutoId) {
			$data = $table->get($AutoId);
			if ($data['AutoId']) {
				$rc = $_POST['ReplyContent'];
				$params = array(
						'ReplyContent' => $rc,
						'DisplayFlag' => $_POST['DisplayFlag'] ? 1 : 0,
						'OrderNo' => (int)$_POST['OrderNo']
						);
				if (!$data['ReplyTime'] && trim(strip_tags($rc))!='') {
					$params['ReplyTime'] = date('Y-m-d H:i:s');
				}
				
				$table->doUpdate($params, $AutoId);
			}
		} else {
			throw new Msd_Exception('请选择要编辑的留言');
		}		
		
		$this->redirect($this->scriptUrl.'feedback');
	}
	
	public function editAction()
	{
		$this->view->headScript()->appendFile($this->baseUrl.'js/kindeditor/kindeditor-min.js');
		
		$AutoId = (int)$this->getRequest()->getParam('AutoId', 0);
		$data = array(
				'OrderNo' => '9999'
				);
		$table = &Msd_Dao::table('feedback');
		
		if ($AutoId) {
			$data = $table->get($AutoId);
		} else {
			throw new Msd_Exception('请选择要编辑的留言');
		}
		
		$this->view->data = $data;
	}
	
	public function indexAction()
	{
		$this->pager_init();

		$table = &Msd_Dao::table('feedback');
		 
		$params = $sort = array();
		$orderKey = trim($this->getRequest()->getParam('order_key', ''));
		$searchKey = trim(urldecode($this->getRequest()->getParam('search_key', '')));
		$searchVal = trim(urldecode($this->getRequest()->getParam('search_val', '')));
		 
		if (strlen($searchKey) && strlen($searchVal)) {
			$params[$searchKey] = $searchVal;
		}
		
		$params['Regions'] = Msd_Config::cityConfig()->db->guids->area->toArray();
		$params['DisplayFlag'] = trim($this->getRequest()->getParam('DisplayFlag'));
		 
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
				'message' => '浏览用户留言',
		));		
	}
	
	public function delAction()
	{
		$AutoId = (int)$this->getRequest()->getParam('AutoId');
		
		if ($AutoId) {
			Msd_Dao::table('feedback')->doDelete($AutoId);
			
			$this->log(array(
					'type' => 'delete',
					'message' => '删除用户留言, Id:'.$AutoId
					));
		}
		
		$this->redirect($this->scriptUrl.'feedback');
	}
}