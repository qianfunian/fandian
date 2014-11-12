<?php

/**
 * DAO调试
 * 
 * @author pang
 *
 */
class Msd_Dao_Debug
{
	public static $sql = array();
	
	public static function StartDebugTimer($timer)
	{
		Msd_Timer::start($timer);
	}
	
	public static function EndDebugTimer($timer)
	{
		$t = Msd_Timer::end($timer);
		$config = &Msd_Config::cityConfig();
		$limit = (float)$config->db->slow_query_time;
		$limit || $limit = 1;
		
		if ($t>$limit) {
			Msd_Log::getInstance()->sql_slow($timer.':'.$t);
		}
	}
}