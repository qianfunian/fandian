<?php

/**
 * 超时单分析
 * 
 * @NOTE:
 * 为了避免频繁的数据库查询，该脚本每10分钟运行一次
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
$cityConfig = &Msd_Config::cityConfig();

Msd_Hook::run('BeforeBootStrap');

$locker = dirname(__FILE__).'/analysis_'.APPLICATION_ENV;

if (file_exists($locker) && fileatime($locker)>(time()-30)) {
	echo "Server is running, so exit ...\n";
	exit(0);
}

$cacher = &Msd_Cache_Remote::getInstance();

$oaTable = &Msd_Dao::table('order/analysis');
$oTable = &Msd_Dao::table('order');
$ovTable = &Msd_Dao::table('order/version');
$sTable = &Msd_Dao::table('sales');
$odTable = &Msd_Dao::table('delivery/order');
$dlvTable = &Msd_Dao::table('deliveryman');
$oslTable = &Msd_Dao::table('order/status/log');
$vTable = &Msd_Dao::table('vendor');
$vstTable = &Msd_Dao::table('vendor/servicetime');
$cTable = &Msd_Dao::table('customer');

$from_param = $oaTable->lastOrderTime();
$rows = &$oTable->lastCompleted($from_param);

$i = 1;
foreach ($rows as $row) {
	$RecalFlag = '0';
	$OrderGuid = $row['OrderGuid'];
	$OrderId = $row['OrderId'];
	
	$AddTime = $DeliveryedTime = $AssignedTime = $InformTime = $MinutesCost = $ReqDateTime = $LastChangeTime = $Deliver = $Distance = '';
	
	$Order = $oTable->get($OrderGuid);
	$Sales = $sTable->get($row['SalesGuid']);
	$VendorGuid = $row['VendorGuid'];
	$InService = $vTable->InService($VendorGuid, $row['AddTime']);
	$VendorName = $row['VendorName'];
	
	$Source = substr($OrderId, 0, 1)=='W' ? 'web' : 'tel';
	
	//	确认时间
	$tmp = $oslTable->getStatusTime($OrderGuid, $config->msd->order->status->confirmed);
	$AddTime = trim($tmp['AddTime'] ? $tmp['AddTime'] : '2000-01-01 00:00:00');

	//	送达时间
	$tmp = $oslTable->getLastStatusTime($OrderGuid, $config->msd->order->status->delivered);
	$DeliveryedTime = $tmp['AddTime'] ? $tmp['AddTime'] : $row['AddTime'];
	
	//	分配速递时间
	$tmp = $oslTable->getStatusTime($OrderGuid, $config->msd->order->status->assigned);
	$AssignedTime = $tmp['AddTime'] ? $tmp['AddTime'] : '2000-01-01 00:00:00';
	
	//	最后一次下单完成时间
	$tmp = $oslTable->getLastStatusTime($OrderGuid, $config->msd->order->status->issued);
	$InformTime = $tmp['AddTime'];
	
	//	预定时间
	$rdtt = 0;
	if (strlen(trim($Order['ReqTimeStart']))>0) {
		$ReqDateTime = $Sales['ReqDate'].' '.substr($Order['ReqTimeStart'], 0, -4);
		$rdt = new DateTime($ReqDateTime);
		$rdtt = $rdt->getTimestamp();
	}

	//	最后改单时间
	$tmp = $ovTable->lastItemChange($OrderGuid);
	if ($tmp) {
		$LastChangeTime = $tmp['LastChange'];
	}
	
	//	耗时
	$lctt = 0;
	if ($LastChangeTime) {
		$lct = new DateTime($LastChangeTime);
		$lctt = $lct->getTimestamp();
	}
	
	$at = new DateTime($AddTime);
	$att = $at->getTimeStamp();
	
	$istt = 0;
	if (!$InService) {
		$tmp = $vstTable->nextServiceTime($VendorGuid, substr($row['AddTime'], 11, 8));
		$ist = new DateTime(substr($row['AddTime'], 0, 10).' '.substr($tmp['StartTime'], 0, 8));
		$istt = $ist->getTimestamp();
	}
	
	$StartTime = max($lctt, $att, $istt);
	$ed = new DateTime($DeliveryedTime);
	$EndTime = $ed->getTimestamp();
	$SecondsCost = $EndTime-$StartTime;
	$MinutesCost = intval($SecondsCost/60);
	
	//	速递
	$od = $odTable->getOrderDeliver($OrderGuid);
	$DlvManGuid = $od['DlvManGuid'];
	$DlvMan = $dlvTable->get($DlvManGuid);
	$Deliver = $DlvMan['DlvManName'] ? $DlvMan['DlvManName'] : '--';
	
	//	运费
	$Distance = $Order['Distance'];
	
	//	是否是一级客户
	$IsVip = 0;
	$Customer = $cTable->get($Sales['CustGuid']);
	if ($Customer['CtgGroupGuid'] && $Customer['CtgGroupGuid']==$cityConfig->db->guids->customer->vip) {
		$IsVip = 1;
	}
	
	//	是否超时判断
	$IsTimeout = 0;
	if ($ReqDateTime) {
		intval($EndTime-$rdtt)>=5*60 && $IsTimeout = 1;
	} else {
		if ($Distance<3000) {
			$SecondsCost>=60*60 && $IsTimeout = 1;
		} else if ($Distance>=3000 && $Distance<5000) {
			$SecondsCost>=60*70 && $IsTimeout = 1;
		} else if ($Distance>5000) {
			$SecondsCost>=60*80 && $IsTimeout = 1;
		}
	}
	
	$params = array(
		'OrderId' => $OrderId,
		'AddTime' => $AddTime,
		'DeliveryedTime' => $DeliveryedTime,
		'AssignedTime' => $AssignedTime,
		'InformTime' => $InformTime,
		'MinutesCost' => $MinutesCost,
		'Deliver' => $Deliver,
		'Distance' => $Distance,
		'RecalFlag' => '0'	,
		'IsVip' => $IsVip,
		'IsTimeout' => $IsTimeout,
		'RealAddTime' => $row['AddTime'],
		'VendorName' => $VendorName
		);
	$LastChangeTime && $params['LastChangeTime'] = $LastChangeTime;
	$ReqDateTime && $params['ReqDateTime'] = &$ReqDateTime;

	if ($row['oaOrderGuid']) {
		$oaTable->doUpdate($params, $row['OrderGuid']);
	} else {
		$params['OrderGuid'] = $row['OrderGuid'];

		$oaTable->insert($params);
	}

	echo ($i++)." done\n";	
	touch($locker);
}

echo "\nDone\n";
