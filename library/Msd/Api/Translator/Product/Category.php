<?php

class Msd_Api_Translator_Product_Category extends Msd_Api_Translator_Base
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
		if ($params['CtgGuid']) {
			$result['code'] = $params['CtgGuid'];
			$result['name'] = $params['CtgName'];
			$result['products'] = array();

			$t = &Msd_Api_Translator::getInstance()->t('product');
			foreach ($params['items'] as $item) {
				$result['products'][] = array(
					'product' => $t->translate($item)
					);
			}
		}
		
		return $result;
	}
}