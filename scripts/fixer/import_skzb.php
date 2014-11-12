<?php

/**
 * 食客准备用户导入
 * 
 * 
 */

define('MSD_ONE_DAY', 86400);
define('MSD_START_TIME', microtime());
define('MSD_FORCE_RELOAD_CONFIG', true);
define('MSD_LOCAL_CACHER', getenv('MSD_LOCAL_CACHER') ? getenv('MSD_LOCAL_CACHER') : 'apc');
define('MSD_FORCE_CITY', getenv('MSD_FORCE_CITY') ? getenv('MSD_FORCE_CITY') : 'wuxi');

defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__).'/../../application'));
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

set_include_path(implode(PATH_SEPARATOR, array(
	realpath(APPLICATION_PATH . '/../library'),
	get_include_path(),	
	)));

require_once 'Msd/Autoloader.php';
Msd_Autoloader::getInstance()->register();

Msd_Timer::start('application');

$config = &Msd_Config::getInstance();
$cConfig = &Msd_Config::cityConfig();

Msd_Hook::run('BeforeBootStrap');

$db = &Msd_Dao::assignDbConnection();

$sql = "SELECT * FROM [skzb].[dbo].[t_customer] WHERE [real_name] IS NOT NULL";
try {
	$rs = $db->query($sql);
	$rows = $rs->fetchAll();
} catch (Exception $e) {
	die($e->getTraceAsString()."\n");
}

try {
	$sql = "SELECT * FROM [skzb].[dbo].[t_order]";
	$rs = $db->query($sql);
	$rows2 = $rs->fetchAll();
	$adds = array();
	foreach ($rows2 as $row) {
		$addr = $row['r_addr'];
		$tel = $row['r_tel'];
	
		if (!in_array($adds[$tel], $addr)) {
			$adds[$tel][] = $addr;
		}
	}
} catch (Exception $e) {
	die($e->getTraceAsString()."\n");
}
Msd_Dao::unassignDbConnection();

$cTable = &Msd_Dao::table('customer');
$caTable = &Msd_Dao::table('customer/address');
$cpTable = &Msd_Dao::table('customer/phone');
$uTable = &Msd_Dao::table('user');

foreach ($rows as $row) {
	$CustName = $row['real_name'];
	$Phone = $row['telepone'];
	$Mail = $row['email'];
	$Address = $row['address'];
	$Password = $row['password'];
	$PhoneType = Msd_Validator::isCell(ltrim($Phone, '0')) ? 1 : 0;
	$Qq = $Msn = '';
	
	if (is_numeric($Phone)) {
		$d = $cpTable->getGuidByNumber($Phone);

		if (!Msd_Validator::isGuid($d['CustGuid'])) {
			$CustGuid = $cTable->genGuid();
			$cTable->insert(array(
				'CustGuid' => $CustGuid,
				'CustName' => $CustName,
				'Mail' => $Mail,
				'Disabled' => '0',
				'Remark' => '食客准备老用户'
				));
			
			$PhoneGuid = $cpTable->genGuid();
			$cpTable->insert(array(
				'PhoneGuid' => $PhoneGuid,
				'CustGuid' => $CustGuid,
				'PhoneNumber' => $Phone,
				'PhoneType' => $PhoneType,
				'Remark' => 'skzb',
				'Disabled' => '0'
				));
			
			$AddressGuid = $caTable->genGuid();
			$caTable->insert(array(
				'AddressGuid' => $AddressGuid,
				'CustGuid' => $CustGuid,
				'CustAddress' => $Address,
				'Remark' => 'skzb',
				'Disabled' => '0'
				));
			if (is_array($adds[$Phone])) {
				foreach ($adds[$Phone] as $_addr) {
					$AGuid = $caTable->genGuid();
					$caTable->insert(array(
						'AddressGuid' => $AGuid,
						'CustGuid' => $CustGuid,
						'CustAddress' => $_addr,
						'Remark' => 'skzb',
						'Disabled' => '0'
						));
				}
			}
			
			if ($PhoneType==1) {
				$uTable->insert(array(
					'CustGuid' => $CustGuid,
					'Username' => 'skzb'.$Phone,
					'Password' => sha1($Password),
					'Address' => $Address,
					'Qq' => $Qq,
					'Msn' => $Msn
					));
			}
			echo $Phone."\n";
		}
	}
}