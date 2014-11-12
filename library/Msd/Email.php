<?php

/**
 * Email处理
 * 
 * @author pang
 *
 */

require_once 'Zend/Mail.php';
require_once 'Zend/Mail/Transport/Smtp.php';

class Msd_Email
{
	public static function &factory($transport='')
	{
		$config = &Msd_Config::cityConfig()->mail;
		$transport = $config->transport;
		
		$tr = new Zend_Mail_Transport_Smtp($config->smtp->host, array(
				'auth' => 'login',
				'username' => $config->smtp->user,
				'password' => $config->smtp->password
				));
		
		Zend_Mail::setDefaultTransport($tr);
		$handler = new Zend_Mail('UTF-8');
		$handler->setFrom(preg_match('/@/', $config->smtp->user) ? $config->smtp->user : $config->smtp->user.'@fandian.com', '饭店网');
		$handler->addHeader('X-MailGenerator', 'Fandian.Com');
		
		return $handler;
	}
}