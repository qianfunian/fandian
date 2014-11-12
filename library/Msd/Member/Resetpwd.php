<?php

class Msd_Member_Resetpwd extends Msd_Member_Base
{
	protected static $instances = array();
	
	protected function __construct($uid)
	{
		parent::__construct($uid);
	}
	
	public static function &getInstance($uid)
	{
		if (!isset(self::$instances[$uid])) {
			self::$instances[$uid] = new self($uid);
		}
	
		return self::$instances[$uid];
	}
	
	public function saveHash($hash)
	{
		$this->clearMyHash();
		$table = &Msd_Dao::table('resetpasswordhash');
		
		$table->insert(array(
				'Hash' => $hash,
				'CustGuid' => $this->uid
				));
	}
	
	public function clearMyHash()
	{
		$table = &Msd_Dao::table('resetpasswordhash');
		$table->deleteForMember($this->uid);
	}
	
	public function doReset()
	{
		$newPassword = rand(100000, 999999);
		$params = array(
				'PassWord' => $newPassword
				);
		Msd_Member::getInstance($this->uid)->update($params);
		
		return $newPassword;
	}
}