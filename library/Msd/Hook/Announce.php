<?php

class Msd_Hook_Announce extends Msd_Hook_Base
{
	protected static $instance = null;
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function NewOrderCreated(array $params)
	{
		$cConfig = &Msd_Config::cityConfig();
		$config  = &Msd_Config::appConfig();
		
		$OrderGuid     = $params['OrderGuid'];
		$CityId        = $params['CityId'];
		$VendorName    = $params['VendorName'];
		$VendorGuid    = $params['VendorGuid'];
		$FirstItemName = $params['FirstItemName'];
		
		if ($VendorGuid!=$cConfig->db->guids->mini_market && $FirstItemName!=$config->db->n->item_name->mifan) {
			$CustName = $params['CustName'];
			
			$Content = Msd_Iconv::usubstr($CustName, 0, 1).'**';
			$Content .= ' 在 '."<a href='/vendor/".$VendorName."'>".Msd_Waimaibao_Vendor::FilterVendorName($VendorName)."</a>".' 点了 <span class="oa_item">'.$FirstItemName.'</span> 等美味';
			
			try {
				Msd_Dao::table('order/announce')->insert(array(
					'Content' => $Content,
					'RegionGuid' => $cConfig->root_region,
					'CityId' => $CityId
					));
				
				Msd_Cache_Clear::orderAnnounce();
			} catch (Exception $e) {
				Msd_Log::getInstance()->hook($e->getMessage()."\n".$e->getTraceAsString());	
			}
		}
	}	
}
