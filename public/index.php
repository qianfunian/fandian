<?php
/**
 * 启动器
 */
date_default_timezone_set('Asia/Shanghai');
define ('MSD_FORCE_CITY', getenv('MSD_FORCE_CITY') ? getenv('MSD_FORCE_CITY') : 'wuxi');

$img_url = MSD_FORCE_CITY == 'nanjing' ? '/common/images/njclose.jpg' : '/common/images/jiaqi.jpg';

$html = <<<EFO
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>饭店网</title>
	</head>
	
	<body style="margin:0px auto;width:960px">
	<img src="{$img_url}" />
	</body>
	</html>
EFO;

//if(time()>strtotime('2015-02-15 14:00:00') && time<strtotime('2015-02-26 08:00:00')){
//	echo $html;
//	exit;
//}

if (time() > strtotime('2015-01-15 21:00:00') && MSD_FORCE_CITY == 'nanjing') {
    echo $html;
    exit;
}
define ('FANDIAN_APP_VER', '4c95932f40b9ca06');
define ('MSD_ONE_DAY', 86400);
define ('MSD_START_TIME', microtime());
define ('MSD_FORCE_RELOAD_CONFIG', true);
define ('MSD_LOCAL_CACHER', getenv('MSD_LOCAL_CACHER') ? getenv('MSD_LOCAL_CACHER') : 'apc');

defined('APPLICATION_PATH') || define ('APPLICATION_PATH', realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'application'));
defined('APPLICATION_ENV') || define ('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'library'),
    get_include_path()
)));

require_once 'Msd' . DIRECTORY_SEPARATOR . 'Autoloader.php';
Msd_Autoloader::getInstance()->register();

Msd_Timer::start('application');

$config = &Msd_Config::getInstance();

Msd_Hook::run('BeforeBootStrap');

$application = new Msd_Application (APPLICATION_ENV, $config);
$application->bootstrap()->run();
