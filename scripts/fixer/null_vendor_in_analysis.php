<?php

/**
 * 修正超时单分析中的商家名
 * 
 * 
 */

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

$config = &Msd_Config::getInstance();
$cityConfig = &Msd_Config::cityConfig();

Msd_Hook::run('BeforeBootStrap');

$cacher = &Msd_Cache_Remote::getInstance();

$oaTable = &Msd_Dao::table('order/analysis');
$oTable = &Msd_Dao::table('order');
$olvTable = &Msd_Dao::table('order/lastversion');
$ovTable = &Msd_Dao::table('order/version');
$sTable = &Msd_Dao::table('sales');
$svTable = &Msd_Dao::table('sales/version');
$odTable = &Msd_Dao::table('delivery/order');
$dlvTable = &Msd_Dao::table('deliveryman');
$fvTable = &Msd_Dao::table('freight/version');
$oslTable = &Msd_Dao::table('order/status/log');
$vTable = &Msd_Dao::table('vendor');
$vstTable = &Msd_Dao::table('vendor/servicetime');
$cTable = &Msd_Dao::table('customer');

$rows = $oaTable->nullVendorRows();

$i = 1;
foreach ($rows as $row) {
	$OrderGuid = $row['OrderGuid'];
	$OrderId = $row['OrderId'];
	
	$AddTime = $DeliveryedTime = $AssignedTime = $InformTime = $MinutesCost = $ReqDateTime = $LastChangeTime = $Deliver = $Distance = '';
	
	$Order = $oTable->get($OrderGuid);
	$OrderLastVersion = $olvTable->get($OrderGuid);
	$OrderVersion = $ovTable->get($OrderLastVersion['OrdVerGuid']);
	$Sales = $sTable->get($row['SalesGuid']);
	$SalesVersion = $svTable->get($OrderVersion['SalesVerGuid']);
	$VendorGuid = $row['VendorGuid'];
	$Vendor = $vTable->get($VendorGuid);
	$VendorName = $Vendor['VendorName'];

	$params = array(
		'VendorName' => $VendorName
		);
	$oaTable->doUpdate($params, $row['OrderGuid']);

	echo ($i++)." ".$VendorName." done\n";	
}

echo "\nDone\n";