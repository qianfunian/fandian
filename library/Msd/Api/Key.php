<?php

class Msd_Api_Key
{
	public static function GenNewKey()
	{
		$max = Msd_Dao::table('api/keys')->GetMax();
		$alpha = &Msd_Alpha::getInstance();

		return $alpha->C($max+rand(1000,9999));
	}
}