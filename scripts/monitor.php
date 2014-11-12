<?php

/**
 * 服务状态监控
 * 
 * 
 */

if (getenv('FANDIAN_APP_VER')) {
	define('FANDIAN_APP_VER', getenv('FANDIAN_APP_VER'));
} else {
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
	realpath(APPLICATION_PATH . '/../library'),
	get_include_path(),	
	)));

require_once 'Msd/Autoloader.php';
Msd_Autoloader::getInstance()->register();

//	只在无锡站激活监控功能
if (MSD_FORCE_CITY!='wuxi') {
	exit(0);
}

Msd_Timer::start('application');

$config = &Msd_Config::getInstance();
$cConfig = &Msd_Config::cityConfig();

if ($cConfig->monitor->httpd->enabled) {
	$httpd = Msd_Monitor::factory('httpd');
	if (!$httpd->isAvailable()) {
		$httpd->sendAlert();
	}
}

if ($cConfig->monitor->httpd_sz->enabled) {
	$httpdSz = Msd_Monitor::factory('httpd_sz');
	if (!$httpdSz->isAvailable()) {
		$httpdSz->sendAlert();
	}
}

if ($cConfig->monitor->httpd_cz->enabled) {
	$httpdSz = Msd_Monitor::factory('httpd_cz');
	if (!$httpdSz->isAvailable()) {
		$httpdSz->sendAlert();
	}
}

echo "\nDone\n";