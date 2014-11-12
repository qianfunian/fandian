<?php

/**
 * 异常处理
 * 
 * @author pang
 *
 */
class Msd_Exception extends Exception
{
	public function __construct($message='', $code=0)
	{
		parent::__construct($message, $code);
	}
}