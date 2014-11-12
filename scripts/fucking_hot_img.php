<?php

/**
 * 批量菜品图片导入
 * 
 * @Usage: php item_image.php D:\\bbl
 */

function c($str)
{
	return PHP_OS=='WINNT' ? iconv('gbk', 'utf-8', $str) : $str;
}

function r($str)
{
	return PHP_OS=='WINNT' ? iconv('utf-8', 'gbk', $str) : $str;
}

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

$config = &Msd_Config::cityConfig();

$ieTable = &Msd_Dao::table('item/extend');
$i = 1;
$path = $config->attachment->save_path->items_big;
$handler = opendir($path);
while(false!==($VendorGuid=readdir($handler))) {
	if (Msd_Validator::isGuid($VendorGuid)) {
		$dpath = $path.'\\'.$VendorGuid.'\\';

		$dh = opendir($dpath);
		while(false!==($ItemGuid=readdir($dh))) {
			if (strlen($ItemGuid)>2) {
				echo $i.':'.$ItemGuid."\n";
				$ieTable->doUpdate(array(
					'IsRec' => '1'	
					), $ItemGuid);
			}
		}
		closedir($dh);
		
		$i++;
	}
}

closedir($handler);

echo "\nDone\n";
