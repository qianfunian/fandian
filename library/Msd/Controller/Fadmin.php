<?php

/**
 * 后台控制器基类
 * 
 * @author pang
 *
 */

class Msd_Controller_Fadmin extends Msd_Controller
{
	protected $sess = null;
	protected $member = array();
	protected static $acl = null;
	protected static $aclInited = false;
	protected $pageName = '';
	protected $dbKey = 'web';
	protected static $acls = null;
	protected $user_acls = array();
	protected $member_is_super = false;
	
	public function init()
	{
		parent::init();
		
		$config = &Msd_Config::cityConfig();
		
		if (self::$acls==null) {
			$acls = $config->acl->fadmin->toArray();
			foreach ($acls as $acl=>$row) {
				self::$acls[$acl] = $row;
			}	
		}

		$this->sess = &Msd_Session::getInstance('admin');
		if ($this->sess->get('uid')) {
			$this->member = Msd_Dao::table('sysuser', $this->dbKey)->get($this->sess->get('uid'));
			$this->user_acls = explode('|', $this->member['Sysrights']);
			in_array('admin', $this->user_acls) && $this->member_is_super = true;
		}

		$this->view->member = $this->member;
		$this->view->scriptUrl = $this->scriptUrl = $this->baseUrl.'fadmin/';
		
		$this->view->acls = self::$acls;
		$this->view->user_acls = $this->user_acls;
		$this->view->member_is_super = $this->member_is_super;
		
		$this->view->CityConfig = $config;
	}
	
	protected function auth($resource='')
	{
		if (!$this->sess->get('uid')) {
			$this->_helper->getHelper('Redirector')->gotoUrl($this->scriptUrl.'login');
		} else if (!$this->member_is_super && !in_array($resource, $this->user_acls)) {
			throw new Msd_Exception("对不起，你没有权限访问当前页面");
		}
	}
	
	protected function log(array $params)
	{
		Msd_Dao::table('systemlog', $this->dbKey)->insert(array(
				'ActionType' => $params['type'],
				'Uid' => (int)$this->sess->get('uid'),
				'Memo' => $params['message'],
				'Url' => $_SERVER['REQUEST_URI'],
				'Ip' => Msd_Request::clientIp()
				));	
	}
	
	public function redirect($url)
	{
		$this->view->redirectUrl = $url;
		echo $this->view->render('redirect/index.phtml');
		exit(0);
	}
}