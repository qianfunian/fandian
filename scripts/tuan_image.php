<?php

/**
 * 精品套餐批量菜品图片导入
 * 
 * @usage: php tuan_image.php
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
	define('THIS_SAVE_PATH', 'E:'.DIRECTORY_SEPARATOR.'tuan');
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
$SavePath = $config->attachment->save_path->items_tuan;	

Msd_Hook::run('BeforeBootStrap');

$vTable = &Msd_Dao::table('vendor');
$iTable = &Msd_Dao::table('item');
$ieTable = &Msd_Dao::table('item/extend');

$not_found = array();
$not_found_items = array();
$handler = opendir(THIS_SAVE_PATH);
$storage = &Msd_File_Storage::factory($config->attachment->save->protocol);
while(false!==($VendorName=readdir($handler))) {
	$VendorName = trim($VendorName);
	if ($VendorName!='.' && $VendorName!='..') {
		//根目录循环	START
		$vendor = $vTable->getByName(c($VendorName));
		if ($vendor['VendorGuid']) {
			echo "Starting ".$VendorName." : ".$vendor['VendorGuid']."\n";
			$vPath = THIS_SAVE_PATH.DIRECTORY_SEPARATOR.$VendorName.DIRECTORY_SEPARATOR;
			$vh = opendir($vPath);

			while(false!==($entry=readdir($vh))) {
				$fd = $vPath.$entry;

				if ($entry!='.' && $entry!='..' && !is_dir($entry) && strtolower($entry!='thumbs.db')) {
					$fname = $entry;

					$fname = c(trim($fname));
					$row = $iTable->getByName($fname, $vendor['VendorGuid']);

					if ($row['ItemGuid']) {
						$ItemGuid = $row['ItemGuid'];
						$ThisSavePath = $SavePath.$vendor['VendorGuid'];

						$storage->initDir($ThisSavePath);
						
						$ThisSavePath .= '/' . $ItemGuid;
						$ThisBigFile = $ThisSavePath.'.jpg';
						
						$storage->initDir($ThisSavePath);

						$bfile = $fd.'.jpg';

						if ($storage->exists($ThisBigFile)) {
							$storage->del($ThisBigFile);
						}
						
						$storage->save($bfile, $ThisBigFile);

						$gfd = $fd.DIRECTORY_SEPARATOR.r($entry);

						$gfdh = opendir($gfd);
						$gs = array();
						while(false!==($entry2=readdir($gfdh))) {
							if ($entry2!='.' && $entry2!='..') {
								$gs[] = $entry2;
							}
						}
						closedir($gfdh);

						sort($gs);
						foreach ($gs as $g) {
							list($fname, $ext) = explode('.', $g);
							$nfile = $ThisSavePath.'/'.$fname.'.jpg';

							if ($storage->exists($nfile)) {
								$storage->del($nfile);
							}
							
							$_from = $gfd.DIRECTORY_SEPARATOR.$g;
							$_to = $nfile;
							$storage->save($_from, $_to);
						}
						
						$imported++;
						
						$ieTable->doUpdate(array(
							'HasLogo' => 1	
							), $ItemGuid);
						
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
