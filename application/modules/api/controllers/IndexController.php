<?php

class Api_IndexController extends Msd_Controller_Api
{
	
	public function indexAction()
	{
		$this->output();
	}
	
	public function appInfoAction(){
		$this->xmlRoot = 'result';
		$this->output [$this->xmlRoot] = array (
				'versionCode' => '2',
				'versionName' => '1.0',
				'downloadUrl' => "http://10.0.0.4/Vendor.apk",
				'updateLog' => '更新了商家获取订单信息的功能'
		);
		$this->output ();
		exit ();
	}
}