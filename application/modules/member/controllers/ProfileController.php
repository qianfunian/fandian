<?php

class Member_ProfileController extends Msd_Controller_Member
{
	public function indexAction()
	{
		$this->AuthRedirect();
		
		$member = $this->member->info();
		$extend = $this->member->extend();

		$this->view->success = $this->getRequest()->getParam('success', '0') ? true : false;
		$this->view->member = array(
				'UserName' => $member['Username'],
				'RealName' => $extend['RealName'],
				'Email' => $extend['Email'],
				'Cell' => $extend['Cell'],
				'Avatar' => $member['Avatar'],
				'Avatars' => $extend['Avatar'],
				'Qq' => $member['Qq'],
				'Address' => $member['Address'],
				'Msn' => $member['Msn'],
				'Homepage' => $member['Homepage']
		);		
	}
	
	public function doAction()
	{
		$this->AuthRedirect();
		
		$toUpdate = $error = array();
		$p = $this->getRequest()->getPost();
		$uid = $this->member->uid();
		$info = $this->member->info();
		$toUpdate['DeleteAvatar'] = (int)$p['DeleteAvatar'];
		
		if ($p['PassWord']) {
			$PassWordValidate = Msd_Member_Validator::password($p['PassWord'], $p['PassWord2']);
			if ($PassWordValidate['result']==$PassWordValidate['codes']['PASSWORD_NOT_VALID']) {
				$error['PassWord'] = '请填写有效的密码';
			} else if ($PassWordValidate['result']==$PassWordValidate['codes']['PASSWORD_NOT_MATCH']) {
				$error['PassWord2']= '两次输入的密码不一致';
			} else {
				$toUpdate['PassWord'] = $p['PassWord'];
			}
		}
		
		$EmailValidate = Msd_Member_Validator::email($p['Email'], $uid);
		if ($EmailValidate['result']==$EmailValidate['codes']['EMAIL_NOT_VALID']) {
			$error['Email'] = '请填写有效的Email地址';
		} else if ($EmailValidate['result']==$EmailValidate['codes']['EMAIL_EXISTS']) {
			$error['Email'] = '这个Email已经被注册了';
		} else {
			$toUpdate['Email'] = $p['Email'];
		}
		
		$CellValidate = Msd_Member_Validator::cell($p['Cell'], $uid);
		if ($CellValidate['result']==$CellValidate['codes']['CELL_NOT_VALID']) {
			$error['Cell'] = '请填写有效的手机号码';
		} else if ($CellValidate['result']==$CellValidate['codes']['CELL_EXISTS']) {
			$error['Cell'] = '这个手机号码已经被注册了';
		} else {
			$toUpdate['Cell'] = $p['Cell'];
		}
		
		$RealNameValidator = Msd_Member_Validator::realname($p['RealName'], $uid);
		if ($RealNameValidator['result']==$RealNameValidator['codes']['REALNAME_NOT_VALID']) {
			$error['RealName'] = '请填写有效的真实姓名';
		} else {
			$toUpdate['RealName'] = $p['RealName'];
		}

		if (!$p['DeleteAvatar'] && $_FILES['Avatar'] && $_FILES['Avatar']['name']) {
			if ($_FILES['Avatar']['name'] && Msd_Validator::isImage($_FILES['Avatar'])) {
				$hash = sha1(uniqid(mt_rand()));
				$result = Msd_Uploader::Save(array(
						'file' => 'Avatar',
						'hash' => $hash,
						'usage' => 'avatar'
						));
				if ($result['file_id']) {
					$toUpdate['Avatar'] = $hash;
					$toUpdate['file'] = $_FILES['Avatar'];
					$toUpdate['AvatarId'] = $result['file_id'];
				} else {
					$error['Avatar'] = '头像上传失败';
				}
			} else {
				$error['Avatar'] = '只能用图片来作为头像';
			}
		}
		
		$p['Msn'] = trim($p['Msn']);
		if ($p['Msn']!='' && !Msd_Validator::isEmail($p['Msn'])) {
			$error['Msn'] = '请输入正确的Msn地址';
		}
		
		$p['Qq'] = trim($p['Qq']);
		if ($p['Qq']!='' && !is_numeric($p['Qq'])) {
			$error['Qq'] = '请输入正确的QQ号码';
		}

		if (count($error)>0) {
			$p['UserName'] = $info['Username'];
			$this->view->error = $error;
			$this->view->member = $p;

			echo $this->view->render('profile/index.phtml');
			exit(0);
		} else {
			$toUpdate['Address'] = $p['Address'];
			$toUpdate['Qq'] = $p['Qq'];
			$toUpdate['Msn'] = $p['Msn'];
			$toUpdate['Homepage'] = $p['Homepage'];

			$this->member->update($toUpdate);
			$this->redirect('profile?success=1');
		}
	}
}

