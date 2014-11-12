<?php

class Msd_Hook_Service extends Msd_Hook_Base
{
	protected static $instance = null;

	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	public function MemberRegistered(array $params=array())
	{
		$config = &Msd_Config::appConfig();
		
		$CustGuid = $params['CustGuid'];
		
		$sess    = &Msd_Session::getInstance();
		$tencent = $sess->get('tencent_connect');
		$weibo_userinfo = $sess->get('weibo_userinfo');
		$follow_fdw = (bool)$params['follow_fdw'];
		$city_config = &Msd_Config::cityConfig();
		if ($tencent) {
			$avatar = $tencent['userinfo']['figureurl_2'];
			/*
			if ($avatar) {
				$result = Msd_Service_Tencent_Avatar::save(array(
						'url' => $avatar,
						'CustGuid' => $CustGuid,
						'usage' => Msd_Config::appConfig()->attachment->usage->avatar
						));
				if ($result) {
					$toUpdate = array(
							'Avatar' => $result['hash'],
							'AvatarId' => $result['file_id'],
							'tmp_name' => $result['tmp_name']
							);
					$member = &Msd_Member::getInstance($CustGuid);
					$member->update($toUpdate);
				}
			}*/
			
			//	更新Token
			$OpenId = $tencent['openid'];
			$token  = $tencent['token'];
			
			$table  = &Msd_Dao::table('tencent/connect');
			$table->insert(array(
					'CustGuid' => $CustGuid,
					'Token' => $token,
					'OpenID' => $OpenId,
					'LastUpdate' => date('Y-m-d H:i:s', time()),
					'CityId'=>$city_config->city_id
			));			
		} else if ($weibo_userinfo) {
			$avatar = $weibo_userinfo['avatar_larget'];
			/*
			if ($avatar) {
				$result = Msd_Service_Tencent_Avatar::saveToDb($avatar);
				if ($result) {
					$toUpdate = array(
							'Avatar' => $result['hash'],
							'AvatarId' => $result['file_id'],
							'tmp_name' => $result['tmp_name']
							);
					$member = &Msd_Member::getInstance($CustGuid);
					$member->update($toUpdate);
				}
			}*/
			
			$table = &Msd_Dao::table('weibo');
			$weibo = $sess->get('weibo');
			
			$token = $weibo['access_token'];
			$expires = $weibo['expires_in'];
			
			$params = array(
					'WeiboUid'   => $weibo_userinfo['idstr'],
					'Token'      => $token,
					'Expires'    => date('Y-m-d H:i:s', time()+$expires),
					'LastUpdate' => date('Y-m-d H:i:s', time()),
					'CustGuid'   => $CustGuid,
					'CityId'=>$city_config->city_id
				);
			$table->insert($params);
			
			if ($follow_fdw) {
				Msd_Service_Sina_Weibo_Friendship::create($token, $config->service->sina->weibo->fdw_id);
			}
		}
	}
}