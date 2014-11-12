<?php

class Fadmin_ArticlecategoryController extends Msd_Controller_Fadmin
{
	public function init()
	{
		parent::init();
		
		$this->auth('articlecategory');
	}
	
	public function indexAction()
	{
    	$this->pager_init();
    	
    	$table = &Msd_Dao::table('article/category');
    	
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
    			'message' => '浏览文章分类',
    			));
	}
	
	public function editAction()
	{
		$CategoryId = (int)$this->getRequest()->getParam('CategoryId', 0);
		$this->view->data = Msd_Article_Category::getInstance($CategoryId)->data();
	}
	
	public function doeditAction()
	{
		$CategoryId = (int)$this->getRequest()->getParam('CategoryId', 0);
		$params = array();
		$params['CategoryName'] = trim($this->getRequest()->getParam('CategoryName', ''));
		$params['OrderNo'] = (int)$this->getRequest()->getParam('OrderNo', '9999');
		
		$table = &Msd_Dao::table('article/category');
		if ($CategoryId) {
			$table->doUpdate($params, $CategoryId);
			
			$this->log(array(
					'type' => 'update',
					'message' => '修改文章分类，ID: '.$CategoryId
					));
		} else {
			$CategoryId = $table->insert($params);
			
			$this->log(array(
					'type' => 'insert',
					'message' => '新增文章分类，分类名称: '.$params['CategoryName']
					));
		}
		
		Msd_Hook::run('ArticleCategoryChanged');
		
		$this->redirect($this->scriptUrl.'articlecategory');
	}
	
	public function delAction()
	{
		$CategoryId = (int)$this->getRequest()->getParam('CategoryId', 0);
		
		$Category = &Msd_Article_Category::getInstance($CategoryId);
		$data = $Category->data();
		
		if ($data['CategoryId']) {
			$Category->delete();
		
			$this->log(array(
					'type' => 'delete',
					'message' => '删除文章分类，分类名称: '.$data['CategoryName']
					));
			
			Msd_Hook::run('ArticleCategoryChanged');
		}
		
		$this->redirect($this->scriptUrl.'articlecategory');
	}
}

