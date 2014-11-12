<?php

/**
 * 新浪相关服务回调接受
 * 
 * @author pang
 *
 */
class Service_SinaController extends Msd_Controller_Service
{
	
	/**
	 * 接受微博登录回调
	 * 
	 */
	public function weiboAction()
    {
    	$code = $this->getRequest()->getParam('code', '');
    	$errorCode = $this->getRequest()->getParam('error_code', '');
    	$error = $this->getRequest()->getParam('error', '');
    	
    	$service = Msd_Service_Sina_Weibo::OAuth();
    	$data = '';
    	
    	$this->sess->set('tencent_connect');
    	
    	if ($code!='') {
    		$keys = array();
    		$keys['code'] = $code;
    		$keys['redirect_uri'] = Msd_Config::cityConfig()->service->sina->weibo->callback_url;

    		try {
    			$data = $service->getAccessToken('code', $keys);
    		} catch (Exception $e) {
    			Msd_Log::getInstance()->weibo($e->getMessage());
    		}

    		if ($data) {
    			$this->sess->token = $data;
    			Msd_Cookie::set('weibojs_'.$service->client_id, http_build_query($data));

    			$token = $data['access_token'];
    			$expires = $data['expires_in'];
    			$uid = $data['uid'];
    			
    			$this->sess->set('weibo', $data);
    			$client = Msd_Service_Sina_Weibo::client($token);
    			$userInfo = $client->show_user_by_id($uid);
    			
    			$wTable = &Msd_Dao::table('weibo');
    			
    			$tokenCustGuid = $wTable->getCustGuidByUid($uid);

		    	if ($tokenCustGuid) {
		    		//	用户在本地库存在
		    		$member = &Msd_Member::getInstance($tokenCustGuid);
		    		$this->sess->set('uid', $member->uid());
		    		
					Msd_Hook::run('MemberLogin', array(
							'uid' => $tokenCustGuid
							));
		    
		    		//	更新Token
		    		$savedToken = $wTable->get($member->uid());
		    		$params = array(
		    				'WeiboUid' => $uid,
		    				'Token' => $token,
		    				'Expires' => date('Y-m-d H:i:s', time()+$expires),
		    				'LastUpdate' => date('Y-m-d H:i:s', time())		    				
		    				);
		    		
		    		if ($savedToken['CustGuid']) {
		    			$wTable->doUpdate($params, $member->uid());
		    		} else {
		    			$params['CustGuid'] = $member->uid();
			    		$wTable->insert($params);
		    		}
		    		
		    		$redirectUrl = $this->sess->get('last_url') ? $this->sess->get('last_url') : $this->baseUrl;
		    		$this->_helper->getHelper('Redirector')->gotoUrl($redirectUrl);
		    	} else {
		    		//	用户在本地库不存在
		    		$this->sess->set('weibo_userinfo', $userInfo);
		    		//$this->_helper->getHelper('Redirector')->gotoUrl($this->baseUrl.'member/register?from_weibo=1&_='.microtime());
		    		$this->_helper->getHelper('Redirector')->gotoUrl($this->baseUrl.'member/register/from-sina?_='.microtime());
		    	}
    		} else {
    			/**
    			 * TODO: 进入错误处理
    			 */
    		}    		
    	} else if ($errorCode!='' || $error!='') {
    		
    	}
    }

}

