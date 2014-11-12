<?php

class Msd_Api_Translator_Product extends Msd_Api_Translator_Base
{
	protected static $instance = null;

	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	public function translate(array $params)
	{
		$result = array();

		if ($params['ItemGuid']) {
			$result['code'] = $params['ItemGuid'];
			$result['name'] = $params['ItemName'];
			$result['price'] = $params['UnitPrice'] ? $params['UnitPrice'] : ($params['ItemPrice'] ? $params['ItemPrice'] : '');
			$result['description'] = $params['Remark'];
			$result['image_url'] = Msd_Waimaibao_Item::imageUrl($params);
			$result['packages'] = $params['BoxQty'];
			$result['package_price'] = $params['BoxUnitPrice'];
		}
		
		return $result;
	}
}