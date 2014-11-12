<?php

/**
 * 公用服务基类
 * 
 * @author pang
 *
 */

class Msd_Controller_Service extends Msd_Controller
{
	protected $sess = null;
	protected $member = null;
	
	public function init()
	{
		$this->sess = &Msd_Session::getInstance();
		$this->member = &Msd_Member::getInstance($this->sess->uid);
		
		parent::init();
	}
}