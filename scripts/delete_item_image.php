<?php

/**
 * 删除不存在菜品的图片
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

Msd_Hook::run('BeforeBootStrap');

$config = &Msd_Config::cityConfig();

$vTable = &Msd_Dao::table('vendor');
$iTable = &Msd_Dao::table('item');
$ieTable = &Msd_Dao::table('item/extend');

$i = 1;
$dir = $config->attachment->save_path->items;
$dh = opendir($dir);
while(false!==($VendorGuid=readdir($dh))) {
	$VendorGuid = trim($VendorGuid);
	if (strlen($VendorGuid)>3) {
		$vd = $dir.DIRECTORY_SEPARATOR.$VendorGuid.DIRECTORY_SEPARATOR;
		$vdh = opendir($vd);
		while (false!==($img=readdir($vdh))) {
			$ItemGuid = str_replace('.jpg', '', trim($img));
			if (strlen($ItemGuid)>3) {
				if (strlen($ItemGuid)<16) {
					unlink($vd.$img);
					echo "Old img: ".$img."\n";
				} else {
					$item = $iTable->get($ItemGuid);
					if (!$item) {
						unlink($vd.$img);
						echo "Not exists img: ".$img."\n";
					}
				}
			}
		}
		closedir($vdh);
	}
	
	$i++;
}

closedir($dh);
echo "\nDone\n";