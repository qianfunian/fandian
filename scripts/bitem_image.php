<?php

/**
 * 批量菜品大图片导入
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
	define('THIS_SAVE_PATH', 'E:'.DIRECTORY_SEPARATOR.'cp');
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

$imported = 0;
$config = &Msd_Config::cityConfig();
$SavePath = $config->attachment->save_path->items_big;

Msd_Hook::run('BeforeBootStrap');

$vTable = &Msd_Dao::table('vendor');
$iTable = &Msd_Dao::table('item');
$ieTable = &Msd_Dao::table('item/extend');

$not_found = array();
$not_found_items = array();
$handler = opendir(THIS_SAVE_PATH);
$storage = &Msd_File_Storage::factory($config->attachment->save->protocol);
$storage->initDir($SavePath);

while(false!==($VendorName=readdir($handler))) {
	$VendorName = trim($VendorName);
	if ($VendorName!='.' && $VendorName!='..') {
		//根目录循环	START
		$VendorName = str_replace('.jpg', '', $VendorName);
		
		$tmp = explode('-', $VendorName);
		$VendorName = $tmp[0];
		unset($tmp[0]);
		$ItemName = implode('-', $tmp);
		$vendor = $vTable->getByName(c($VendorName));
		if ($vendor['VendorGuid']) {
			
			$item = $iTable->getByName(c($ItemName), $vendor['VendorGuid']);
			if ($item['ItemGuid']) {
				echo "Starting ".$VendorName." : ".$vendor['VendorGuid']."\n";
				$vSavePath = $SavePath.'/'.$vendor['VendorGuid'];
				
				$storage->mkdir($vSavePath);
				
				$from = THIS_SAVE_PATH.DIRECTORY_SEPARATOR.$VendorName.'-'.$ItemName.'.jpg';
				$to = $vSavePath.'/'.$item['ItemGuid'].'.jpg';
				
				if ($storage->exists($to)) {
					$storage->rename($to, $to.'.bak');
				}
				
				$storage->save($from, $to);
				
				if ($storage->exists($to.'.bak')) {
					$storage->del($to.'.bak');
				}
				
				$ie = $ieTable->get($item['ItemGuid']);
				
				if ($ie['ItemGuid']) {
					$ieTable->doUpdate(array(
						'HasLogo' => 1,
						'IsRec' => 1	
						), $item['ItemGuid']);
				} else {
					$ieTable->insert(array(
						'ItemGuid' => $item['ItemGuid'],
						'HasLogo' => 1,
						'IsRec' => 1	
						));
				}
				
				echo $VendorName.' : '.$ItemName."\n";
				$imported ++;
			} else {
				$not_found[] = $VendorName.': '.$ItemName;
			}
		} else {
			$not_found[] = $VendorName.': '.$ItemName;
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
//var_dump($not_found);
echo "\n";
//var_dump($not_found_items);
$fp = fopen(THIS_SAVE_PATH.'/bitemimg.log', 'w');
fwrite($fp, serialize($not_found));
fclose($fp);
