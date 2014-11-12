<?php

/**
 * 网站用户
 * 
 * @author pang
 *
 */

class Msd_Member
{
	protected static $instances = array();
	
	protected $uid = 0;
	protected $member = array();
	protected $extend = array();
	
	private function __construct($uid)
	{
		$this->uid = Msd_Validator::isGuid(trim($uid)) ? $uid : 0;
		
		if ($this->uid) {
			$table = &Msd_Dao::table('user');
			$this->member = &$table->get($this->uid);
		}
	}
	
	public function __get($key)
	{
		$result = false;
		
		if (isset($this->member[$key])) {
			$result = &$this->member[$key];
		}
		
		return $result;
	}
	
	public function __set($key, $val)
	{
		$this->member[$key] = $val;
		
		return true;
	}
	
	public static function &getInstance($uid)
	{
		if (!isset(self::$instances[$uid])) {
			self::$instances[$uid] = new self($uid);
		}
		
		return self::$instances[$uid];
	}
	
	/**
	 * 根据数据库自动创建一个用户实例
	 * 
	 * @param misc $val
	 * @param string $key
	 */
	public static function &createInstance($val, $key='CustGuid')
	{
		$obj = null;
		
		$uid = '';
		switch ($key) {
			case 'username':
				$data = Msd_Dao::table('user')->get($val, 'UserName');
				$uid = $data['CustGuid'];
				break;
			case 'cell':
				$data = Msd_Dao::table('customer/phone')->CellLogin($val);
				$uid = $data['CustGuid'];
				break;
			case 'email':
				$data = Msd_Dao::table('customer')->get($val, 'Mail');
				$uid = $data['CustGuid'];
				break;
		}

		if ($uid) {
			$obj = &self::getInstance($uid);
		} else {
			$obj = &self::getInstance(0);
		}

		return $obj;
	}
	
	/**
	 * 创建一个用户
	 * @param array $params
	 */
	public static function &create(array $params)
	{
		$result = array();
		$obj = null;
		
		$usernameResult = Msd_Member_Validator::username($params['UserName']);
		$cellResult     = Msd_Member_Validator::cell($params['Cell']);
		$emailResult    = Msd_Member_Validator::email($params['Email']);
		$realnameResult = Msd_Member_Validator::realname($params['RealName']);

		if ($usernameResult['result']==$usernameResult['codes']['SUCCESS'] && $emailResult['result']==$emailResult['codes']['SUCCESS'] && ($cellResult['result']==$cellResult['codes']['SUCCESS'] || $cellResult['result']==$cellResult['codes']['CELL_NOT_EXISTS_BUT_ORDERED']) && $realnameResult['result']==$realnameResult['codes']['SUCCESS']) 
		{
			$_CustOrdered = $CustGuid = $cellResult['CustGuid'];
			
			$table  = &Msd_Dao::table('user');
			$cTable = &Msd_Dao::table('customer');
			$pTable = &Msd_Dao::table('customer/phone');
				
			$tTrans = $cTable->transaction();
			$tr = $tTrans->start();
				
			try {
				$city_config = &Msd_Config::cityConfig();	
				if (!$CustGuid) {
					$cust = array(
							'CustName'     => $params['RealName'],
							'CtgGroupGuid' => $cTable->expr('NULL'),
							'Company'      => $params['Company'],
							'Mail'         => $params['Email'],
							'AddTime'      => date('Y-m-d H:i:s', time()),
							'AddUser'      => ''						
							);
					$CustGuid = $cTable->insert($cust);
				} else {
					$cTable->doUpdate(array(
							'CustName' => $params['RealName'],
							'Mail'     => $params['Email']), $CustGuid);
				}
	
				$password = isset($params['PassWord']) ? ($params['_PasswordSha1'] ? $params['PassWord'] : sha1($params['PassWord'])) : null;
	
				if ($CustGuid) {
					$table->insert(array(
							'CustGuid' => $table->wrapGuid($CustGuid),
							'UserName' => $params['UserName'],
							'PassWord' => $password,
							'Avatar'   => '',
							'Address'  => $params['Address'],
							'CityId'   => $city_config->city_id,
							'VerifyString' =>$params['randomstring']
							));
						
					if (!$_CustOrdered) {
						$pTable->insert(array(
								'CustGuid'    => $pTable->wrapGuid($CustGuid),
								'PhoneNumber' => $params['Cell'],
								'PhoneType'   => $pTable->cellType(),
								'Remark'      => ' '
								));
					}
						
					$obj = &self::getInstance($CustGuid);
					
					$tTrans->commit();
						
	 				Msd_Hook::run('WelcomeEmail', array(
	 				'Email' => $params['Email'],
	 				'Content' => $params['welcome']
	 				));
						
					Msd_Hook::run('MemberRegistered', array(
					'CustGuid'   => $CustGuid,
					'from_qq'    => $params['from_qq'],
					'from_weibo' => $params['from_weibo'],
					'follow_fdw' => $params['follow_fdw']
					));
						
				} else {
					$tTrans->rollback();
				}
			} catch (Exception $e) {
				$tTrans->rollback();
				Msd_Log::getInstance()->member($e);
			}
		}
		
		$result['username'] = $usernameResult;
		$result['cell'] = $cellResult;
		$result['email'] = $emailResult;
		$result['member'] = &$obj;
		
		return $result;
	}
	
