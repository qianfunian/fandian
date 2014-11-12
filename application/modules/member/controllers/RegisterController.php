<?php

class Member_RegisterController extends Msd_Controller_Member
{
	public function termAction()
	{
		
	}
	
	public function validateAction()
	{
		$key = strtolower(trim(urldecode($this->getRequest()->getParam('key'))));
		$val = trim(urldecode($this->getRequest()->getParam('val')));
		$msg = '未知错误';
		$success = 0;
		
		$charset = strtolower(mb_detect_encoding($val, array(
			'utf-8', 'gbk', 'ascii'	
			)));
		if ($charset=='gbk') {
			$charset = Msd_Iconv::g2u($val);
		}
		
		switch($key) {
			case 'username':
				$result = Msd_Member_Validator::username($val);
				switch ($result['result']) {
					case $result['codes']['USERNAME_EXISTS']:
						$msg = '用户名已经被注册了';
						break;
					case $result['codes']['USERNAME_NOT_VALID']:
						$msg = '用户名无效';
						break;
					case $result['codes']['SUCCESS'];
						$msg = '用户名可用';
						$success = 1;
						break;
				}
				break;
			case 'email':
				$result = Msd_Member_Validator::email($val);
				switch ($result['result']) {
					case $result['codes']['EMAIL_EXISTS']:
						$msg = 'Email已被注册了';
						break;
					case $result['codes']['EMAIL_NOT_VALID']:
						$msg = 'Email无效';
						break;
					case $result['codes']['SUCCESS'];
						$msg = 'Email可用';
						$success = 1;
						break;
				}
				break;
			case 'cell':
				$result = Msd_Member_Validator::cell($val);
				switch ($result['result']) {
					case $result['codes']['CELL_EXISTS']:
						$msg = '手机号已被注册了';
						break;
					case $result['codes']['CELL_NOT_VALID']:
						$msg = '手机号无效';
						break;
					case $result['codes']['SUCCESS']:
					case $result['codes']['CELL_NOT_EXISTS_BUT_ORDERED']:
						$msg = '手机号可用';
						$success = 1;
						break;
				}
				break;
			case 'captcha':
				$code = Msd_Session::getInstance()->get('captcha_code');
				if (strtolower($code)!=strtolower($val)) {
					$msg = '验证码不正确';
				} else {
					$msg = '验证码有效';
					$success = 1;
				}
				break;
		}
		
		$output = array(
			'msg' => $msg,
			'success' => $success	
			);
		$this->ajaxOutput($output);
	}

	public function fromSinaAction()
	{
		$request = &$_REQUEST;
		$uid = $this->member->uid();
		if (Msd_Validator::isGuid($uid)) {
			$this->_helper->getHelper('Redirector')->gotoUrl($this->scriptUrl);
			exit(0);
		}
		
		$weibo = $this->sess->get('weibo');
		//	新浪微博需要发起一次用户资料查询才可以获得用户的基本资料
		$wu = $this->sess->get('weibo_userinfo');
		
		if($wu==null)
		{
			$this->_helper->getHelper('Redirector')->gotoUrl($this->baseUrl."member/register");
			exit(0);
		}else
		{
			$username = trim($wu['domain']);
			if (!$username) {
				$username = trim($wu['screen_name']);
				if (!$username) {
					$username = trim($wu['name']);
				}
			}
			$request['UserName'] = $username;	
			$request['RealName'] = $wu['name'];
			isset($request['follow_fdw']) || $request['follow_fdw'] = '1';
			$this->view->request = $request;
		}
	}
	
