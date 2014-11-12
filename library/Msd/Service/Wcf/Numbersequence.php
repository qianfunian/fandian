<?php

class Msd_Service_Wcf_Numbersequence extends Msd_Service_Wcf_Base
{
	protected static $instance = null;
	
	public function __construct()
	{
		$this->url = Msd_Config::cityConfig()->wcf->number_sequence->url.'?wsdl';
		parent::__construct();
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function OrderId()
	{
		$id = '';
		
		try {
			$params = array(
				'NumSeqName' => 'OrderId'	
				);
			$r = $this->soapClient->GetNewId($params);
			$id = $r->GetNewIdResult;
			Msd_Log::getInstance()->wcf('GetNewIdResult:'.$id);
		} catch (Exception $e) {
			Msd_Log::getInstance()->wcf($e->getTraceAsString()."\n".$e->getMessage());
		}
		
		return $id;
	}
	
	public function CustomId()
	{
		$id = '';
		
		try {
			$params = array(
				'NumSeqName' => 'CustId'	
				);
			$r = $this->soapClient->GetNewId($params);
			$id = $r->GetNewIdResult;
			Msd_Log::getInstance()->wcf($id);
		} catch (Exception $e) {
			Msd_Log::getInstance()->wcf('GetNewIdResult:'.$e->getTraceAsString()."\n".$e->getMessage());
		}
		
		return $id;
	}
}