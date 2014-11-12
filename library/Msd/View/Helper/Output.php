<?php

class Msd_View_Helper_Output
{
	
	public function Prepare()
	{
		Msd_Output::prepareHtml();
	}
	
	public function Outputer()
	{
		Msd_Output::doOutput();
	}
}