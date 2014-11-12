<?php

class Msd_View_Helper_Member extends Msd_View_Helper_Base
{
	/**
	 * 订单支付方式解析
	 * 
	 * @param unknown_type $payment
	 */
	public function PayName($payment)
	{
		switch ($payment) {
			case '1':
				$result = '网上支付';
				break;
			default:
				$result = '到付';
				break;
		}
	
		return $result;
	}
}