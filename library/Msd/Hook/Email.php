<?php

class Msd_Hook_Email extends Msd_Hook_Base
{
	protected static $instance = null;
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function MemberChanged(array $params=array())
	{
		
	}
	
	public function WelcomeEmail(array $params=array())
	{
		$config = &Msd_Config::appConfig();
		$emailer = &Msd_Email::factory();
		$queue = &Msd_Queue::getQueue('email');
		
		$queueData = array(
				'receiver' => $params['Email'],
				'Content' => $params['Content'],
				'Subject' => '饭店网注册邮箱激活',
				'CreateTime' => time()
		);
		$queue->put(json_encode($queueData));
	}
	
	public function ResetpwdRequested(array $params=array())
	{
		$queue = &Msd_Queue::getQueue('email');
		
		$queueData = array(
				'receiver' => $params['Email'],
				'Content' => $params['Content'],
				'Subject' => '重置您在饭店网的密码',
				'CreateTime' => time()
				);
		$queue->put(json_encode($queueData));
	}
	
	public function OrderBug($params)
	{
		$queue = &Msd_Queue::getQueue('email');
		$queueData = array(
				'receiver' => "312181918@qq.com",
				'Content' => $params,
				'Subject' => '紧急。订单提交失败',
				'CreateTime' => time()
		);
		$queue->put(json_encode($queueData));
	}
	
	public function FeedbackPosted(array $params=array())
	{
		$config = &Msd_Config::appConfig();
		$cConfig = &Msd_Config::cityConfig();
		$emailer = &Msd_Email::factory();
		$notifiers = explode(',', $config->feedback->notifiers);
		$queue = &Msd_Queue::getQueue('email');
		
		foreach ($notifiers as $receiver) {
			$data = array(
					'receiver' => $receiver,
					'Content' => $params['Content'],
					'Subject' => '['.$cConfig->city_id.']'.$params['UserName'].' 提交了新的网上留言',
					'CreateTime' => time()
					);
			$queue->put(json_encode($data));
		}
	}
}