<?php

class Msd_Exception_Ajax extends Msd_Exception
{
	public function __construct($message='', $code=0)
	{
		parent::__construct($message, $code);
	}	
}