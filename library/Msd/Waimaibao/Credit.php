<?php

class Msd_Waimaibao_Credit
{
	public static $categories = array();
	public static $data = array();
	
	public static function &Categories()
	{
		if (!self::$categories) {
			$config = &Msd_Config::cityConfig();
			$cs = explode('|', $config->credit->categories);
			foreach ($cs as $v) {
				list($tid, $tname) = explode(':', $v);
				self::$categories[$tid] = $tname;
			}
		}
		
		return self::$categories;
	}
	
	public static function &get($id)
	{
		if (!isset(self::$data[$id])) {
			$data = array(
				'basic' => array(),
				'extend' => array()	
				);
			
			if ($id) {
				$basic = &Msd_Dao::table('article')->get($id);
				if ($basic['ArticleId']) {
					$extend = &Msd_Dao::table('article/credit')->get($id);
					if ($extend['ArticleId']) {
						$data['basic'] = &$basic;
						$data['extend'] = &$extend;
					}
				}
			}
			
			self::$data[$id] = &$data;
		}
		
		return self::$data[$id];
	}
	
	public static function exchange($id, array $params)
	{
		$result = array(
				'message' => '兑换失败！',
				'success' => 0
				);
		
		$data = self::get($id);
		if ($data['extend']['Remains']<=0) {
			$result['message'] = '兑换失败，该物品已经被兑换完了。';
		} else {
			$_params = array(
				'Remains' => $data['extend']['Remains']-1	
				);
			Msd_Dao::table('article/credit')->doUpdate($_params, $id);
			self::$data[$id]['extend']['Remains']--;
			$data['extend']['Remains']--;
			
			$Phone = $params['Phone'];
			$Address = $params['Address'];
			$Contactor = $params['Contactor'];
			Msd_Dao::table('creditlogs')->insert(array(
				'ArticleId' => $id,
				'CellPhone' => $Phone,
				'Address' => $Address,
				'Contactor' => $Contactor
				));
			
			$result['message'] = '兑换成功！';
			$result['success'] = 1;
		
		}
		
		return $result;
	}
}