<?php

/**
 * 批量菜品图片检测
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

$items = $iTable->all();
$i = 1;
$str = "";
foreach ($items as $item) {
	$itemImg = $config->attachment->save_path->items;
	$itemImg .= $item['VendorGuid'].DIRECTORY_SEPARATOR.$item['ItemGuid'].'.jpg';

	if (!file_exists($itemImg)) {
		echo $i.": ".$item['ItemName'].' : '.$itemImg."\n";
		$i++;
	
		$itemE = $ieTable->get($item['ItemGuid']);
		if ($itemE) {
			$ieTable->doUpdate(array(
					'HasLogo' => 0
				), $item['ItemGuid']);
		} else {
			$ieTable->insert(array(
				'ItemGuid' => $item['ItemGuid'],
				'HasLogo' => 0
				));
		}
		
		$str .= "UPDATE W_ItemExtend SET HasLogo='0' WHERE ItemGuid='".$item['ItemGuid']."';\n";
	}
}

echo "\nDone\n";