<?php

class Fadmin_LogsController extends Msd_Controller_Fadmin
{
	protected $Categories = array();
	
	public function init()
	{
		parent::init();
		
		$this->auth('logs');
	}
	
	public function adminAction()
	{
		$this->pager_init();
		 
		$table = &Msd_Dao::table('systemlog');
		 
		$params = $sort = array();
		$orderKey = trim($this->getRequest()->getParam('order_key', ''));
		$searchKey = trim(urldecode($this->getRequest()->getParam('search_key', '')));
		$searchVal = trim(urldecode($this->getRequest()->getParam('search_val', '')));
		$ActionType = trim($this->getRequest()->getParam('ActionType'));
		$from = trim($this->getRequest()->getParam('from', ''));
		$to = trim($this->getRequest()->getParam('to', ''));
		
		$yesterday  = new DateTime(date('Y-m-d 00:00:00', time()-3600*24));

		if (!$from) {
			$from = date('Y-m-d', $yesterday->getTimeStamp());
			$_REQUEST['from'] = $from;
			$from .= ' 00:00:00';
		}
		
		if (!$to) {
			$to = date('Y-m-d');
			$_REQUEST['to'] = $to;
			$to .= ' 23:59:59';
		}
		
		$params['to'] = $to;
		$params['from'] = $from;
		
		if (strlen($searchKey) && strlen($searchVal)) {
			$params[$searchKey] = $searchVal;
		}
		
		if (strlen($ActionType)) {
			$params['ActionType'] = $ActionType;
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
				'message' => '浏览后台日志',
		));		
	}
}