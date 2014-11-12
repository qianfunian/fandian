<?php

class Msd_Api_Translator_Dispatcher extends Msd_Api_Translator_Base
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
		
		if ($params['OrderGuid']) {
			$config = &Msd_Config::appConfig();
			
			$at = new DateTime($params['AddTime']);
			$items = array();
			foreach ($params['items'] as $item) {
				$str = $item['ItemName'].'='.$item['ItemQty'].'='.$item['ItemAmount'];
				$str .= $item['ItemReq'] ? '='.$item['ItemReq'] : '';
				
				$items[] = $str;
			}
			$items[] = '运费='.($params['Distance']>1000 ? intval(($params['Distance']+500)/1000) : 1).'='.$params['Freight'];
			$items[] = '打包费='.$params['BoxQty'].'='.$params['BoxAmount'];
			
			$zt = 'new';
			if ($params['StatusId']==$config->order->status->assigned && $params['AcceptTime']) {
				$zt = 'confirm';
			} else if ($params['StatusId']==$config->order->status->received) {
				$zt = 'sending';
			} else if ($params['StatusId']==$config->order->status->delivered) {
				$zt = 'over';
			}
		
			$result['no'] = $params['OrderId'];
			$result['vip'] = $params['Category'] ? 'y' : 'n';
			$result['cz'] = $params['cz'];
			$result['zt'] = $zt;
			$result['sj'] = $params['VendorName'].' '.$params['CompletionTime'];
			$result['sum'] = Msd_Format::money($params['TotalAmount']);
			$result['time'] = trim($params['ReqTime'], ':');
			$result['xdsj'] = date('Y-m-d H:i:s', $at->getTimestamp());
			$result['to'] = $params['CoordName'].', '.$params['CustAddress'].', '.$params['CustName'];	
			$result['tel'] = $params['CallPhone'];
			$result['cai'] = implode('||', $items);
			$result['bz'] = $params['Remarks'];
		}
		
		return $result;
	}
}