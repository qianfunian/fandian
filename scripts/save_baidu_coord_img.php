<?php

/**
 * 保存地标的baidu地图图片
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
Msd_Hook::run('BeforeBootStrap');

$save_path = '\\\\192.168.1.92\images\images\coord\\';

$table = &Msd_Dao::table('coordinate');

$rows = $table->fetchAll('', '', 9999);

$txt = '';
$i = 1;
foreach ($rows as $row) {
	$CoordGuid = $row['CoordGuid'];
	
	$lon = $row['Longitude'];
	$lat = $row['Latitude'];
	
	$url = 'http://api.map.baidu.com/staticimage?width=280&height=208&center='.$lon.','.$lat.'&markers='.iconv('utf-8', 'gbk', '您的位置').'|'.$lon.','.$lat.'|'.iconv('utf-8', 'gbk', '您的位置').'&zoom=16';
	echo $url."\n";
	$txt .= 'wget "'.$url.'" -O '.$CoordGuid.'.png'."\n";
}

$cf = &Msd_Config::cityConfig();
$lon = $cf->longitude;
$lat = $cf->latitude;

$url = 'http://api.map.baidu.com/staticimage?width=280&height=208&center='.$lon.','.$lat.'&markers='.iconv('utf-8', 'gbk', '您的位置').'|'.$lon.','.$lat.'|'.iconv('utf-8', 'gbk', '您的位置').'&zoom=16';
$txt .= 'wget "'.$url.'" -O default.png'."\n";

$fp = fopen('d:\\coord.txt', 'w');
fwrite($fp, $txt);
fclose($fp);
echo "\nDone\n";