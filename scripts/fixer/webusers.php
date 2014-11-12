<?php

/**
 * 网站注册用户导入
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
Msd_Hook::run('BeforeBootStrap');

$conn = mysql_connect('192.168.1.251', 'root', '85860068');
mysql_select_db('waimaibao', $conn);
mysql_query('set names gbk');

$uTable = &Msd_Dao::table('user');
$cpTable = &Msd_Dao::table('customer/phone');

set_time_limit(0);
$i = 1;
$sql = "SELECT *
	FROM tb_customer
	WHERE c_id LIKE 'W%' AND (c_username IS NOT NULL AND c_username!='')
";
$rs = mysql_query($sql, $conn);

while ($r = mysql_fetch_array($rs)) {
	$username = $r['c_username'];
	$phone = $r['c_cellphone'];
	
	if (Msd_Validator::isCell($phone)) {
		$info = $cpTable->cellInfo($phone);
		$CustGuid = $info['CustGuid'];
		
		if (Msd_Validator::isGuid($CustGuid)) {
			$params = array(
				'CustGuid' => $CustGuid,
				'Username' => iconv('gbk', 'utf-8', $username),
				'Password' => $r['c_pwd'],
				'Address' => iconv('gbk', 'utf-8', $r['c_address']),	
				);
			echo $i.":".$username.", ".$phone.', '.$info['CustGuid']."\n";

			try {
				$uTable->insert($params);
				$i++;
			} catch (Exception $e) {
				
			}
		}
	}
}

mysql_close($conn);


echo "\nDone.\n";