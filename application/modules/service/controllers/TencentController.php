<?php

/**
 * 腾讯相关服务
 * 
 * @author pang
 *
 */
class Service_TencentController extends Msd_Controller_Service
{
	
	public function connectAction()
    {
    	$code = $this->getRequest()->getParam('code', '');
    	$state = $this->getRequest()->getParam('state', '');
    	
    	$this->sess->set('weibo_userinfo');

    	$service = Msd_Service_Tencent_Connect::getInstance();
    	$data = $service->getAccessToken($code, $state);
    	$token = $data['token'];
    	
    	$openID = $service->getOpenID($token);
    	$userInfo = $service->getUserInfo($openID, $token);
    	
    	$data = array(
    			'userinfo' => &$userInfo,
    			'token' => $token,
    			'openid' => $openID
    			);
    	$this->sess->set('tencent_connect', $data);
    	
    	$tcTable = &Msd_Dao::table('tencent/connect');
    	$tokenCustGuid = $tcTable->getCustGuidByOpenId($openID);

    	if ($tokenCustGuid) {
    		//	用户在本地库存在
    		$member = &Msd_Member::getInstance($tokenCustGuid);
    		$this->sess->set('uid', $tokenCustGuid);
    		
			Msd_Hook::run('MemberLogin', array(
					'uid' => $tokenCustGuid
					));
    		
    		//	更新Token
    		$tcTable->replace(
    				array('LastUpdate' => date('Y-m-d H:i:s', time())),
    				array('CustGuid' => $member->uid())
    		);

    		$redirectUrl = $this->sess->get('last_url') ? $this->sess->get('last_url') : '/';
    		$this->_helper->getHelper('Redirector')->gotoUrl($redirectUrl);
    	} else {
    		//	用户在本地库不存在
    		//$this->_helper->getHelper('Redirector')->gotoUrl($this->scriptUrl.'member/register?from_qq=1&member_not_exists=1');
    		$this->_helper->getHelper('Redirector')->gotoUrl($this->scriptUrl.'member/register/from-qq');
    	}

		exit(0);
    }
}

