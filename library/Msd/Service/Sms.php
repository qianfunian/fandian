<?php

/**
 * 写入数据到短信Modem的队列
 * 
 * @author pang
 *
 */

class Msd_Service_Sms
{
	public static function Send($receiver, $content)
	{
		Msd_Dao::table('sendtask')->insert(array(
			'DestNumber' => $receiver,
			'Content' => $content	
			));
	}
}