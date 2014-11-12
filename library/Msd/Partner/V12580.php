<?php

class Msd_Partner_V12580 extends Msd_Partner_Base
{
	/**
	 * 加密字符串
	 * 
	 * @param string $string
	 */
	public static function SignString($string)
	{
		$config = &Msd_Config::cityConfig();
		$signUrl = $config->partner->v12580->sign_url;
		$secret = $config->partner->v12580->csecret;

		Msd_Log::getInstance()->v12580("SignUrl: ".$signUrl."\nSecret:".$secret);
		
		$http = new Msd_Http_Client($signUrl, array());
		$http->setParameterGet(array(
				'string' => $string,
				'secret' => $secret
			));
		$result = trim(strip_tags(trim($http->request('GET')->getBody())));

		return $result;
	}
	
	/**
	 * 生成加密基础字符串
	 * 
	 * @param array $params
	 */
	public static function BuildSignBaseString(array $params)
	{
		$tmp = array();
		
		ksort($params);	//	该死的签名还得先按照参数字母顺序
		foreach ($params as $key=>$val) {
			$tmp[] = $key.'='.$val;
		}
		$string = implode('&', $tmp);		
		
		return $string;
	}
	
	/**
	 * 退款
	 * 
	 * @param unknown_type $OrderGuid
	 */
	protected static function RefundMoney($OrderGuid)
	{
		$firstVersion = Msd_Dao::table('order/version')->firstVersion($OrderGuid);
		
		return $firstVersion['TotalAmount'];
	}
	
	/**
	 * 推送订单更新到12580
	 * 
	 * @param string $OrderId
	 * @param string $OrderGuid
	 * @param string $LastPush
	 */
	public static function PushUpdate($OrderId, $OrderGuid, $LastPush)
	{
		$config = &Msd_Config::appConfig();
		$cityConfig = &Msd_Config::cityConfig();
		$city = $cityConfig->partner->v12580->city_code;
		
		$lTable = &Msd_Dao::table('partner/v12580pushlog');
		$oTable = &Msd_Dao::table('order');
		$ovTable = &Msd_Dao::table('order/version');
		$sTable = &Msd_Dao::table('sales');
		$ocTable = &Msd_Dao::table('order/cancel');
		
		$LastPush = $lTable->get($OrderId);
		$o = $oTable->get($OrderGuid);
		if ($o) {
			$OrderVersionId = (int)$o['VersionId'];
			
			$bov = $bsv = $bf = array();
			if ($OrderVersionId>0) {
				$bov = $ovTable->getBefore($OrderGuid, $OrderVersionId);
				
				$bsv = $sTable->get($bov['SalesVerGuid']);
				$bf = $fTable->get($bov['FrtVerGuid']);
			} else {
				$bov = $ov;
				$bsv = $sv;
				$bf = $f;
			}
			
			$remark = '';
			$StatusId = $ov['StatusId'];
			if ($StatusId==$config->order->status->delivered) {
				$deliveryStatus = '3';
			} else if (substr($StatusId, 0, 6)=='Cancel') {
				$deliveryStatus = '2';
				$cr = $ocTable->getCancelRemark($OrderGuid);
				$remark = $cr['Reason'].($cr['Remark'] ? ', 备注：'.$cr['Remark'] : '');
			} else {
				//	其他状态不再做判断
				$deliveryStatus = '1';
			}
			
			$ckey = 'vendor';
			$pushType = 'send_status';
			$money = '';
			$increment = '';
			$decrease = '';
			$is_refund = '0';
			$refund_amount = '';
			
			if ($deliveryStatus!='2') {
				if ($ov['TotalAmount']>$bov['TotalAmount']) {
					$increment = $ov['TotalAmount'] - $bov['TotalAmount'];
				} else if ($ov['TotalAmount']<$bov['TotalAmount']) {
					$decrease = $bov['TotalAmount'] - $ov['TotalAmount'];
					$refund_amount = $decrease;
				}
			} else {
				//	配送失败
				$decrease = $ov['TotalAmount'];
				$is_refund = '1';
				$refund_amount = self::RefundMoney($OrderGuid);
			}
			
			list($usec, $sec) = explode(' ', microtime());
			$timestamp = (gmmktime()*100).((int)($usec*100));
			
			$PostParams = array(
				'ckey' => $ckey,																//	客户端关键字
				'city' => $city,																	//	所属城市
				'order_id' => $OrderId,													//	12580订单号
				'delivery_status' => $deliveryStatus,								//	配送状态
				'type' => $pushType,														//	同步状态
				'nonce' => rand(100000, 999999),								//	随机数
				'timestamp' => $timestamp,											//	UTC时间戳，毫秒级,
				'increment' => (string)$increment,									//	配送过程中增加的金额
				'decrease' => (string)$decrease,									//	配送过程中减少的金额
				'money' => $money,														//	退款金额
				'is_refund' => $is_refund,												//	是否退款
				'refund_amount' => (string)$refund_amount,				//	退款金额
				'remark' => self::wrapChinese($remark),						//	退款原因
				);
			$vParams = $PostParams;
			unset($vParams['nonce']);
			unset($vParams['timestamp']);

			$vStr = serialize($vParams);
			$dbvStr = $LastPush['LastPushData'];

			if ($vStr!=$dbvStr) {
				//	生成加密原始字符串
				$string = self::BuildSignBaseString($PostParams);
				$PostParams['signature'] = self::SignString($string);
	
				$pushUrl = $cityConfig->partner->v12580->push_url;
				$PostJson = json_encode($PostParams);
	
				Msd_Log::getInstance()->v12580("Callback JSON: \n".$PostJson);
				
				$result = '';
				try {
					if ($cityConfig->partner->v12580->callback_enabled) {
						$http = new Msd_Http_Client($pushUrl, array());
						$http->setRawData($PostJson, 'application/json');
						
						$response = $http->request('POST');
						$result = trim($response->getBody());
						$json = json_decode($result);
					} else {
						$json = '';
						$json->result = '1';
					}
					
					if ((int)$json->result==0) {
						//	更新日志
						$lTable->updateLastPushTime($OrderId, $vStr);
					}
					
					Msd_Log::getInstance()->v12580(var_export($PostParams, true)."\n--------------------------------------------------\n".$result);
				} catch (Exception $e) {
					Msd_Log::getInstance()->v12580($e->getMessage()."\n--------------------------------------------------\n".$e->getTraceAsString());
				}
			
			} else {
				$lTable->updateLastPushTime($OrderId);
			}
		}
	}
	
	/**
	 * 包装中文字符
	 * 
	 * @param string $string
	 */
	public static function wrapChinese($string)
	{
		return base64_encode($string);
	}
}