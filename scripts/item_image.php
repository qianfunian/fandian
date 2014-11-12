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

$args = $_SERVER['argv'];
unset($args[0]);

if (count($args)==1) {
	define('THIS_SAVE_PATH', $args[1]);
} else {
	//	导入的图片保存在哪里
	define('THIS_SAVE_PATH', PHP_OS=='WINNT' ? 'E:'.DIRECTORY_SEPARATOR.'cp'  : '/tmp/cp');
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
$SavePath = $config->attachment->save_path->items;

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
		$vendor = $vTable->getByName(c($VendorName), $config->db->guids->root_region);
		if ($vendor['VendorGuid']) {
			echo "Starting ".$VendorName." : ".$vendor['VendorGuid']."\n";
			$vPath = THIS_SAVE_PATH.'/'.$VendorName.'/';
			$vh = opendir($vPath);
			
			while(false!==($entry=readdir($vh))) {
				$fd = $vPath.$entry;
				if (!is_dir($fd)) {
					$_x = explode('.', $entry);
					unset($_x[count($_x)-1]);
					$fname = implode('.', $_x);
					
					$fname = c(trim($fname));
					$row = $iTable->getByName($fname, $vendor['VendorGuid']);
					
					if ($row['ItemGuid']) {
						$ItemGuid = $row['ItemGuid'];
						$ThisSavePath = $SavePath.'/' . $vendor['VendorGuid'];
						
						$storage->initDir($ThisSavePath);
						
						$ThisSavePath .= '/' . $ItemGuid.'.jpg';
						
						if ($storage->exists($ThisSavePath)) {
							$storage->rename($ThisSavePath, $ThisSavePath.'.bak');
						}
						
						$storage->save($fd, $ThisSavePath);
						
						if ($storage->exists($ThisSavePath.'.bak')) {
							$storage->del($ThisSavePath.'.bak');
						}
						
						Msd_Cache_Remote::getInstance()->set('item_url_'.$ItemGuid);
						
						$itemE = $ieTable->get($row['ItemGuid']);
						if ($itemE) {
							$ieTable->doUpdate(array(
									'HasLogo' => 1
							), $row['ItemGuid']);
						} else {
							$ieTable->insert(array(
									'ItemGuid' => $row['ItemGuid'],
									'HasLogo' => 1,
									'IsRec' => 0,
									'Sales' => 0,
									'Persisted' => '',
									'Detail' => '',
									'CityId' => $row['CityId']
							));
						}
						
						$imported++;
						echo $ItemGuid."\n";
					} else {
						$not_found_items[] = r($vendor['VendorName']).': '.r($fname);
					}
				}
			}
			
			closedir($vh);
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
//var_dump($not_found);
echo "\n";
//var_dump($not_found_items);
$fp = fopen(THIS_SAVE_PATH.'/itemimg.log', 'w');
fwrite($fp, serialize($not_found_items));
fclose($fp);
