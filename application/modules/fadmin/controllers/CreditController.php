<?php

class Fadmin_CreditController extends Msd_Controller_Fadmin
{
	protected $Categories = array();
	
	public function init()
	{
		parent::init();
		
		$this->auth('credit');
	}
	
	public function delAction()
	{
		$ArticleId = (int)$this->getRequest()->getParam('ArticleId', 0);
		$table = &Msd_Dao::table('credit');
		$eTable = &Msd_Dao::table('article/credit');
		
		if ($ArticleId) {
			$data = $table->get($ArticleId);
			$hash = $data['AttachHash'];
			
			$files = Msd_Files::GetByHash($hash);
			foreach ($files as $file) {
				Msd_Files::Del($file['FileId']);
			}
			
			$table->doDelete($ArticleId);
			$eTable->doDelete($ArticleId);
			
			Msd_Hook::run('ArticleDeleted', array(
					'id' => $ArticleId
					));
			
			$this->log(array(
					'type'  => 'delete',
					'message' => '删除积分兑换, ArticleId: '.$ArticleId
					));
		} else {
			throw new Msd_Exception('参数不正确');;
		}	
		
		$this->redirect($this->scriptUrl.'credit');
	}
	
	public function doeditAction()
	{
		$ArticleId = (int)$this->getRequest()->getParam('ArticleId', 0);
		
		$Title = trim($this->getRequest()->getParam('Title', ''));
		$p = &$_POST;
		$error = array();
		
		if ($Title=='') {
			$error['Title'] = '请填写积分兑换标题';
		}

		if (count($error)>0) {
			$this->view->error = $error;
			$this->editAction();
			$this->view->data = $p;
			
			echo $this->view->render('credit/edit.phtml');
			exit(0);
		}
		
		$table = &Msd_Dao::table('credit');
		$eTable = &Msd_Dao::table('article/credit');
		$data = array(
				'Title' => $Title,
				'Detail' => $p['Detail'],
				'OrderNo' => $p['OrderNo'],
				'Views' => 0,
				'PubFlag' => $p['PubFlag'] ? '1' : '0',
				'FirstAttach' => '',
				'PubTime' => $p['PubTime'].' 00:00:00.000'
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
			$e = array(
				'Credit' => (int)$p['Credit'],
				'Remains' => (int)$p['Remains'],
				'Total' => (int)$p['Total']	,
				'Category' => (int)$p['Category']
				);
			$eTable->doUpdate($e, $ArticleId);
			
			$this->log(array(
					'type' => 'update',
					'message' => '修改积分兑换，Id: '.$ArticleId
					));
		} else {
			$data['CategoryId'] = Msd_Config::cityConfig()->db->article->category->credit;
			$data['AttachHash'] = $p['attach_hash'];
			$ArticleId = $table->insert($data);

			$e = array(
				'ArticleId' => $ArticleId,
				'Credit' => (int)$p['Credit'],
				'Remains' => (int)$p['Remains'],
				'Total' => (int)$p['Total']	,
				'Category' => (int)$p['Category']
				);

			$eTable->insert($e);
			
			$this->log(array(
					'type' => 'insert',
					'message' => '新增积分兑换，标题: '.$Title
					));
			
			$this->sess->set('new_article_hash');
		}
		
		Msd_Hook::run('ArticleChanged', array(
				'id' => $ArticleId,
				'data' => $data,
				));
		
		$this->redirect($this->scriptUrl.'credit');
	}
	
	public function editAction()
	{
		$this->view->headScript()->appendFile($this->baseUrl.'js/kindeditor/kindeditor-min.js');
		$this->view->headScript()->appendFile($this->baseUrl.'js/jquery/jquery.ajaxupload.js');
		
		$ArticleId = (int)$this->getRequest()->getParam('ArticleId', 0);
		$data = array(
				'OrderNo' => '9999',
				'Credit' => '1000'
				);
		$table = &Msd_Dao::table('credit');
		$eTable = &Msd_Dao::table('article/credit');
		
		if ($ArticleId) {
			$data = $table->get($ArticleId);
			$hash = $data['AttachHash'];
			$data['ext'] = $eTable->get($ArticleId);
		} else {
			$hash = $this->sess->get('new_article_hash');
			if (!$hash) {
				$hash = sha1(uniqid(rand()));
				$this->sess->set('new_article_hash', $hash);	
			}
			
			$data = array(
					'AttachHash' => $hash,
					'OrderNo' => '9999',
					'PubTime' => date('Y-m-d H:i:s.u'),
					'Credit' => '1000'
					);
		}
		
		$this->view->files = Msd_Files::GetByHash($hash);
		$this->view->data = $data;
	}
	
	public function indexAction()
	{
		$this->pager_init();

		$table = &Msd_Dao::table('credit');
		 
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
				'message' => '浏览积分兑换',
			));		
	}
	
	protected function getCategories()
	{
		$this->view->Categories = $this->Categories = &Msd_Cache_Loader::categories();
	}
}