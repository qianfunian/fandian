<?php

/**
 * 批量商家Logo导入
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

$args = $_SERVER['argv'];
unset($args[0]);

if (count($args)==1) {
	define('THIS_SAVE_PATH', $args[1]);
} else {
	//	导入的图片保存在哪里
	define('THIS_SAVE_PATH', 'E:'.DIRECTORY_SEPARATOR.'logo');
}

define('THIS_LOGO_TYPE', getenv('THIS_LOGO_TYPE') ? getenv('THIS_LOGO_TYPE') : 'small');
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

$imported = 0;
$config = &Msd_Config::cityConfig();

switch (THIS_LOGO_TYPE) {
	case 'big':
		$SavePath = $config->attachment->save_path->vendor_big;
		break;
	default:
		$SavePath = $config->attachment->save_path->vendor;
		break;
}

Msd_Hook::run('BeforeBootStrap');

$vTable = &Msd_Dao::table('vendor');
$veTable = &Msd_Dao::table('vendor/extend');

$not_found = array();
$handler = opendir(THIS_SAVE_PATH);
$storage = &Msd_File_Storage::factory($config->attachment->save->protocol);
$storage->initDir($SavePath);
while(false!==($VendorName=readdir($handler))) {
	$VendorName = trim($VendorName);
	if ($VendorName!='.' && $VendorName!='..') {
		$VendorName = strtolower($VendorName);
		$VendorName = str_replace('.jpg', '', $VendorName);
		//根目录循环	START
		$vendor = $vTable->getByName(c($VendorName));
		if ($vendor['VendorGuid']) {
			$fd = THIS_SAVE_PATH . DIRECTORY_SEPARATOR.$VendorName.'.jpg';
			$ThisSavePath = $SavePath . $vendor['VendorGuid'].'.jpg';

			if ($storage->exists($ThisSavePath)) {
				$storage->rename($ThisSavePath, $ThisSavePath.'.bak');
			}
			
			$storage->save($fd, $ThisSavePath);
			
			if ($storage->exists($ThisSavePath.'.bak')) {
				$storage->del($ThisSavePath.'.bak');
			}

			$ve = $veTable->get($vendor['VendorGuid']);
			if ($ve) {
				$veTable->doUpdate(array(
					'HasLogo' => '1'	
					), $vendor['VendorGuid']);
			} else {
				$veTable->insert(array(
					'VendorGuid' => $vendor['VendorGuid'],
					'Views' => 0,
					'Favorites' => 0,
					'SmallLogo' => '',
					'BigLogo' => '',
					'AverageCost' => 0,
					'HotRate' => 1000,
					'HasLogo' => '1',
					'IsRec' => 0,
					'IsIdxRec' => 0	
					));
			}
			
			echo $VendorName.' imported.'."\n";
			$imported++;
		} else {
			$not_found[] = $VendorName;
		}
		
		//	根目录循环	END
	}
}

$storage->close();
closedir($handler);

echo "\nDone.\n";
echo "Total:".$imported."\n";
echo "\n-----------------------------\n";
echo "Not found: \n";
echo "\n";
//var_dump($not_found_items);
$fp = fopen(THIS_SAVE_PATH.'/logo.log', 'w');
fwrite($fp, serialize($not_found_items));
fclose($fp);
