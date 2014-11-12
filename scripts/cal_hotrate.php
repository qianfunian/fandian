<?php

/**
 * 每天重新计算商家的HotRate
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

$oTable = &Msd_Dao::table('order');
$vTable = &Msd_Dao::table('vendor');
$veTable = &Msd_Dao::table('vendor/extend');
$oiTable = &Msd_Dao::table('order/item');
$ieTable = &Msd_Dao::table('item/extend');

$pager = array(
	'page' => 1,
	'limit' => 1000,
	'skip' => '0'	
	);

$where = array(
	'TimeStart' => date('Y-m-d', time()-MSD_ONE_DAY).' 00:00:00'
	);

$sort = array();

$orders = $oTable->search($pager, $where, $sort);

foreach ($orders as $order) {
	$OrderGuid = $order['OrderGuid'];
	
	$VendorName = $order['VendorName'];
	$vendor = $vTable->getByName($VendorName);
	
	$Total = (int)$order['SumAmount'];
	$VendorGuid = $vendor['VendorGuid'];

	if ($VendorGuid) {
		$ve = $veTable->get($VendorGuid);
		$veTable->doUpdate(array(
			'HotRate' => (int)$ve['HotRate']+$Total	
			), $VendorGuid);
	}
	
	if ($OrderGuid) {
		//	更新菜品售出数量
		$items = $oiTable->getOrderItems($OrderGuid);
		foreach ($items as $item) {
			$TotalAmount = $item['ItemPrice']>0 ? (int)($item['ItemAmount']/$item['ItemPrice']) : 1;
			$ItemGuid = $item['ItemGuid'];
			
			$ieTable->increaseSales($ItemGuid, $TotalAmount);
		}
	}
	
	echo $order['OrderId'].' done'."\n";
}

echo "\nDone\n";