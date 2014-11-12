<?php

class Msd_Hook_Member extends Msd_Hook_Base
{
	protected static $instance = null;
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function MemberLogin(array $params=array())
	{
		$uid = $params['uid'];
		Msd_Member::getInstance($uid)->update(array('LastLogin' => date('Y-m-d H:i:s')));
		
		$cTable = &Msd_Dao::table('customer');
		$c = $cTable->get($uid);
		if (!$c) {
			$cTable->insert(array(
					'CustGuid' => $uid,
					'CustName' => '',
					'Company' => '',
					'Mail' => '',
					'Remark' => '',
					'Disabled' => '',
					'AddUser' => ''
					));
		}
	}
}