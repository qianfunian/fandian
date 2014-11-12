<?php

class Msd_Format
{
	public static function money($money)
	{
		return round((float)$money, 1);
	}
}