<?php

/**
 * WCF相关通信的异步处理
 * 
 */

define('MSD_ONE_DAY', 86400);
define('MSD_START_TIME', microtime());
define('MSD_FORCE_RELOAD_CONFIG', true);
define('MSD_LOCAL_CACHER', getenv('MSD_LOCAL_CACHER') ? getenv('MSD_LOCAL_CACHER') : 'apc');
define('MSD_FORCE_CITY', getenv('MSD_FORCE_CITY') ? getenv('MSD_FORCE_CITY') : 'wuxi');

defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__).'/../application'));
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

$heartbeat = dirname(__FILE__).'/wcf_order_'.APPLICATION_ENV;

if ($cConfig->wcf->enabled && file_exists($heartbeat) && fileatime($heartbeat)>(time()-30)) {
	echo "Server is healthy, so exit ...\n";
	exit(0);
}

Msd_Hook::run('BeforeBootStrap');

$cacher = &Msd_Cache_Remote::getInstance();
$key = 'wcf_orders';
$i = 1;

while (true) {
	$os = $cacher->get($key);
	$os || $os = array();
	var_dump($os);
	if (is_array($os) && count($os)>0) {
		foreach ($os as $OrderId) {
			$wcf = new Msd_Service_Wcf_Order();
			$result = $wcf->RegisterUnconfirmWebOrder($OrderId);
			echo $OrderId." done\n";
			$result && Msd_Log::getInstance()->wcfscript($OrderId);
			unset($wcf);
		}
		
		$cacher->delete($key);
	}
	
	echo "Loop ".($i++)."\n";
	
	touch($heartbeat);
	sleep(3);
}

echo "\nDone.\n";