<?php

require_once 'Zend/Soap/Client.php';

class Service_WcfController extends Msd_Controller_Service
{
	public function seqAction()
	{
		$r = Msd_Service_Wcf::factory('numbersequence')->OrderId();
		
		Msd_Debug::dump($r);exit;
	}
}