<?php

class Msd_Mail
{
	public static function &factory($transport='sendmail')
	{
		$config = Msd_Config::appConfig()->mail;
		
		switch ($transport) {
			case 'sendmail':
				$tr = new Zend_Mail_Transport_Sendmail('-'.$config->return_to);
				Zend_Mail::setDefaultTransport($tr);
				break;
		}
		
		$mail = new Zend_Mail();
		
		return $mail;
	}
}