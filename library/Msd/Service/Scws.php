<?php

/**
 * Scws中文分词
 * 
 * @author pang
 *
 */

class Msd_Service_Scws extends Msd_Service_Base
{
	public static function split($word)
	{
		$cacher = &Msd_Cache_Remote::getInstance();
		$cacheKey = 'Scws_'.md5($word);
		$result = $cacher->get($cacheKey);
		
		if (!$result) {
			if (extension_loaded('scws')) {
				$sh = scws_open();
				scws_set_charset($sh, 'utf8');
				scws_send_text($sh, $word);
				$tmp = scws_get_tops($sh, 5);
				
				if ($tmp) {
					foreach ($tmp as $row) {
						if ($row['word']!=$word) {
							$result[] = $row['word'];
						}
					}
				} else {
					$result = array($word);
				}
				
				$cacher->set($cacheKey, $result);
			}
		}
		
		return $result;
	}
}