<?php

class Msd_Member_Avatar extends Msd_Member_Base
{
	protected static $instances = array();
	protected static $dao = null;
	
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
	
	public function delete($hash='')
	{
		$config = &Msd_Config::appConfig()->attachment->usage;
		$pager = array(
				'limit' => 20
				);
		$files = &Msd_Dao::table('attachment')->search($pager, array(
				'Uid' => $this->uid,
				'Usage' => array(
						$config->avatar,
						$config->avatar_normal,
						$config->avatar_small
						)
				), array());
		foreach ($files as $file) {
			($hash=='' || ($hash!='' && $file['Hash']!=$hash)) && Msd_Files::Del($file['FileId']);
		}
	}
}