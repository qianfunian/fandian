<?php

class Member_CommentController extends Msd_Controller_Member
{
	protected $handler = null;
	
	public function init()
	{
		parent::init();
	
		$this->AuthRedirect();
		$this->handler = &Msd_Member_Order::getInstance($this->member->uid());
	}
	
	public function indexAction()
	{
		$this->pager_init();
		
		$table = &Msd_Dao::table('order/comment');
			
		$params = $sort = array();
		$orderKey = trim($this->getRequest()->getParam('order_key', ''));
		$searchKey = trim(urldecode($this->getRequest()->getParam('search_key', '')));
		$searchVal = trim(urldecode($this->getRequest()->getParam('search_val', '')));
			
		$params['CustGuid'] = $this->member->uid();
		
		if (strlen($searchKey) && strlen($searchVal)) {
			$params[$searchKey] = $searchVal;
		}
			
		if ($orderKey!='') {
			$sort[$orderKey] = 'DESC';
		}
		
		$rows = $table->search($this->pager, $params, $sort);
		
		$this->view->rows = $rows;
		$this->view->page_links = $this->page_links($this);
		$this->view->data = array();
		$this->view->request = $_REQUEST;		
	}
}

