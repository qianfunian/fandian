<?php

class Fadmin_MemberController extends Msd_Controller_Fadmin
{
	
	public function init()
	{
		parent::init();
		
		$this->auth('member');
	}
	
	public function doeditAction()
	{
		$ArticleId = (int)$this->getRequest()->getParam('ArticleId', 0);
		
		$Title = trim($this->getRequest()->getParam('Title', ''));
		$CategoryId = (int)$this->getRequest()->getParam('CategoryId', 0);
		$p = &$_POST;
		$error = array();
		
		if ($Title=='') {
			$error['Title'] = '请填写文章标题';
		}

		if (!isset($this->Categories[$CategoryId])) {
			$error['CategoryId'] = '请选择所属分类';
		}
		
		if (count($error)>0) {
			$this->view->error = $error;
			$this->editAction();
			$this->view->data = $p;
			
			echo $this->view->render('article/edit.phtml');
			exit(0);
		}
		
		$table = &Msd_Dao::table('article');
		$data = array(
				'Title' => $Title,
				'CategoryId' => (int)$p['CategoryId'],
				'Detail' => $p['Detail'],
				'OrderNo' => $p['OrderNo'],
				'Views' => 0,
				'PubFlag' => $p['PubFlag'] ? '1' : '0',
				'FirstAttach' => ''
				);
		
		Msd_Uploader::SaveFilesOrder($p['attach_list_orders']);
		
		if ($p['attach_list_orders']) {
			$tmp = explode(',', $p['attach_list_orders']);
			$data['FirstAttach'] = $tmp[0];
		} else {
			if ($p['FirstAttach']) {
				$data['FirstAttach'] = $p['FirstAttach'];
			} else {
				$files = Msd_Files::GetByHash($p['attach_hash']);
				if (count($files)>0) {
					$data['FirstAttach'] = $files[0]['FileId'];
				}
			}
		}

		if ($ArticleId) {
			$table->doUpdate($data, $ArticleId);
			
			$this->log(array(
					'type' => 'update',
					'message' => '修改文章，Id: '.$ArticleId
					));
		} else {
			$data['AttachHash'] = $p['attach_hash'];
			$table->insert($data);
			
			$this->log(array(
					'type' => 'insert',
					'message' => '新增文章，标题: '.$Title
					));
			
			$this->sess->set('new_article_hash');
		}
		
		$this->redirect($this->scriptUrl.'article');
	}
	
	public function editAction()
	{
		$this->view->headScript()->appendFile($this->baseUrl.'js/kindeditor/kindeditor-min.js');
		$this->view->headScript()->appendFile($this->baseUrl.'js/jquery/jquery.ajaxupload.js');
		
		$ArticleId = (int)$this->getRequest()->getParam('ArticleId', 0);
		$data = array(
				'OrderNo' => '9999'
				);
		$table = &Msd_Dao::table('article');
		
		if ($ArticleId) {
			$data = $table->get($ArticleId);
			$hash = $data['AttachHash'];
		} else {
			$hash = $this->sess->get('new_article_hash');
			if (!$hash) {
				$hash = sha1(uniqid(mt_rand()));
				$this->sess->set('new_article_hash', $hash);	
			}
			
			$data = array(
					'AttachHash' => $hash,
					'OrderNo' => '9999'
					);
		}
		
		$this->view->files = Msd_Files::GetByHash($hash);
		$this->view->data = $data;
	}
	
	public function indexAction()
	{
		$this->pager_init();

		$table = &Msd_Dao::table('user');
		 
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
				'message' => '浏览网站用户',
		));		
	}
}