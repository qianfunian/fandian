<?php

class Fadmin_LoginController extends Msd_Controller_Fadmin
{
	
	public function indexAction()
    {
    	
    }
    
    public function dologinAction()
    {
    	$username = $this->getRequest()->getParam('username', '');
    	$password = $this->getRequest()->getParam('password', '');
    	$captcha = $this->getRequest()->getParam('captcha', '');
    	
    	$table = &Msd_Dao::table('sysuser');
    	$row = $table->get($username, 'username');

    	if ($captcha && Msd_Image_Captcha::check($captcha, 'admin') && $row['Username'] && $row['Password']==sha1($password)) {
    		$uid = $row['AutoId'];
    		$this->sess->set('uid', $uid);
    		$this->member = $row;

    		$table->doUpdate(array(
    				'LastLogin' => date('Y-m-d H:i:s')
    				), $uid);
    		
    		$this->log(array(
    				'type' => 'login',
    				'message' => '登录系统'
    				));
    		$this->redirect($this->scriptUrl);
    	} else {
    		$this->log(array(
    				'type' => 'login',
    				'message' => '登录失败',
    				'username' => $username
    				));
    		parent::redirect($this->scriptUrl.'login');
    	}
    }

    public function logoutAction()
    {
    	$this->log(array(
    			'type' => 'login',
    			'message' => '退出系统',
    	));
    	    	
    	$this->sess->set('uid');
    	$this->redirect($this->scriptUrl.'login');
    }
}

