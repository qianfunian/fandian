<?php

/**
 * 启动器
 */
date_default_timezone_set('Asia/Shanghai');
if (getenv('FANDIAN_APP_VER')) {
	define('FANDIAN_APP_VER', getenv('FANDIAN_APP_VER'));
} else {
	//	获取git版本号作为静态文件随机码
	$file = dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'.git'.DIRECTORY_SEPARATOR.'ORIG_HEAD';
	$file2 = dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'.git'.DIRECTORY_SEPARATOR.'refs'.DIRECTORY_SEPARATOR.'heads'.DIRECTORY_SEPARATOR.'master';
	if (file_exists($file)) {
		define('FANDIAN_APP_VER', substr(str_replace("\n", "", file_get_contents($file)), 0, 16));
	} else if (file_exists($file2)) {
		define('FANDIAN_APP_VER', substr(str_replace("\n", "", file_get_contents($file2)), 0, 16));
	} else {
		define('FANDIAN_APP_VER', '201112091309');
	}

	$bfile = dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'.git'.DIRECTORY_SEPARATOR.'HEAD';
	if (file_exists($bfile)) {
		$b = file_get_contents($bfile);
		$t = explode('/', $b);
		define('FANDIAN_APP_BRANCH', $t[count($t)-1]);
	} else {
		define('FANDIAN_APP_BRANCH', 'master');
	}
}

define('MSD_ONE_DAY', 86400);
define('MSD_START_TIME', microtime());
define('MSD_FORCE_RELOAD_CONFIG', true);
define('MSD_LOCAL_CACHER', getenv('MSD_LOCAL_CACHER') ? getenv('MSD_LOCAL_CACHER') : 'apc');
define('MSD_FORCE_CITY', getenv('MSD_FORCE_CITY') ? getenv('MSD_FORCE_CITY') : 'wuxi');

defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'application'));
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

set_include_path(implode(PATH_SEPARATOR, array(
realpath(APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'library'),
get_include_path(),
)));

require_once 'Msd' . DIRECTORY_SEPARATOR . 'Autoloader.php';
Msd_Autoloader::getInstance()->register();

Msd_Timer::start('application');

$config = &Msd_Config::getInstance();

Msd_Hook::run('BeforeBootStrap');

$heartbeat = dirname(__FILE__).'/dispatcher_'.APPLICATION_ENV;

if (file_exists($heartbeat) && fileatime($heartbeat)>(time()-30)) {
	echo "Server is healthy, so exit ...\n";
	exit(0);
} else {
	$dlvs = &Msd_Dao::table('deliveryman')->fetchAll('', '', 999);
	foreach ($dlvs as $dlv) {
		Msd_Cache_Remote::getInstance()->set(Msd_Waimaibao_Order_Dispatcher::dKey().'dmi_'.$dlv['DlvManId']);
	}

	Msd_Cache_Remote::getInstance()->set(Msd_Waimaibao_Order_Dispatcher::dKey().'chats');
	Msd_Cache_Remote::getInstance()->set(Msd_Waimaibao_Order_Dispatcher::dKey().'chats_start');
	
	$rows = Msd_Waimaibao_Order_Dispatcher::load();
	foreach ($rows as $row) {
		echo "Order Cache Cleared for ".$row['CityId'].$row['OrderId']."\n";
		Msd_Cache_Remote::getInstance()->set($row['CityId'].((string)$row['OrderId']));
	}
}

$i = 1;
while (true) {
	Msd_Waimaibao_Order_Dispatcher::load();
	
	echo "Loop ".($i++)."\n";
	sleep(15);
	touch($heartbeat);
}

unlink($heartbeat);
echo "\nExited.\n";