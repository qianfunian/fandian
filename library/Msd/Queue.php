<?php

/**
 * 队列服务
 * 
 * @author pang
 *
 */

class Msd_Queue
{
	/**
	 * 获取队列服务实例
	 * 
	 * @param string $name
	 */
	public static function &getQueue($name)
	{
		$className = 'Msd_Queue_'.ucfirst(strtolower($name));
		
		return call_user_func(array(
				$className,
				'getInstance'
				));
	}
}