	public function sinaDoAction()
	{
		$p = &$_POST;
		$error = array();
		
		$UserNameValidate = Msd_Member_Validator::username($p['UserName']);
		if ($UserNameValidate['result']==$UserNameValidate['codes']['USERNAME_NOT_VALID']) {
			$error['UserName'] = '请填写有效的用户名';
		} else if ($UserNameValidate['result']==$UserNameValidate['codes']['USERNAME_EXISTS']) {
			$error['UserName'] = '用户名已经被注册了';
		}
		
		$EmailValidate = Msd_Member_Validator::email($p['Email']);
		if ($EmailValidate['result']==$EmailValidate['codes']['EMAIL_NOT_VALID']) {
			$error['Email'] = '请填写有效的Email地址';
		} else if ($EmailValidate['result']==$EmailValidate['codes']['EMAIL_EXISTS']) {
			$error['Email'] = '这个Email已经被注册了';
		}
		
		$CellValidate = Msd_Member_Validator::cell($p['Cell']);
		if ($CellValidate['result']==$CellValidate['codes']['CELL_NOT_VALID']) {
			$error['Cell'] = '请填写有效的手机号码';
		} else if ($CellValidate['result']==$CellValidate['codes']['CELL_EXISTS']) {
			$error['Cell'] = '这个手机号码已经被注册了';
		}
		
		$RealNameValidator = Msd_Member_Validator::realname($p['RealName']);
		if ($RealNameValidator['result']==$RealNameValidator['codes']['REALNAME_NOT_VALID']) {
			$error['RealName'] = '请填写有效的真实姓名';
		}
		
		if (count($error)>0) {
			$this->view->request = $p;
			$this->view->error = $error;
				
			echo $this->view->render('register/from-sina.phtml');
			exit(0);
		} else {
			$randomstring = NULL;
			for ($i = 0; $i < 16; $i++)
			{
				$randomstring .= chr(mt_rand(32, 126)); //Range of ASCII characters
			}
			$p['randomstring'] = $randomstring;
			$this->view->randomstring = $randomstring;
			$p['welcome'] = $this->view->render('register/welcome.phtml');
			//	如果有第三方的Oauth Token
			$result = &Msd_Member::create($p);
			$member = &$result['member'];
			
			if ($member instanceof Msd_Member && strlen(trim($member->uid()))) {
				$this->sess->set('uid', $member->uid());
				Msd_Hook::run('MemberLogin', array('uid' => $member->uid()));
				
				if($_COOKIE['preurl']){
					$this->_helper->getHelper('Redirector')->gotoUrl(addslashes($_COOKIE['preurl']));
				}else{
					$this->redirect('register/success');
				}
			} else {
				$this->view->fatal_error = '注册失败!';
				$this->indexAction();
				echo $this->view->render('register/index.phtml');
				exit;
			}
		}
	}

	public function fromQqAction()
	{
		$request = &$_REQUEST;
		$uid = $this->member->uid();
		if (Msd_Validator::isGuid($uid)) {
			$this->_helper->getHelper('Redirector')->gotoUrl($this->scriptUrl);
			exit(0);
		}
		// QQ会一起返回用户的QQ昵称
		$tencent = $this->sess->get('tencent_connect');
		if($tencent==null)
		{
			$this->_helper->getHelper('Redirector')->gotoUrl($this->baseUrl."member/register");
			exit(0);
		}else
		{
			$request['UserName'] = $tencent['userinfo']['nickname'];
		}
		$this->view->request = $request;
	}

	
	public function qqDoAction()
	{
		$p = $this->getRequest()->getPost();
		$error = array();
	
		$UserNameValidate = Msd_Member_Validator::username($p['UserName']);
		if ($UserNameValidate['result']==$UserNameValidate['codes']['USERNAME_NOT_VALID']) {
			$error['UserName'] = '请填写有效的用户名';
		} else if ($UserNameValidate['result']==$UserNameValidate['codes']['USERNAME_EXISTS']) {
			$error['UserName'] = '用户名已经被注册了';
		}
	
		$EmailValidate = Msd_Member_Validator::email($p['Email']);
		if ($EmailValidate['result']==$EmailValidate['codes']['EMAIL_NOT_VALID']) {
			$error['Email'] = '请填写有效的Email地址';
		} else if ($EmailValidate['result']==$EmailValidate['codes']['EMAIL_EXISTS']) {
			$error['Email'] = '这个Email已经被注册了';
		}
	
		$CellValidate = Msd_Member_Validator::cell($p['Cell']);
		if ($CellValidate['result']==$CellValidate['codes']['CELL_NOT_VALID']) {
			$error['Cell'] = '请填写有效的手机号码';
		} else if ($CellValidate['result']==$CellValidate['codes']['CELL_EXISTS']) {
			$error['Cell'] = '这个手机号码已经被注册了';
		}
	
		$RealNameValidator = Msd_Member_Validator::realname($p['RealName']);
		if ($RealNameValidator['result']==$RealNameValidator['codes']['REALNAME_NOT_VALID']) {
			$error['RealName'] = '请填写有效的真实姓名';
		}
	
		if (count($error)>0) {
			$this->view->request = $p;
			$this->view->error = $error;
			echo $this->view->render('register/from-qq.phtml');
			exit(0);
		} else {
			$randomstring = NULL;
			for ($i = 0; $i < 16; $i++)
			{
				$randomstring .= chr(mt_rand(32, 126)); //Range of ASCII characters
			}
			$p['randomstring'] = $randomstring;
			$this->view->randomstring = $randomstring;
			$p['welcome'] = $this->view->render('register/welcome.phtml');
			
			//	如果有第三方的Oauth Token
			$result = &Msd_Member::create($p);
			$member = &$result['member'];
	
			if ($member instanceof Msd_Member && strlen(trim($member->uid()))) {
				$this->sess->set('uid', $member->uid());
				Msd_Hook::run('MemberLogin', array('uid' => $member->uid()));
				
				if($_COOKIE['preurl']){
					$this->_helper->getHelper('Redirector')->gotoUrl(addslashes($_COOKIE['preurl']));
				}else{
					$this->redirect('register/success');
				}
			} else {
				$this->view->fatal_error = '注册失败!';
				$this->indexAction();
				echo $this->view->render('register/from-qq.phtml');
				exit;
			}
		}
	}

	
	public function indexAction()
	{
		$request = &$_REQUEST;
		$uid = $this->member->uid();
		if (Msd_Validator::isGuid($uid)) {
			$this->_helper->getHelper('Redirector')->gotoUrl($this->scriptUrl);
			exit(0);
		}
	}
	
