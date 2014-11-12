<?php

class Msd_Api_Translator_Order extends Msd_Api_Translator_Base
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
		if ($params['order']) {
			$vendor = &$params['vendor'];

			$result['order_id'] = $params['order']['OrderGuid'];
			$result['display_order_id'] = $params['order']['OrderId'];
			$result['create_time'] = Msd_Functions::Dt($params['order']['AddTime']);
			$result['request_time'] = $params['order']['ReqTimeStart'] ? $param['sales']['ReqDate'].' '.substr($params['order']['ReqTimeStart'], 0, 5) : '';
			$result['vendor_concise'] = Msd_Api_Translator::getInstance()->t('vendor_concise')->translate($vendor);
			$result['address'] = $params['sales']['CustAddress'];
			$result['placemark'] = Msd_Api_Translator::getInstance()->t('placemark')->translate($params['coord']);;
			$result['contactor'] = $params['sales']['CustName'];
			$result['phone'] = trim($params['sales']['CallPhone']);
			$result['status'] = Msd_Waimaibao_Order_Status::publicStatusName($params['order']['StatusId']);
			$result['note'] = $params['order']['Remark'];
			$result['total_money'] = Msd_Format::money($params['order']['TotalAmount']);
			$result['total_product_money'] = Msd_Format::money($params['order']['ItemAmount']);
			$result['total_packages_money'] = Msd_Format::money($params['order']['BoxAmount']);
			$result['total_express_money'] = Msd_Format::money($params['order']['Freight']);
			$result['placemark'] = Msd_Api_Translator::getInstance()->t('placemark')->translate($params['coord']);
			$result['elements'] = array();
			foreach ($params['items'] as $item) {
				$result['elements'][] = array(
					'element' => array(
						'count' => $item['ItemPrice'] ? round($item['ItemAmount']/$item['ItemPrice'], 2) : 0,
						'product' => Msd_Api_Translator::getInstance()->t('product')->translate($item)	
						)	
					);
			}
		}
		
		return $result;
	}
}
