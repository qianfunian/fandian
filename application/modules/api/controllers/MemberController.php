<?php

class Api_MemberController extends Msd_Controller_Api
{
	public function init()
	{
		parent::init();
	}
	
	public function avatarAction()
	{
		$this->auth(true);
		$this->xmlRoot = 'message';
		
		if (is_array($_FILES) && isset($_FILES['avatar'])) {
			$_FILES['myfile'] = &$_FILES['avatar'];

			$hash = sha1(uniqid(mt_rand()));
			$result = Msd_Uploader::Save(array(
					'file' => 'avatar',
					'hash' => $hash,
					'usage' => 'avatar'
				));
			
			if ($result['file_id']) {
				$toUpdate = array();
				$toUpdate['Avatar'] = $hash;
				$toUpdate['file'] = $_FILES['Avatar'];
				$toUpdate['AvatarId'] = $result['file_id'];
				
				$this->member->update($toUpdate);
				$this->message('头像上传成功');
			} else {
				$this->error('error.member.avatar_save_failed');
			}
		} else {
			$this->error('error.member.avatar.invalid_file');
		}
		
		$this->output();		
	}
	
	public function myordersAction()
	{
		$this->auth(true);
		$this->xmlRoot = 'result';

		$page = (int)$this->getRequest()->getParam('page', 1);
		$pageSize = (int)$this->getRequest()->getParam('page_size', 10);
		
		$this->pager_init(array(
			'limit' => $pageSize
			));
		
		$table = &Msd_Dao::table('order');
			
		$params = $sort = array();
		$params['CustGuid'] = $this->uid;
		
		$t = &$this->t('order_concise');
		$rows = $table->search($this->pager, $params, $sort);
		foreach ($rows as $row) {
			$d = Msd_Waimaibao_Order::detail($row['OrderGuid']);
			$this->output[$this->xmlRoot]['orders'][] = array(
				'order_concise' => $t->translate($d)	
				);
		}
		
		$this->output[$this->xmlRoot]['summary'] = array(
			'page' => $page,
			'page_size' => $pageSize,
			'total_pages' => $this->pages(),
			'total_rows' => $this->pager['total']
			);
		
		$this->output();
	}
	
	public function bindiphoneAction()
	{
		$this->auth();
		$this->xmlRoot = 'message';
		$this->needPost();
		
		$token = trim($this->getRequest()->getParam('token', ''));
		$allow = trim($this->getRequest()->getParam('allow', ''));
		
		switch ($allow) {
			case 'true':
				if ($token) {
					if (Better_Phone_Apple::bind($this->uid, $token, 8, $polo)) {
						$this->data[$this->xmlRoot] = $this->lang->bindiphone->success;
					} else {
						$this->serverError();
					}
				} else {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.profile.bindiphone.invalid_token');
				}
				break;
			case 'false':
			case 'off':
				if (Better_Phone_Apple::unbind($this->uid, $token)) {
					$this->data[$this->xmlRoot] = $this->lang->unbindiphone->success;
				} else {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->serverError();
				}
				break;
			default:
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.profile.bindiphone.invalid_allow_param');
				break;
		}
		
		$this->output();		
	}
	
	/**
	 * 登录验证
	 * 
	 */
	public function authAction()
	{
		$this->auth(true);
		$this->xmlRoot = 'member';
		
		if ($this->uid) {
			$data = $this->member->extend();
			
			$data['CustGuid'] = $this->uid;
			$data['UserName'] = $this->member->Username;
			$data['Address'] = $this->member->Address;
			$data['Avatar'] = $this->view->Avatar($this->member->Avatar, $this->staticUrl);

			$this->output[$this->xmlRoot] = $this->t('member')->translate($data);
		}
		
		$this->output();
	}
	
	/**
	 * 地址簿列表
	 * 
	 */
	public function addressbookAction()
	{
		$this->auth(true);
		$this->xmlRoot = 'addressbooks';
		
		$cTable = &Msd_Dao::table('coordinate');
		$handler = &Msd_Member_Addressbook::getInstance($this->uid);
		$rows = &$handler->all();
		foreach ($rows as $row) {
			$pm = array();
			if ($row['CoordGuid']) {
				$pm = &$cTable->cget($row['CoordGuid']);
			}
			$row['pm'] = $pm;
			
			$this->output[$this->xmlRoot][] = array(
				'addressbook' => $this->t('addressbook')->translate($row)	
				);
		}
		
		$this->output();
	}
	
