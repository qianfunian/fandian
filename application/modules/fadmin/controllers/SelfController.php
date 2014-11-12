<?php

class Fadmin_SelfController extends Msd_Controller_Fadmin
{
	public function init()
	{
		parent::init();
	}
	
	public function indexAction()
	{
		$p = &$_POST;

		if (count($p)>0) {
			$password = $p['password'];
			$password2 = $p['password2'];
			$error  = array();
			
			if (strlen($password)<6) {
				$error['password'] = '密码必须大于等于6个字符';
			} else if ($password!=$password2) {
				$error['password2'] = '两次输入的密码不一致';
			}
			
			if (count($error)>0) {
				$this->view->error = $error;
			} else {
				$table = &Msd_Dao::table('sysuser');
				$table->doUpdate(array(
					'Password' => sha1($password)	
					), $this->member['AutoId']);
				
				$this->log(array(
					'type' => 'update',
					'message' => '修改个人密码'
					));
				
				$this->redirect($this->scriptUrl.'self');
			}
		}
	}
	
}