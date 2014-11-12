<?php

class Member_LoginController extends Msd_Controller_Member
{
	public function indexAction()
	{
		if ($this->member->uid()) {
			$this->_helper->getHelper('Redirector')->gotoUrl($this->baseUrl);
		}
	}
	
	public function logoutAction()
	{
		$this->sess->set('uid');
		
		$this->_helper->getHelper('Redirector')->gotoUrl($this->baseUrl);
	}
	
	public function isloginAction()
	{
		echo $this->member->uid()?'1':'0';
		exit;
	}
	
	public function doAction()
	{
		$passed = false;
		
		$UserName = $this->getRequest()->getPost('UserName');
		$PassWord = $this->getRequest()->getPost('PassWord');
		
		if ($UserName && $PassWord) {
			$user = &Msd_Member::createInstance($UserName, 'username');
			$DbPassWord = trim($user->Password);

			if ($user->uid() && (sha1($PassWord)==$DbPassWord || md5($PassWord)==$DbPassWord)) {
				$passed = true;
			}
			
			if (!$passed && Msd_Validator::isCell($UserName)) {
				$user = &Msd_Member::createInstance($UserName, 'cell');

				$DbPassWord = trim($user->Password);
				if ($user->uid() && (sha1($PassWord)==$DbPassWord || md5($PassWord)==$DbPassWord)) {
					$passed = true;
				}
			}

			if (!$passed && Msd_Validator::isEmail($UserName)) {
				$user = &Msd_Member::createInstance($UserName, 'email');
				$DbPassWord = trim($user->Password);
				if ($user->uid() && (sha1($PassWord)==$DbPassWord || md5($PassWord)==$DbPassWord)) {
					$passed = true;
				}
			}
		}
		
		if ($passed==true) {
			$this->sess->set('uid', $user->uid());
			Msd_Hook::run('MemberLogin', array('uid' => $user->uid()));
			$redirect = addslashes($_COOKIE['preurl'])?:$this->baseUrl;
			//$arr = explode('?',$redirect);
			$this->_helper->getHelper('Redirector')->gotoUrl($redirect);
		} else {
			$this->view->failed = true;
			
			echo $this->view->render('login/index.phtml');
			exit(0);
		}
	}
}

