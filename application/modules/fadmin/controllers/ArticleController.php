<?php

class Fadmin_ArticleController extends Msd_Controller_Fadmin
{
	protected $Categories = array();
	
	public function init()
	{
		parent::init();
		
		$this->auth('article');
		
		$this->getCategories();
	}
	
	public function delAction()
	{
		$ArticleId = (int)$this->getRequest()->getParam('ArticleId', 0);
		$table = &Msd_Dao::table('article');
		
		if ($ArticleId) {
			$data = $table->get($ArticleId);
			$hash = $data['AttachHash'];
			
			$files = Msd_Files::GetByHash($hash);
			foreach ($files as $file) {
				Msd_Files::Del($file['FileId']);
			}
			
			$table->doDelete($ArticleId);
			
			Msd_Hook::run('ArticleDeleted', array(
					'id' => $ArticleId
					));
			
			$this->log(array(
					'type'  => 'delete',
					'message' => '删除文章, ArticleId: '.$ArticleId
					));
		} else {
			throw new Msd_Exception('参数不正确');;
		}	
		
		$this->redirect($this->scriptUrl.'article');
	}
	
	public function doeditAction()
	{
		$cConfig = &Msd_Config::cityConfig();
		
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
				'Views' => (int)$p['Views'],
				'PubFlag' => $p['PubFlag'] ? '1' : '0',
				'FirstAttach' => '',
				'PubTime' => $p['PubTime'].' 00:00:00.000',
				'RegionGuid' => $p['RegionGuid']
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
			
			$cacheKey = 'article_views_'.$ArticleId;
			$cacher = &Msd_Cache_Remote::getInstance();
			$cdata = array(
				'views' => $data['Views']	
				);
			$cacher->set($cacheKey, $cdata);
			
			$this->log(array(
					'type' => 'update',
					'message' => '修改文章，Id: '.$ArticleId
					));
		} else {
			$data['AttachHash'] = $p['attach_hash'];
			$data['CityId'] = $cConfig->city_id;
			$ArticleId = $table->insert($data);
			
			$this->log(array(
					'type' => 'insert',
					'message' => '新增文章，标题: '.$Title
					));
			
			$this->sess->set('new_article_hash');
		}
		
		Msd_Hook::run('ArticleChanged', array(
				'id' => $ArticleId,
				'data' => $data,
				));
		
		$this->redirect($this->scriptUrl.'article');
	}
	
	public function editAction()
	{
		$cConfig = &Msd_Config::cityConfig();
		
		$this->view->headScript()->appendFile($this->baseUrl.'js/kindeditor/kindeditor-min.js');
		$this->view->headScript()->appendFile($this->baseUrl.'js/jquery/jquery.ajaxupload.js');

		$_Regions = &Msd_Cache_Loader::Regions();
		$this->view->Regions = array();
		foreach ($_Regions as $_region) {
			$this->view->Regions[$_region['RegionGuid']] = $_region['RegionName'];
		}

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
				$hash = sha1(uniqid(rand()));
				$this->sess->set('new_article_hash', $hash);	
			}
			
			$data = array(
					'AttachHash' => $hash,
					'OrderNo' => '9999',
					'PubTime' => date('Y-m-d H:i:s.u')
					);
		}
		
		$this->view->files = Msd_Files::GetByHash($hash);
		$this->view->data = $data;
	}
	
	public function indexAction()
	{
		$this->pager_init();

		$cConfig = &Msd_Config::cityConfig();
		
		$table = &Msd_Dao::table('article');
		 
		$params = $sort = array();
		$orderKey = trim($this->getRequest()->getParam('order_key', ''));
		$searchKey = trim(urldecode($this->getRequest()->getParam('search_key', '')));
		$searchVal = trim(urldecode($this->getRequest()->getParam('search_val', '')));
		$CategoryId = (int)$this->getRequest()->getParam('CategoryId', 0);
		 
		if (strlen($searchKey) && strlen($searchVal)) {
			$params[$searchKey] = $searchVal;
		}
		
		if ($CategoryId>0) {
			$params['CategoryId'] = (array)$CategoryId;
		}
		
		$params['Regions'] = Msd_Waimaibao_Region::RegionGuids();
		 
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
				'message' => '浏览文章',
			));		
	}
	
	protected function getCategories()
	{
		$this->view->Categories = $this->Categories = &Msd_Cache_Loader::categories();
	}
}