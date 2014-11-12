<?php

class Msd_Service_Sina_Weibo_Friendship extends Msd_Service_Sina_Weibo_Base
{
	public static function create($token, $uid, $screen_name='')
	{
		$client = &Msd_Service_Sina_Weibo::client($token);
		$result = $client->follow_by_id($uid);
		
		Msd_Log::getInstance()->weibo(serialize($result));
		
		return $result;
	}
}