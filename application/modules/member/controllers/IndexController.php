<?php

class Member_IndexController extends Msd_Controller_Member
{
	public function __call($method, $params)
	{
		$this->_helper->getHelper('Redirector')->gotoUrl($this->scriptUrl.'profile');
	}
}

