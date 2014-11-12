<?php 

class Msd_Lsadds
{
	public static function getlsadds($uid,$phone)
	{
		
		
		$cacher   = &Msd_Cache_Remote::getInstance();
		$cacheKey = 'lsas';
		$data   = $cacher->get($cacheKey);
		
		if(!$data[$phone]) {
			$city_config = Msd_Config::cityConfig();
			
			$sql = "select distinct CustAddress,temp.CoordGuid,temp.CoordName,lat,long from
(SELECT TOP 5 CustAddress,CoordGuid,CoordName,CoordValue.Lat as lat
,CoordValue.Long as long,AddTime  FROM [Sales] where CustGuid in((SELECT CustGuid FROM [CustomerPhone] where PhoneNumber='".$phone."')) AND
CoordGuid is not null and CoordName is not null and AreaGuid ='".$city_config->root_region."' order by AddTime desc) as temp right join Coordinate on temp.CoordGuid=Coordinate.CoordGuid where CustAddress is not null";
			$lsadds=Msd_Dao::table('sales')->query($sql);
			
			$html=$lsadds?"<option value='0'>选择历史订餐地址</option>":'';
			foreach($lsadds as $row)
			{
				$html .= '<option value='.$row['CoordName'].','.$row['CustAddress'].','.$row['CoordGuid'].','.$row['lat'].','.$row['long'].'>'.$row['CoordName'].$row['CustAddress'].'</option>';
			}
			
			$data[$phone] = $html;
			$cacher->set($cacheKey, $data);
		}
		
		return $data[$phone];
		
	}
}