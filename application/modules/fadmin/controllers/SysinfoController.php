<?php

class Fadmin_SysinfoController extends Msd_Controller_Fadmin
{
	public function init()
	{
		parent::init();
		
		$this->auth('sysinfo');
	}
	
	public function indexAction()
	{
		$this->view->data = Msd_Cache_Loader::Systemvars();
	}
	
	public function doAction()
	{
		$p = $_POST;
		unset($p['go']);
		
		$table = Msd_Dao::table('systemvars');
		foreach ($p as $key=>$value) {
			$params = array(
					'DataKey' => $key, 
					'DataValue' => $value,
					'LastUpdate' => date('Y-m-d H:i:s', time()),
					'RegionGuid' => Msd_Config::cityConfig()->root_region,
					'CityId' => Msd_Config::cityConfig()->city_id
					);
			$where = array(
					'DataKey' => $key,
					'RegionGuid' => Msd_Config::cityConfig()->root_region
					);
			$table->replace($params, $where);
		}
		
		Msd_Cache_Clear::vars();
		
		$this->redirect($this->scriptUrl.'sysinfo');
	}
}

