<?php

class Fadmin_SysusersController extends Msd_Controller_Fadmin
{
	public function init()
	{
		parent::init();
		
		$this->auth('sysuser');
	}
	
	public function indexAction()
    {
    	$this->pager_init();
    	
    	$table = &Msd_Dao::table('sysuser', 'web');
    	
    	$params = $sort = array();
    	$orderKey = trim($this->getRequest()->getParam('order_key', ''));
    	$searchKey = trim(urldecode($this->getRequest()->getParam('search_key', '')));
    	$searchVal = trim(urldecode($this->getRequest()->getParam('search_val', '')));
    	
    	if (strlen($searchKey) && strlen($searchVal)) {
    		$params[$searchKey] = $searchVal;
    	}
    	
    	if ($orderKey!='') {
    		$sort[$orderKey] = 'DESC';
    	}
    	
    	$rows = $table->search($this->pager, $params, $sort);
    	
    	$this->view->rows = $rows;
    	$this->view->page_links = $this->page_links();
    	$this->view->data = array();
    	$this->view->request = $_REQUEST;
    	
    	$this->log(array(
    			'type' => 'browse',
    			'message' => '浏览系统用户',
    			));
    }
    
    public function editAction()
    {
    	$id = $this->getRequest()->getParam('AutoId', '');

    	if ($id) {
    		$this->auth('sysuser:edit');
    		$data = Msd_Dao::table('sysuser', 'web')->get($id);
    		$acls = explode('|', $data['Sysrights']);
    		$this->view->u_acls = $acls;
    	} else {
    		$this->auth('sysuser:add');
    	}

    	$this->view->data = $data;
    }
    
    public function doeditAction()
    {
    	$AutoId = $this->getRequest()->getParam('AutoId', '');
    	$Username = $this->getRequest()->getParam('Username', '');
    	$Password = $this->getRequest()->getParam('Password', '');
    	$acls = $this->getRequest()->getParam('acls', '');
    	$acls[] = 'self';
    	$acls[] = 'index';
    	
    	$table = &Msd_Dao::table('sysuser');
    	$user = array();
    	
    	$error = array();
    	if (trim($Username)=='') {
    		$error['Username'] = '请填写用户名';
    	} else {
    		$user = $table->Username($Username);
    		if ($user['AutoId'] && $user['AutoId']!=$AutoId) {
    			$error['Username'] = '这个用户名已经使用过了';
    		}
    	}
    	
    	if (trim($Password)=='') {
    		$error['Password'] = '请填写密码';
    	}
    	 
    	if (count($error)>0) {
    		$this->view->error = $error;
    		$this->view->data = $_POST;
    		echo $this->view->render('edit.phtml');
    		exit(0);
    	}
    	
    	$params = array(
    			'Username' => $Username,
    			'Password' => $Password==$user['Password'] ? $Password : sha1($Password),
    			'Sysrights' => implode('|', array_unique($acls)),
    			);

    	if ($AutoId) {
    		$this->auth('sysuser:edit');
    		$x = $table->doUpdate($params, $AutoId);
    		
    		$this->log(array(
    				'type' => 'update',
    				'message' => '修改系统用户, id: '.$AutoId
    				));
    	} else {
    		$this->auth('sysuser:add');
    		$table->insert($params);
    		
    		$this->log(array(
    				'type' => 'insert',
    				'message' => '新增系统用户，用户名：'.$Username
    				));
    	}
    	
    	$this->redirect($this->scriptUrl.'sysusers');
    }
    
    public function delAction()
    {
    	$this->auth('sysuser:del');
    	$AutoId = $this->getRequest()->getParam('AutoId', '');
    	
    	if ($AutoId) {
	    	$table = &Msd_Dao::table('sysuser');
	    	$table->doDelete($AutoId);
    	}
    	
    	$this->redirect($this->scriptUrl.'sysusers');
    }

}

