<?php

/**
 * 网站留言
 * 
 * @author pang
 *
 */
class FeedbackController extends Msd_Controller_Default {
	public function init() {
		parent::init ();
	}
	public function indexAction() {
		$phone = '';
		if ($this->member->uid ()) {
			$extend = $this->member->extend ();
			$phone = $extend ['Cell'];
		}
		$this->view->phone = $phone;
		$this->pager_init ( array (
				'limit' => 10 
		) );
		$table = &Msd_Dao::table ( 'feedback' );
		
		$rows = $table->search ( $this->pager, array (
				'DisplayFlag' => '1',
				'Regions' => Msd_Waimaibao_Region::RegionGuids () 
		), array (
				'CreateTime' => 'DESC' 
		) );
		
		$this->view->rows = $rows;
		$this->view->page_links = $this->page_links ( $this );
	}
	public function errorAction() {
	}
	public function doAction() {
		$p = $this->getRequest ()->getPost ();
		$cConfig = &Msd_Config::cityConfig ();
		$scode = $this->sess->get ( 'captcha_code' );
		$phone = trim ( $p ['phone'] );
		
		if (! empty ( $_COOKIE ['feedback'] )) {
			echo '抱歉，20分钟内不能再次提交留言，谢谢！';
			exit ();
		}
		if (trim ( $p ['content'] ) == '') {
			echo '请填写留言内容';
			exit ();
		}
		
		if ($phone == '') {
			echo '请输入手机号，便于我们及时给您回复，谢谢！';
			exit ();
		}
		
		if (! Msd_Validator::isCell ( $phone )) {
			echo '您输入的手机号有误，请检查一下！';
			exit ();
		}
		
		if (strtolower ( $p ['scode'] ) != strtolower ( $scode )) {
			echo '验证码不正确';
			exit ();
		}
		$params = array (
				'Content' => strip_tags ( $p ['content'] ),
				'OrderNo' => '9999',
				'IpAddress' => Msd_Request::clientIp (),
				'RegionGuid' => $cConfig->db->guids->root_region,
				'Phone' => $phone,
				'CityId' => $cConfig->city_id 
		);
		
		if ($this->member->uid ()) {
			$params ['CustGuid'] = $this->member->uid ();
			$params ['Username'] = $this->member->Username;
		}
		
		$insertid = Msd_Dao::table ( 'feedback' )->insert ( $params );
		if ($insertid) {
			setcookie ( "feedback", time (), time () + 60 * 20 );
			$this->sess->set ( 'captcha_code', rand ( 1000, 9999 ) );
			Msd_Hook::run ( 'FeedbackPosted', array (
					'UserName' => $this->member->Username,
					'Content' => $p ['content'] 
			) );
			echo "提交成功,饭店网客服将会尽快给您答复，感谢支持!";
		}
		$this->_helper->viewRenderer->setNoRender ();
	}
}