	public function doAction()
	{
		$p = $this->getRequest()->getPost();
		$error = array();
		
		$UserNameValidate = Msd_Member_Validator::username($p['UserName']);
		if ($UserNameValidate['result']==$UserNameValidate['codes']['USERNAME_NOT_VALID']) {
			$error['UserName'] = '请填写有效的用户名';
		} else if ($UserNameValidate['result']==$UserNameValidate['codes']['USERNAME_EXISTS']) {
			$error['UserName'] = '用户名已经被注册了';
		}
		
		$PassWordValidate = Msd_Member_Validator::password($p['PassWord'], $p['PassWord2']);
		if ($PassWordValidate['result']==$PassWordValidate['codes']['PASSWORD_NOT_VALID']) {
			$error['PassWord'] = '请填写有效的密码';
		} else if ($PassWordValidate['result']==$PassWordValidate['codes']['PASSWORD_NOT_MATCH']) {
			$error['PassWord2']= '两次输入的密码不一致';
		}
		
		$EmailValidate = Msd_Member_Validator::email($p['Email']);
		if ($EmailValidate['result']==$EmailValidate['codes']['EMAIL_NOT_VALID']) {
			$error['Email'] = '请填写有效的Email地址';
		} else if ($EmailValidate['result']==$EmailValidate['codes']['EMAIL_EXISTS']) {
			$error['Email'] = '这个Email已经被注册了';
		}
		
		$CellValidate = Msd_Member_Validator::cell($p['Cell']);
		if ($CellValidate['result']==$CellValidate['codes']['CELL_NOT_VALID']) {
			$error['Cell'] = '请填写有效的手机号码';
		} else if ($CellValidate['result']==$CellValidate['codes']['CELL_EXISTS']) {
			$error['Cell'] = '这个手机号码已经被注册了';
		}
		
		$RealNameValidator = Msd_Member_Validator::realname($p['RealName']);
		if ($RealNameValidator['result']==$RealNameValidator['codes']['REALNAME_NOT_VALID']) {
			$error['RealName'] = '请填写有效的真实姓名';
		}
		
		if (!Msd_Validator::captcha($p['Captcha'])) {
			$error['Captcha'] = '验证码不正确';
		}
		
		if (count($error)>0) {
			$this->view->request = $p;
			$this->view->error = $error;
			
			echo $this->view->render('register/index.phtml');
			exit(0);
		} else {
			$randomstring = NULL;
			for ($i = 0; $i < 16; $i++)
			{
				$randomstring .= chr(mt_rand(32, 126)); //Range of ASCII characters
			}
			$p['randomstring'] = $randomstring;
			$this->view->randomstring = $randomstring;
			$p['welcome'] = $this->view->render('register/welcome.phtml');
			
			$result = &Msd_Member::create($p);
			$member = &$result['member'];

			if ($member instanceof Msd_Member && strlen(trim($member->uid()))) {
				$this->sess->set('uid', $member->uid());
				Msd_Hook::run('MemberLogin', array('uid' => $member->uid()));
				
				if($_COOKIE['preurl']){
					$this->_helper->getHelper('Redirector')->gotoUrl(addslashes($_COOKIE['preurl']));
				}else{
					$this->redirect('register/success');
				}
			} else {
				$this->view->fatal_error = '注册失败!';
				$this->indexAction();
				echo $this->view->render('register/index.phtml');
				exit;
			}
		}
	}
	
	public function successAction()
	{
		$this->sess->set('tencent_data');
		$this->sess->set('weibo');
		
		$this->_helper->getHelper('Redirector')->gotoUrl($this->baseUrl);
		exit(0);
	}
	
	public function activeEmailAction()
	{
		$email  = $this->_request->getParam('email',null);
		$verify = $this->_request->getParam('verify',null);
		if($email == null || $verify == null)
		{
			$this->_helper->getHelper('Redirector')->gotoUrl($this->baseUrl);
		}else
		{
			$randomstring = NULL;
			for ($i = 0; $i < 16; $i++)
			{
				$randomstring .= chr(mt_rand(32, 126)); //Range of ASCII characters
			}
			
			$cTable = &Msd_Dao::table('customer');
			$row = $cTable->get($email,'Mail');
			$table = &Msd_Dao::table('user');
			
			$flag = $table->updateActive($randomstring,$row['CustGuid'],$verify);
			$this->view->flag = $flag;
		}
	}
	
	protected function parseTecentData()
	{
		
	}
	
	protected function parseWeiboData()
	{
		
	}
}

