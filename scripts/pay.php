<?php

/**
 * 网上支付主动查询
 * 
 * @NOTE:
 * 为了避免频繁的数据库查询，该脚本每分钟运行一次
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
$hTable = &Msd_Dao::table('order/hash');
$opTable = &Msd_Dao::table('order/onlinepay');

$pager = array(
	'page' => 1,
	'limit' => 100,
	'skip' => 0
	);
$params = array(
	'PaymentMethod' => '1',
	'StatusId' => $config->order->status->posted,
	'TimeStart' => date('Y-m-d H:i:s', time()-MSD_ONE_DAY).'.000',
	'Payed' => 0
	);
$sort = array();

$rows = $oTable->search($pager, $params, $sort);
$parsedHash = array();

$i = 1;
foreach ($rows as $row) {
	$hash = $hTable->Order2Hash($row['OrderGuid']);
	
	$gateway = $hash['BankApi'];
	$handler = Msd_Service_Pay::factory($hash['BankApi']);
	
	$PayedMoney = $handler->query($row['OrderId']);
	if ($PayedMoney>0) {
		$orders = $hTable->getHashOrders($hash['Hash']);
		$total = 0;
		$rowsCount = count($orders);
		
		foreach ($orders as $order) {
			$OrderGuid = $order['OrderGuid'];
			$d = Msd_Waimaibao_Order::detail($OrderGuid);
			if ($d['order'] && !Msd_Waimaibao_Order::isCanceled($d['order']['StatusId'])) {
				$TotalAmount = $d['order']['TotalAmount'];
				$ThisMoney = ($rowsCount==1) ? $PayedMoney : (($PayedMoney - $total - $TotalAmount)>=0 ? $TotalAmount : ($PayedMoney - $total));
				$pay = $opTable->get($OrderGuid);
				
				$payInfo = array(
					'PayedMoney' => $ThisMoney, 
					'PayedVia' => $gateway
					);
				if ($pay) {
					$opTable->doUpdate($payInfo, $OrderGuid);
				} else {
					$payInfo['OrderGuid'] = $OrderGuid;
					$opTable->insert($payInfo);
				}
				
				$hashInfo = array(
					'Payed' => '1',
					'PayedMoney' => $ThisMoney
					);
				Msd_Dao::table('order/hash')->UpdateOrderStatus($hashInfo, $OrderGuid);
				
				$total += $TotalAmount;
			}
		}
	
		unset($handler);
	}
		
	echo "Loop ".($i++).", OrderId: ".$row['OrderId'].", PayedMoney: ".$PayedMoney."\n";
}

echo "\nDone (".count($rows).")\n";