	/**
	 * 新建地址簿
	 * 
	 */
	public function newaddressbookAction()
	{
		$this->auth(true);
		$this->xmlRoot = 'addressbook';
		
		$handler = &Msd_Member_Addressbook::getInstance($this->uid);
		$rows = &$handler->all();
		
		if (count($rows)>=5) {
			$this->error('error.member.addressbook_rows_limit');
		}
		
		$p = &$_POST;
		$name = trim($p['name']);
		$placemark = trim($p['placemark']);
		$address = trim($p['address']);
		$setDefault = $p['set_default']=='true' ? true : false;
		$contactor = trim($p['contactor']);
		$phone = trim($p['phone']);
		
		if ($name=='') {
			$this->error('error.member.addressbook_name_required');
		} else if ($address=='') {
			$this->error('error.member.addressbook_address_required');
		} else if ($contactor=='') {
			$this->error('error.member.addressbook_contactor_required');
		} else if ($phone=='') {
			$this->error('error.member.addressbook_phone_required');
		}
		
		$params = array(
			'Title' => $name,
			'IsDefault' => $setDefault,
			'Address' => $address,
			'Contactor' => $contactor,
			'Phone' => $phone,
			'OrderNo' => '9999'
			);
		Msd_Validator::isGuid($placemark) && $params['CoordGuid'] = $placemark;
		$id = $handler->add($params);
		
		if ($setDefault) {
			$handler->resetDefault($id, $params);
		}
		Msd_Validator::isGuid($params['CoordGuid']) && $params['pm'] = Msd_Dao::table('coordinate')->get($params['CoordGuid']);
		$this->output[$this->xmlRoot] = &$this->t('addressbook')->translate($params);
		
		$this->output();
	}
	
	/**
	 * 更新地址簿
	 * 
	 */
	public function modifyaddressbookAction()
	{
		$this->auth(true);
		$this->xmlRoot = 'addressbook';
		$id = trim(urldecode(trim($this->getRequest()->getParam('id', ''))));
		$name = trim(urldecode(trim($this->getRequest()->getParam('name', ''))));
		$placemark = trim(urldecode(trim($this->getRequest()->getParam('placemark', ''))));
		$address = trim(urldecode(trim($this->getRequest()->getParam('address', ''))));
		$setDefault = trim(urldecode(trim($this->getRequest()->getParam('set_default', ''))));
		$contactor = trim(urldecode(trim($this->getRequest()->getParam('contactor', ''))));
		$phone = trim(urldecode(trim($this->getRequest()->getParam('phone', ''))));
		
		if (!$id) {
			$this->error('error.member.addressbook_id_required');
		}
		
		$params = array();
		strlen($name) && $params['Title'] = $name;
		Msd_Validator::isGuid($placemark) && $params['CoordGuid'] = $placemark;
		strlen($address) && $params['Address'] = $address;
		strlen($setDefault) && $params['IsDefault'] = $setDefault;
		strlen($contactor) && $params['Contactor'] = $contactor;
		strlen($phone) && $params['Phone'] = $phone;
		
		if (count($params)>0) {
			$handler = &Msd_Member_Addressbook::getInstance($this->uid);
			$handler->update($params, $id);
			
			if ($setDefault) {
				$handler->resetDefault($id, $params);
			}
			
			$params = $handler->get($id);
			Msd_Validator::isGuid($params['CoordGuid']) && $params['pm'] = Msd_Dao::table('coordinate')->get($params['CoordGuid']);
			$this->output[$this->xmlRoot] = &$this->t('addressbook')->translate($params);
			
			$this->output();
		} else {
			$this->error('error.member.addressbook_parameters_not_valid');
		}
	}
	
	/**
	 * 删除地址簿
	 * 
	 */
	public function removeaddressbookAction()
	{
		$this->auth(true);

		$this->xmlRoot = 'message';
		$id = trim(urldecode(trim($this->getRequest()->getParam('id', ''))));
		$handler = &Msd_Member_Addressbook::getInstance($this->uid);
		$handler->delete($id);
		
		$this->message('地址簿删除成功');
	}
	
