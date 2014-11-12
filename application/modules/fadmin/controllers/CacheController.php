<?php

class Fadmin_CacheController extends Msd_Controller_Fadmin
{
	public function init()
	{
		parent::init();
		
		$this->auth('cache');
	}
	
	public function indexAction()
	{
	}
	
	public function clearallAction()
	{
		Msd_Cache_Clear::vars();
		Msd_Cache_Clear::config();
		Msd_Cache_Clear::Enums();
		Msd_Cache_Clear::ApiKeys();
		Msd_Cache_Clear::Regions();
		Msd_Cache_Clear::vendorCategories();
		Msd_Cache_Clear::categories();
		Msd_Cache_Clear::Items();
		Msd_Cache_Clear::Vendors();
		Msd_Cache_Clear::OrderStatus();
		Msd_Cache_Clear::index();
		Msd_Cache_Clear::ServerCategories();
		Msd_Cache_Clear::Sitemap();
		Msd_Cache_Clear::ics();
		Msd_Cache_Clear::vote();
		Msd_Cache_Clear::ServiceGroup();

		Msd_Cache_Clear::Services();
		
		$this->redirect($this->scriptUrl.'cache');
	}
	
	public function clearAction()
	{
		$do = trim(urldecode($this->getRequest()->getParam('do')));
		
		switch ($do) {
			case 'services':
				Msd_Cache_Clear::Services();
			case 'service_group':
				Msd_Cache_Clear::ServiceGroup();
				Msd_Cache_Clear::Services();
				break;
			case 'votes':
				Msd_Cache_Clear::vote();
				break;
			case 'ics':
				Msd_Cache_Clear::ics();
				break;
			case 'enums':
				Msd_Cache_Clear::Enums();
				break;
			case 'vendor_category':
			case 'vendor_categories':
				Msd_Cache_Clear::vendorCategories();
				break;
			case 'categories':
				Msd_Cache_Clear::categories();
				break;
			case 'config':
				Msd_Cache_Clear::config();
				break;
			case 'items':
				Msd_Cache_Clear::Items();
				break;
			case 'vars':
				Msd_Cache_Clear::vars();
				break;
			case 'api':
				Msd_Cache_Clear::ApiKeys();
				break;
			case 'regions':
				Msd_Cache_Clear::Regions();
				break;
			case 'vendors':
				Msd_Cache_Clear::Vendors();
				break;
			case 'orderstatus':
				Msd_Cache_Clear::OrderStatus();
				break;
			case 'index':
				Msd_Cache_Clear::index();
				break;
			case 'server_categories':
				Msd_Cache_Clear::ServerCategories();
				break;
			case 'sitemap':
				Msd_Cache_Clear::Sitemap();
				break;
			case 'gift':
				Msd_Cache_Clear::Gift();
				break;
			case 'new_year':
				Msd_Cache_Clear::NewYear();
				break;
			case 'lsas':
				Msd_Cache_Clear::Lsas();
				break;
		}
		
		$this->redirect($this->scriptUrl.'cache');
	}
}

