<?php

/**
 * 商家人均消费导入
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
Msd_Hook::run('BeforeBootStrap');

$conn = mysql_connect('192.168.1.251', 'root', '85860068');
mysql_select_db('waimaibao', $conn);
mysql_query('set names gbk');

$vTable = &Msd_Dao::table('vendor');
$veTable = &Msd_Dao::table('vendor/extend');

set_time_limit(0);
$i = 1;
$sql = "select sum(b.b_needmoney)/count(b.b_id) as total, b.b_dpcode, dp.dp_name
from tb_book as b
	inner join tb_msd_view_book as v
		on b.b_iduse=v.b_iduse
	inner join tb_dpoint as dp
		on dp.dp_code=b.b_dpcode
where b.b_sstatus!='9'
group by b.b_dpcode
";
$rs = mysql_query($sql, $conn);
$i = 1;

while ($r = mysql_fetch_array($rs)) {
	$VendorName = Msd_Iconv::g2u($r['dp_name']);
	$Vendor = $vTable->getByName($VendorName);

	if ($Vendor) {
		$VendorGuid = $Vendor['VendorGuid'];
		$ve = $veTable->get($VendorGuid);
		
		if ($ve) {
			$veTable->doUpdate(array(
				'AverageCost' => (int)$r['total']	
				), $VendorGuid);
		} else {
			$veTable->insert(array(
				'VendorGuid' => $VendorGuid,
				'AverageCost' => (int)$r['total'],
				'Views' => 100,
				'Favorited' => 0,
				'HotRate' => 1000,
				'HasLogo' => 0	
				));
		}
		
		echo ($i++).'.'.$r['dp_name']."\n";
	}
}

mysql_close($conn);


echo "\nDone.\n";