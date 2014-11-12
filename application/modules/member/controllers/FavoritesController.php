<?php


class Member_FavoritesController extends Msd_Controller_Member
{
	protected $handler = null;
	protected $all = array();
	
	public function init()
	{
		parent::init();
		
		$this->AuthRedirect();
	}
	
	public function indexAction()
	{
	}
	
	public function additemAction()
	{
		$ItemGuid = trim(urldecode($this->getRequest()->getParam('ItemGuid', '')));
		if (Msd_Validator::isGuid($ItemGuid)) {
			$table = &Msd_Dao::table('favorited/items');
			$favorited = $table->isFavorited($ItemGuid, $this->member->uid());
			if ($favorited) {
				throw new Msd_Exception('参数错误');
			} else {
				$table->insert(array(
					'ItemGuid' => $ItemGuid,
					'CustGuid' => $this->member->uid()
				));
		
				$this->ajaxOutput();
			}
		} else {
			throw new Msd_Exception('参数错误');
		}		
	}
	
	public function itemsAction()
	{
		$this->pager_init();
		
		$table = &Msd_Dao::table('favorited/items');

		$rows = $table->mine($this->pager, $this->member->uid());
		
		$this->view->rows = $rows;
		$this->view->page_links = $this->page_links($this);
		$this->view->data = array();
		$this->view->request = $_REQUEST;		
	}
	
	public function vendorsAction()
	{
		$this->pager_init();
		
		$table = &Msd_Dao::table('favorited/vendors');
			
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
	
	public function addvendorAction()
	{
		$VendorGuid = trim(urldecode($this->getRequest()->getParam('VendorGuid', '')));
		if (Msd_Validator::isGuid($VendorGuid)) {
			$table = &Msd_Dao::table('favorited/vendors');
			
			$favorited = $table->isFavorited($VendorGuid, $this->member->uid());
			
			if ($favorited) {
				throw new Msd_Exception('参数错误');
			} else {
				$cityConfig = &Msd_Config::cityConfig();
				$table->insert(array(
						'VendorGuid' => $VendorGuid,
						'CustGuid' => $this->member->uid(),
						'CityId' =>$cityConfig->city_id
						));
				Msd_Dao::table('vendor/extend')->increase('Favorites', $VendorGuid);
				
				$this->ajaxOutput();
			}
		} else {
			throw new Msd_Exception('参数错误');
		}
	}
	
	public function delvendorAction()
	{
		$VendorGuid = trim(urldecode($this->getRequest()->getParam('VendorGuid', '')));
		if (Msd_Validator::isGuid($VendorGuid)) {
			$table = &Msd_Dao::table('favorited/vendors');
			$favorited = $table->isFavorited($VendorGuid, $this->member->uid());
			if (!$favorited) {
				throw new Msd_Exception('参数错误');
			} else {
				$table->doDelete($favorited);
				Msd_Dao::table('vendor/extend')->decrease('Favorites', $VendorGuid);
				$this->_forward('vendors');
			}
		} else {
			throw new Msd_Exception('参数错误');
		}
	}
	
	public function delitemAction()
	{
		$ItemGuid = trim(urldecode($this->getRequest()->getParam('ItemGuid', '')));
		if (Msd_Validator::isGuid($ItemGuid)) {
			$table = &Msd_Dao::table('favorited/items');
			$favorited = $table->isFavorited($ItemGuid, $this->member->uid());
			if (!$favorited) {
				throw new Msd_Exception('参数错误');
			} else {
				$table->doDelete($favorited);
				$this->ajaxOutput();
			}
		} else {
			throw new Msd_Exception('参数错误');
		}
	}
}