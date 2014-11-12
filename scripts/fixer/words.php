<?php

/**
 * 网站留言导入
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

$conn = mysql_connect('192.168.1.92', 'root', 'leipang');
mysql_select_db('tmp', $conn);
mysql_query('set names gbk');

$fTable = &Msd_Dao::table('feedback');

set_time_limit(0);
$i = 1;
$sql = "SELECT *
	FROM fandian_words
";
$rs = mysql_query($sql, $conn);
while ($r = mysql_fetch_array($rs)) {
	
	$params = array(
			'Content' => Msd_Iconv::g2u($r['content']),
			'OrderNo' => $r['order_no'],
			'IpAddress' => $r['ip'],
			'CreateTime' => date('Y-m-d H:i:s', $r['create_time']),
			'DisplayFlag' => $r['published'],
			'ReplyContent' => Msd_Iconv::g2u($r['reply_content']),
			'ReplyTime' => date('Y-m-d H:i:s', $r['reply_time']),
			'RegionGuid' => $cConfig->db->guids->root_region
			);
		echo $r['content']."\n";
	$fTable->insert($params);
}

mysql_close($conn);


echo "\nDone.\n";