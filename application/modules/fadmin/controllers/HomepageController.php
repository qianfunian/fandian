<?php

class Fadmin_HomepageController extends Msd_Controller_Fadmin
{
	public function init()
	{
		parent::init();
	}
	
	public function doAction()
	{
		$cConfig = &Msd_Config::cityConfig();
		
		$p = &$_POST;
		$data = array();
		$table = &Msd_Dao::table('systemvars');
		
		$flag = false;
		for ($i=0;$i<=14;$i++) {
			if($p['img_url_'.$i]!='')
			{
				$flag = true;
			}
			$data['img_url_'.$i] = $p['img_url_'.$i];
			$data['link_url_'.$i] = $p['link_url_'.$i];	
		}

		if(!$flag)
		{
			echo "Banner广告轮播 图片不能为空！";
			exit;
		}
		$table->updateRVars(array(
			'DataValue' => serialize($data),
            'CityId' => $cConfig->city_id
			), 'nhomepage', $cConfig->root_region);
		
		$d = array(
			'enabled' => $p['enabled'],
			'content' => $p['content']	
			);

		$table->updateRVars(array(
			'DataValue' => serialize($d),
            'CityId' => $cConfig->city_id
			), 'close_anounce', $cConfig->root_region);
		
		$b = array(
				'url' => $p['top_banner_url'],
				'link' => $p['top_banner_link']
		);
		$table->updateRVars(array(
				'DataValue' => serialize($b),
                'CityId' => $cConfig->city_id
			), 'top_banner', $cConfig->root_region);
		
		$c = array(
			'url' => $p['scroll_banner_url'],
			'link' => $p['scroll_banner_link'],
			'close' => $p['scroll_banner_close']
			);

        $table->updateRVars(array(
            'DataValue' => serialize($c),
            'CityId' => $cConfig->city_id
        ), 'scroll_banner', $cConfig->root_region);
		
		$cacher = &Msd_Cache_Remote::getInstance();
		$cacher->set('site_index');		
		$cacher->set('nsystemvars');
		
		if (strlen($p['new_expressforce'])) {
			Msd_Dao::table('expressforce')->insert(array(
				'Force' => (int)$p['new_expressforce'],
				'AddUser' => $this->member['Username'],
				'RegionGuid' => $cConfig->root_region
				));
			
			$cacher->set('express_force');
		}
		
		Msd_Cache_Clear::vars();
		
		$this->redirect($this->scriptUrl.'homepage');
	}
	
	public function indexAction()
	{
		$table = &Msd_Dao::table('systemvars');
		$cConfig = &Msd_Config::cityConfig();
		
		$data = $table->getByRegion('nhomepage', $cConfig->root_region);
		if (!$data) {
			$table->insert(array(
					'DataKey' => 'nhomepage',
					'DataValue' => serialize(array()),
					'RegionGuid' => $cConfig->root_region,
					'CityId' => $cConfig->city_id
					));
		} else {
			$data = unserialize($data['DataValue']);
		}
		
		$this->view->data = $data;
		$this->view->express_force = Msd_Cache_Loader::ExpressForce();		
		
		$_vars = &Msd_Cache_Loader::Systemvars();

		$this->view->close_announce = unserialize($_vars['close_anounce']);
		$this->view->sb = unserialize($_vars['scroll_banner']);
		$this->view->tb = unserialize($_vars['top_banner']);
	}
	
}