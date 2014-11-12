<?php

class Msd_Service_Wcf_Order extends Msd_Service_Wcf_Base
{
	protected static $instance = null;
	
	public function __construct()
	{
		$this->url = Msd_Config::cityConfig()->wcf->order->url.'?wsdl';
		parent::__construct();
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function RegisterUnconfirmWebOrder($OrderId)
	{
		$result = false;
		
		try {
			$params = array(
				'OrderId' => $OrderId
				);
			$r = $this->soapClient->RegisterUnconfirmWebOrder($params);
			Msd_Log::getInstance()->wcf('OrderId:'.$OrderId.',RegisterUnconfirmWebOrderResult:'.$r->RegisterUnconfirmWebOrderResult);
			$result = true;
		} catch (Exception $e) {
			Msd_Log::getInstance()->wcf('OrderId:'.$OrderId."\n".$e->getTraceAsString()."\n".$e->getMessage());
		}
		
		return $result;
	}
}