	/**
	 * 修改密码
	 * 
	 */
	public function passwordAction()
	{
		$this->auth(true);
		
		$p = &$_POST;
		$password = $p['password'];
		$repassword = $p['repassword'];
		
		$PassWordValidate = Msd_Member_Validator::password($password, $repassword);
		if ($PassWordValidate['result']==$PassWordValidate['codes']['PASSWORD_NOT_VALID']) {
			$this->error('error.member.password_not_valid');
		} else if ($PassWordValidate['result']==$PassWordValidate['codes']['PASSWORD_NOT_MATCH']) {
			$this->error('error.member.passwords_not_match');
		}
		
		$todo = array(
			'PassWord' => $password	
			);
		$this->member->update($todo);
		$this->message('密码修改成功');
	}
	
	/**
	 * 更新个人资料
	 * 
	 */
	public function modifyAction()
	{
		$this->auth(true);
		$this->xmlRoot = 'member';
		
		$p = &$_POST;
	
		$realname  = trim($p['realname']);
		$cellphone = trim($p['cellphone']);
		$email     = trim($p['email']);
		
		$todo = array();
		if(isset($email) && !empty($email))
		{
			$EmailValidate = Msd_Member_Validator::email($email, $this->uid);
			if ($EmailValidate['result']==$EmailValidate['codes']['EMAIL_NOT_VALID']) {
				$this->error('error.member.email_not_valid');
			} else if ($EmailValidate['result']==$EmailValidate['codes']['EMAIL_EXISTS']) {
				$this->error('error.member.email_exists');
			}
			$todo['Email'] = $email;
		}
		
		if(isset($cellphone) && !empty($cellphone))
		{
			$CellValidate = Msd_Member_Validator::cell($cellphone, $this->uid);
			if ($CellValidate['result']==$CellValidate['codes']['CELL_NOT_VALID']) {
				$this->error('error.member.cellphone_not_valid');
			} else if ($CellValidate['result']==$CellValidate['codes']['CELL_EXISTS']) {
				$this->error('error.member.cellphone_exists');
			}
			$todo['Cell'] = $cellphone;
		}
		
		if(isset($realname) && !empty($realname))
		{
			$RealNameValidator = Msd_Member_Validator::realname($realname);
			if ($RealNameValidator['result']==$RealNameValidator['codes']['REALNAME_NOT_VALID']) {
				$this->error('error.member.realname_not_valid');
			}
			$todo['RealName'] = $realname;
		}
		
		$this->member->update($todo);
		
		$member = $this->member->extend();
		$member = Msd_Functions::ArrayMerge($member, $todo);
		$member['CustGuid'] = $this->uid;
		$member['UserName'] = $this->member->UserName;
		
		$this->output[$this->xmlRoot] = $this->t('member')->translate($member);
		
		$this->output();
	}
	
