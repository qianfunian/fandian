<?php

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

$heartbeat = dirname(__FILE__).'/v12580_'.APPLICATION_ENV;

if (file_exists($heartbeat) && fileatime($heartbeat)>(time()-30)) {
	echo "Server is healthy, so exit ...\n";
	exit(0);
}

Msd_Timer::start('application');

$config = &Msd_Config::getInstance();
Msd_Hook::run('BeforeBootStrap');

$page = 1;
$limit = 100;
$rs = true;

$url = 'http://127.0.3.41/partner/v12580/items.xml';
$params = array(
	'limit' => $limit,
	'key' => 'baaaaaab',	
	'city' => '0510'
	);
$items_loaded = array();
$is = array();

while ($rs) {
	$start = Msd_Timer::start('item_check');
	
	$params['page'] = $page;
	$http = new Msd_Http_Client($url, array());
	$http->setParameterGet($params);
	$http->setHeaders(array(
		'Accept-Encoding' => 'gzip,deflate'	
		));
	
	try {
		$xml = $http->request('GET')->getBody();
		Msd_Log::getInstance()->debug($xml);
		
		$obj = simplexml_load_string($xml);
		
		$items = count($obj->item);
		
		if ($items>0) {
			foreach ($obj->item as $item) {
				$code = $item->code;
				$is[] = $code;
				
				if (!in_array($code, $items_loaded)) {
					$items_loaded[] = $code;
				} else {
					echo "Item Exists: ".$code.", ".iconv('utf-8', 'gbk', $item->name)."\n";
				}

				$iparams = array(
					'ItemGuid' => (string)$item->code,
					'VendorGuid' => (string)$item->vendor_code	
					);
				Msd_Dao::table('v12580temp')->insert($iparams);
			}
			
			unset($http);
			
			echo "Page: ".$page.", \tLimit: ".$limit.", \tItems: ".$items.", \tTimeCost: ".(Msd_Timer::end('item_check'))."\n";
			
			$page++;
		} else {
			$rs = false;
		}
	} catch (Exception $e) {
		echo "\n".$e->getMessage()."\n".$e->getTraceAsString()."\n";
		sleep(3);
		continue;
	}
}

$fp = fopen('D:\\is.log', 'w');
fwrite($fp, implode("\n", $is));
fclose($fp);

echo "\nDone\n";