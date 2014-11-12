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

$heartbeat = dirname(__FILE__).'/dispatchergps_'.APPLICATION_ENV;

if (file_exists($heartbeat) && fileatime($heartbeat)>(time()-30)) {
	echo "Server is healthy, so exit ...\n";
	exit(0);
}

$i = 1;
while (true) {
	$dao = &Msd_Dao::table('historyrawgps');
	$rows = $dao->tobeTrans();
	
	foreach ($rows as $row) {
		$lng = $lat = 0;
		$hb = new DateTime(trim($row['AddTime']));
		$url = 'http://api.map.baidu.com/ag/coord/convert?from=0&to=4&x='.$row['Longitude'].'&y='.$row['Latitude'];
		$client = new Msd_Http_Client($url);
		$response = $client->request();
		$data = json_decode($response->getBody());
		
		$lng = base64_decode($data->y);
		$lat = base64_decode($data->x);
		
		$dao->doUpdate(array(
			'Flag' => '1'	
			), $row['AutoId']);
		
		Msd_Dao::table('deliveryman')->doUpdate(array(
			'LastLongitude' => $lng,
			'LastLatitude' => $lat,
			'LastHeartBeat' => date('Y-m-d H:i:s', $hb->getTimestamp()).'.000'
			), $row['DlvManGuid']);
		
		Msd_Dao::table('historygps')->insert(array(
			'HeartBeatTime' => date('Y-m-d H:i:s', $hb->getTimestamp()).'.000',
			'DlvManId' => $row['DlvManId'],
			'Longitude' => $lng,
			'Latitude' => $lat,
			));
	}
	
	echo "Loop ".($i++)."\n";
	sleep(15);
	touch($heartbeat);
}

unlink($heartbeat);
echo "\nExited.\n";