	/**
	 * 用户注册
	 * 
	 */
	public function signupAction()
	{
		$this->xmlRoot = 'member';
		
		$p = &$_POST;
		$cConfig = &Msd_Config::cityConfig();
		
		$username = trim($p['username']);
		$password = trim($p['password']);
		$repassword = trim($p['repassword']);
		$realname = trim($p['realname']);
		$address = trim($p['address']);
		$cellphone = trim($p['cellphone']);
		$email = trim($p['email']);
		$placemark = trim($p['placemark']);
		$params = array();
		$params['Address'] = trim($p['address']);
		
		$UserNameValidate = Msd_Member_Validator::username($username);
		if ($UserNameValidate['result']==$UserNameValidate['codes']['USERNAME_NOT_VALID']) {
			$this->error('error.member.username_not_valid');
		} else if ($UserNameValidate['result']==$UserNameValidate['codes']['USERNAME_EXISTS']) {
			$this->error('error.member.username_exists');
		} else {
			$params['UserName'] = $username;
		}
		
		$PassWordValidate = Msd_Member_Validator::password($password, $repassword);
		if ($PassWordValidate['result']==$PassWordValidate['codes']['PASSWORD_NOT_VALID']) {
			$this->error('error.member.password_not_valid');
		} else if ($PassWordValidate['result']==$PassWordValidate['codes']['PASSWORD_NOT_MATCH']) {
			$this->error('error.member.passwords_not_match');
		} else {
			$params['PassWord'] = $password;
			$params['_PasswordSha1'] = true;
		}
		
		$EmailValidate = Msd_Member_Validator::email($email);
		if ($EmailValidate['result']==$EmailValidate['codes']['EMAIL_NOT_VALID']) {
			$this->error('error.member.email_not_valid');
		} else if ($EmailValidate['result']==$EmailValidate['codes']['EMAIL_EXISTS']) {
			$this->error('error.member.email_exists');
		} else {
			$params['Email'] = $email;
		}
		
		$CellValidate = Msd_Member_Validator::cell($cellphone);
		if ($CellValidate['result']==$CellValidate['codes']['CELL_NOT_VALID']) {
			$this->error('error.member.cellphone_not_valid');
		} else if ($CellValidate['result']==$CellValidate['codes']['CELL_EXISTS']) {
			$this->error('error.member.cellphone_exists');
		} else {
			$params['Cell'] = $cellphone;
		}
		
		$RealNameValidator = Msd_Member_Validator::realname($realname);
		if ($RealNameValidator['result']==$RealNameValidator['codes']['REALNAME_NOT_VALID']) {
			$this->error('error.member.realname_not_valid');
		} else {
			$params['RealName'] = $realname;
		}
		
		$result = &Msd_Member::create($params);
		$member = &$result['member'];

		if ($member instanceof Msd_Member && strlen(trim($member->uid()))) {
			$data = $member->extend();
			$data['CustGuid'] = $member->uid();
			$data['UserName'] = $member->Username;
			$data['Address'] = $member->Address;
			$data['Avatar'] = $this->view->Avatar($member->Avatar, $this->staticUrl);
			
			$this->output[$this->xmlRoot] = $this->t('member')->translate($data);
			
			if ($realname && $address) {
				$handler = Msd_Member_Addressbook::getInstance($member->uid());
				$params = array(
						'Title' => '默认地址簿',
						'IsDefault' => 1,
						'Address' => $address,
						'Contactor' => $realname,
						'Phone' => $cellphone,
						'OrderNo' => '9999',
						'CityId' => $cConfig->city_id
					);		
				Msd_Validator::isGuid($placemark) && $params['CoordGuid'] = $placemark;
		
				$id = $handler->add($params);
			}
			
			Msd_Hook::run('MemberLogin', array('uid' => $member->uid()));
		} else {
			$this->error('error.fatal_error');
		}
		
		$this->output();
	}
	
	/**
	 * 找回密码
	 * 
	 */
	public function forgotpasswordAction()
	{
		$email = trim($this->getRequest()->getParam('email', ''));
		if (Msd_Validator::isEmail($email)) {
			$user = Msd_Member::createInstance($email, 'email');
			if ($user->uid()) {
				$extend = $user->extend();
					
				$handler = &Msd_Member_Resetpwd::getInstance($user->uid());
				$hash = sha1(uniqid(mt_rand()));
				$handler->saveHash($hash);
					
				Msd_Hook::run('ResetpwdRequested', array(
				'Email' => $extend['Email'],
				'Content' => $this->view->render('resetpwd/content.phtml')
				));
			} else {
				$this->error('error.member.email_not_found');
			}
		} else {
			$this->error('error.member.email_not_valid');
		}
		
		$this->message('一封关于如何重置您密码的邮件一件发送到了指定邮箱。');
	}
	
	/**
	 * 用户资料校验
	 * 
	 */
	public function validateAction()
	{
		$this->xmlRoot = 'result';
		
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
		
		switch ($key) {
			case 'username':
			case 'cell':
			case 'email':
				$result = $this->validate($key, $val);
				break;
		}
		
		$this->output[$this->xmlRoot] = $result;
		$this->output();
	}
	
	protected function validate($key, $val, $uid=0)
	{
		$msg = '';
		$success = 0;
		
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
		}
		
		$result = array(
			'msg' => $msg,
			'success' => $success	
			);
		
		return $result;
	}
}
