<?php

class Msd_Member_Base
{
	protected $uid = '';
	protected $member = null;
	
	protected function __construct($uid)
	{
		$this->uid = $uid;
		$this->member = &Msd_Member::getInstance($uid);
	}
}