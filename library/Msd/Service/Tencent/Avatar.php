<?php

class Msd_Service_Tencent_Avatar extends Msd_Service_Tencent_Base
{
	/**
	 * 将QQ头像保存到用户资料
	 * 
	 * @param string $url
	 */
	public static function save(array $params)
	{
		$url = $params['url'];
		$CustGuid = $params['CustGuid'];
		$usage = 'avatar';
		
		$result = array();
		
		try {
			$http = new Msd_Http_Client($url, array());
			$binary = $http->request()->getBody();
			$ContentType = $http->getLastResponse()->getHeader('Content-type');
			$ContentLength = $http->getLastResponse()->getHeader('Content-length');
			
			switch ($ContentType) {
				case 'image/png':
					$ext = 'png';
					break;
				case 'image/gif':
					$ext = 'gif';
					break;
				default:
					$ext = 'jpg';
					break;
			}
			
			$fid = sha1(uniqid(mt_rand()));
			$hash = sha1(uniqid(mt_rand()));
			$tmp_name = Msd_Config::cityConfig()->system->tmp_dir.DIRECTORY_SEPARATOR.$fid.'.'.$ext;
				
			$fp = fopen($tmp_name, 'w');
			fwrite($fp, $binary);
			fclose($fp);
			
			$width = $height = 0;
			list($width, $height) = @getimagesize($tmp_name);
			
			$config = &Msd_Config::appConfig()->toArray();
			
			$s = array();
			$s['FileId'] = $fid;
			$s['Name'] = 'QQ_Avatar';
			$s['MimeType'] = $ContentType;
			$s['Size'] = $ContentLength;
			$s['Uid'] = $CustGuid;
			$s['Hash'] = $hash;
			$s['Description'] = '';
			$s['Ext'] = $ext;
			$s['Usage'] = (int)$config['attachment']['usage'][$usage];
			$s['Width'] = $width;
			$s['Height'] = $height;
			
			Msd_Dao::table('attachment')->insert($s);
			
			$SavePath = $config['attachment']['save_path'][$usage];
			$SavePath .= substr($fid, 0, 1).DIRECTORY_SEPARATOR.substr($fid, 1, 1).DIRECTORY_SEPARATOR;
			
			if (!is_dir($SavePath)) {
				mkdir($SavePath, 0777, true);
			}
			
			$SaveFile = $SavePath.$fid.'.'.$ext;
			copy($tmp_name, $SaveFile);
			
			$result = array();
			$result['hash'] = $hash;
			$result['file_id'] = $fid;
			$result['ext'] = $s['Ext'];
			$result['tmp_name'] = $SaveFile;
		} catch (Exception $e) {
			Msd_Log::getInstance()->exception($e->getMessage()."\n".$e->getTraceAsString());
		}
		
		return $result;
	}
}