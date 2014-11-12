<?php

/**
 * 用户控制器基类
 * 
 * @author pang
 *
 */

class Msd_Controller_Member extends Msd_Controller
{
	protected $sess = null;
	protected $member = null;
	
	public function init()
	{
		parent::init();
		
		$this->sess = &Msd_Session::getInstance();
		$this->member = &Msd_Member::getInstance($this->sess->get('uid'));
		
		$this->view->scriptUrl = $this->scriptUrl = $this->baseUrl.'member/';

		$this->view->CityConfig = Msd_Config::cityConfig();
		
		//	天气
		$weather = Msd_Service_Webxml_Weather::getInstance()->getWeatherFromCache(
			Msd_Config::cityConfig()->service->webxml->weather->city
			);
			
		$this->view->weather = $weather;
		
		$this->view->uid = $this->member->uid();
		
		$this->view->qq_login_url = $this->member->uid() ? '' : Msd_Service_Tencent_Connect::getInstance()->getAuthUrl();
		$this->view->weibo_login_url = $this->member->uid() ? '' : Msd_Service_Sina_Weibo::getInstance()->getOAuthUrl();
		$this->view->MetaPara = array(
			'title' => '我的账户'
			);
		
		$_vars = &Msd_Cache_Loader::Systemvars();
		$ca = unserialize($_vars['close_anounce']);
		$this->view->close_announce = $ca;
		
		$this->view->top_banner = array(
				'url' => '',
				'link' => ''
		);
		if ($_vars['top_banner']) {
			try {
				$__t = unserialize($_vars['top_banner']);
		
				$this->view->top_banner['url'] = $__t['url'];
				$this->view->top_banner['link'] = $__t['link'];
			} catch (Exception $e) {
			}
		}
		
		if ($this->member->uid()) {
			$member = $this->member->info();
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
		
		$_vars = &Msd_Cache_Loader::Systemvars();
		$ca = unserialize($_vars['close_anounce']);
		$this->view->close_announce = $ca;
		
		$this->view->top_banner = array(
				'url' => '',
				'link' => ''
		);
		if ($_vars['top_banner']) {
			try {
				$__t = unserialize($_vars['top_banner']);
		
				$this->view->top_banner['url'] = $__t['url'];
				$this->view->top_banner['link'] = $__t['link'];
			} catch (Exception $e) {
			}
		}
		
		$this->view->order_announce = Msd_Cache_Loader::orderAnnounce();
		
		$this->fiestParams();
	}
	
	protected function AuthRedirect()
	{
		if (!$this->member->uid()) {
			$this->_helper->getHelper('Redirector')->gotoUrl($this->scriptUrl.'login');
		}
	}
}
