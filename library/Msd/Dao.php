<?php

/**
 * 数据处理层
 * 
 */
require_once 'Zend/Db.php';

class Msd_Dao
{
	protected static $instances = array();
	protected static $dbConnections = array();
	protected static $config = null;
	protected static $dbMap = array(
			'region' => 'server',
			'vendor' => 'server',
			'vendor/address' => 'server',
			'vendor/servicetime' => 'server',
			'vendor/confirm' => 'server',
			'item' => 'server',
			'item/unit' => 'server',
			'item/soldout' => 'server',
			'category/group' => 'server',
			'category/standard' => 'server',
			'customer/phone' => 'server',
			'customer/address' => 'server',
			'customer' => 'server',
			'category' => 'server',
			'enum' => 'server',
			'region' => 'server',
			'cancelreasons' => 'server',
			'order/onlinepay' => 'server',
			'order' => 'server',
			'order/version' => 'server',
			'order/status' => 'server',
			'order/iiversion' => 'server',
			'sales' => 'server',
			'sales/version' => 'server',
			'sales/attribute' => 'server',
			'order/deliveryman' => 'server',
			'order/deliveryman/version' => 'server',
			'order/payment' => 'server',
			'payment' => 'server',
			'freight/version' => 'server',
			'freight' => 'server',
			'order/item' => 'server',
			'order/itemversion' => 'server',
			'coordinate' => 'server',
			'sort/group' => 'server',
			'deliveryman' => 'server',
			'category/groupmember' => 'server',
			'order/status/log' => 'server',
			'delivery/order' => 'server',
			'order/cancel' => 'server',
			'order/cancelreasons' => 'server',
			'issuelastversion' => 'server',
			'chat' => 'server',
			'distribution/center' => 'server',
			'historygps' => 'server',
			'historyrawgps' => 'server',
			'checkout' => 'server',
			'service' => 'server',
			'service/item' => 'server',
			'service/group' => 'server',
			'sendtask' => 'sms',
 			'zvendorexpand' => 'server',
			'active' => 'server',
			'giftticket' => 'server'
			);
	public static $wQueries = 0;
	public static $rQueries = 0;
	public static $compatInsert = false;
	
	public static function &getInstance($db)
	{
		if (!isset(self::$instances[$db])) {
			self::loadConfig();
			
			self::$instance[$db] = &call_user_func(array(
					'Msd_Dao_'.ucfirst(strtolower($db)),
					'getInstance'
					));
		}
		
		return self::$instances[$db];
	}
	
	protected static function loadConfig()
	{
		if (!self::$config) {
			self::$config = &Msd_Config::cityConfig()->db;
		}
	}
	
	/**
	 * 分配数据库连接
	 * 
	 * @param string $dbServer
	 */
	public static function &assignDbConnection($dbServer='server')
	{
		self::loadConfig();

		$dbServer = 'server';
		$config = self::$config->toArray();
		$config = $config[$dbServer];
		$hash = md5($config['host'].$config['port'].$config['dbname'].$config['user'].$config['password'].$config['adapter'].$config['charset']);
		
		if ($config['options']) {
			$options = explode('|', $config['options']);
			if (count($options)>0) {
				$config['driver_options'] = array();
				
				foreach ($options as $option) {
					list($optionName, $optionValue) = explode(':', $option);
					$config['driver_options'][$optionName] = $optionValue;
				}
			}
		}

		if (!isset(self::$dbConnections[$hash])) {
			try {
				self::$dbConnections[$hash] = &Zend_Db::factory(strtoupper($config['adapter']), $config);
			} catch (Exception $e) {
				throw new Msd_Exception($e->getMessage());
			}
		}
		
		return self::$dbConnections[$hash];
	}
	
	public static function unassignDbConnection($dbServer='server')
	{
		self::loadConfig();

		$dbServer = 'server';
		$config = self::$config->toArray();
		$config = $config[$dbServer];
		$hash = md5($config['host'].$config['port'].$config['dbname'].$config['user'].$config['password'].$config['adapter'].$config['charset']);
		
		if (isset(self::$dbConnections[$hash])) {
			unset(self::$dbConnections[$hash]);
		}
	}
	
	public static function dbName($dbServer)
	{
		self::loadConfig();
		
		$config = self::$config->toArray();
		$config = $config[$dbServer];

		return $config['dbname'] ? $config['dbname'] : '';
	}
	
	/**
	 * 返回一个DataTable对象
	 * 
	 * @param string $tbl
	 * @param string $source
	 */
	public static function &table($tbl, $source='web')
	{
		if ($source=='web' && isset(self::$dbMap[$tbl])) {
			$source = self::$dbMap[$tbl];
		}
		
		$suffix = array();
		$tmp = explode('/', $tbl);
		foreach ($tmp as $tbl) {
			$suffix[] = ucfirst(strtolower($tbl));
		}
		
		$className = 'Msd_Dao_Table_'.ucfirst(strtolower($source)).'_'.implode('_', $suffix);

		return call_user_func(array(
				&$className,
				'getInstance'
				));
	}
	
	/**
	 * 返回事务对象
	 * 
	 * @param string $dbServer
	 */
	public static function &transaction($dbServer='meishida')
	{
		return new Msd_Dao_Transaction(self::assignDbConnection($dbServer));
	}
	
	/**
	 * 计算页数
	 * 
	 * @param unknown_type $total
	 * @param unknown_type $limit
	 */
	public static function &Pages($total, $limit)
	{
		$limit <=0 && $limit = 20;
		
		return intval($total/$limit)==ceil($total/$limit) ? intval($total/$limit) : ceil($total/$limit);
	}
}