	public function uid()
	{
		return $this->uid;
	}
	
	public function &info()
	{
		return $this->member;
	}
	
	/**
	 * 获取用户扩展信息
	 * 
	 */
	public function &extend()
	{
		if (count($this->extend)==0) {
			$cTable  = &Msd_Dao::table('customer');
			$pTable  = &Msd_Dao::table('customer/phone');
			$atTable = &Msd_Dao::table('attachment');
			$aTable  = &Msd_Dao::table('customer/address');
			$adTable = &Msd_Dao::table('addressbook');
			
			$avatar = array(
					'origin' => '',
					'normal' => '',
					'small' => ''
					);
			
			$c = $cTable->get($this->uid);
			$p = $pTable->getCellRow($this->uid);
			$a = $aTable->CustLastAddress($this->uid);

			if ($this->member['Avatar']) {
				$files = &Msd_Files::GetByHash($this->member['Avatar']);
				$config = Msd_Config::appConfig()->attachment->usage;

				foreach ($files as $file) {
					switch ($file['Usage']) {
						case $config->avatar_normal:
							$avatar['normal'] = $file['FileId'];
							break;
						case $config->avatar_small:
							$avatar['small'] = $file['FileId'];
							break;
						default:
							$avatar['origin'] = $file['FileId'];
							break;
					}
				}
			}

			if ($this->uid) {
				$ab = &Msd_Member_Addressbook::getInstance($this->uid);
			} else{
				$ab = array();
			}
			
			$this->extend = array(
					'Email' => $c['Mail'],
					'RealName' => $c['CustName'],
					'Cell' => $p['PhoneNumber'],
					'Avatar' => $avatar,
					'Address' => $a['CustAddress'],
					'Coord' => array(
						'CoordGuid' => $a['CoordGuid'],
						'name' => $a['CoordName'],
						'longitude' => $a['Longitude'],
						'latitude' => $a['Latitude']
						),
					'AddressBook' => $ab->getDefault()
					);
		}

		return $this->extend;
	}
	
	/**
	 * 更新用户资料
	 * 
	 * @param array $params
	 */
	public function update(array $params=array())
	{
		$c = $p = $u = array();
		
		isset($params['PassWord']) && $u['Password'] = sha1($params['PassWord']);
		isset($params['UserName']) && $u['Username'] = $params['UserName'];
		isset($params['LastLogin']) && $u['LastLogin'] = $params['LastLogin'];
		isset($params['Email']) && $c['Mail'] = $params['Email'];
		isset($params['RealName']) && $c['CustName'] = $params['RealName'];
		isset($params['Cell']) && $p['PhoneNumber'] = $params['Cell'];
		isset($params['Avatar']) && $u['Avatar'] = $params['Avatar'];
		isset($params['AvatarId']) && $u['Avatar'] = $params['AvatarId'];
		isset($params['Address']) && $u['Address'] = $params['Address'];
		isset($params['Qq']) && $u['Qq'] = $params['Qq'];
		isset($params['Msn']) && $u['Msn'] = $params['Msn'];
		isset($params['Homepage']) && $u['Homepage'] = $params['Homepage'];
		$params['DeleteAvatar'] && $u['Avatar'] = '';
		isset($params['VerifyString']) && $u['VerifyString'] = $params['VerifyString'];

		count($c)>0 && Msd_Dao::table('customer')->doUpdate($c, $this->uid);
		count($p)>0 && Msd_Dao::table('customer/phone')->updateCustomerCell($p, $this->uid);
		count($u)>0 && Msd_Dao::table('user')->doUpdate($u, $this->uid);
		
		Msd_Hook::run('MemberChanged', array(
				'CustGuid' => $this->uid(),
				'params' => &$params
				));
	}
	
	public static function newCustId()
	{
		$id = '';

		if (Msd_Config::cityConfig()->wcf->enabled) {
			$wcf = new Msd_Service_Wcf_Numbersequence();
			$id = $wcf->CustomId();
		} else {
			
		}
		
		return $id;
	}
}
