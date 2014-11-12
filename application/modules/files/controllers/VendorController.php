<?php

class Files_VendorController extends Msd_Controller_Files
{
	public function init()
	{
		parent::init();
	}
	
	public function __call($method, $params)
	{
		$pat = '/^([a-z0-9]{40})\.([a-z]{3,})Action$/';
		$pat2 = '/^([a-z0-9]{40})Action$/';
		$fid = '';
		
		if (preg_match($pat, $method)) {
			$fid = preg_replace($pat.'s', '\\1', $method);
		} else if (preg_match($pat2, $method)) {
			$fid = preg_replace($pat2.'s', '\\1', $method);
		}

		if ($fid) {
			Msd_Files::Output($fid);
		} else {
			
		}
		
		exit(0);
	}
	
}