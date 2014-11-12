<?php

class Fadmin_AttachmentController extends Msd_Controller_Fadmin
{
	protected $Categories = array();
	
	public function init()
	{
		parent::init();
		
		$this->auth('attachment');
	}
	
	public function actionsAction()
	{
		$action = trim($this->getRequest()->getParam('todo'));
		
		switch ($action) {
			case 'delete':
				$toDel = $_POST['to_del'];
				foreach ($toDel as $fid) {
					Msd_Uploader::Del($fid);
				}
				
				$this->log(array(
						'type' => 'delete',
						'message' => '批量删除附件：'.implode(',', $toDel),
				));
				break;
		}
		
		$this->redirect($this->scriptUrl.'attachment');
	}
	
	public function indexAction()
	{
		$this->pager_init();
		 
		$table = &Msd_Dao::table('attachment');
		 
		$params = $sort = array();
		$orderKey = trim($this->getRequest()->getParam('order_key', ''));
		$searchKey = trim(urldecode($this->getRequest()->getParam('search_key', '')));
		$searchVal = trim(urldecode($this->getRequest()->getParam('search_val', '')));
		$Usage = trim($this->getRequest()->getParam('Usage', ''));
		 
		if (strlen($searchKey) && strlen($searchVal)) {
			$params[$searchKey] = $searchVal;
		}
		 
		if ($orderKey!='') {
			$sort[$orderKey] = 'DESC';
		}
		
		$Usage!='' && $params['Usage'] = array($Usage);
		 
		$rows = $table->search($this->pager, $params, $sort);
		 
		$this->view->rows = $rows;
		$this->view->page_links = $this->page_links();
		$this->view->data = array();
		$this->view->request = $_REQUEST;
		 
		$this->log(array(
				'type' => 'browse',
				'message' => '浏览附件',
		));		
	}
}