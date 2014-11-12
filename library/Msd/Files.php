<?php

/**
 * 文件处理
 * 
 * @author pang
 *
 */

class Msd_Files
{
	public static function &GetByHash($hash)
	{
		$rows = Msd_Dao::table('attachment')->getByHash($hash);
	
		return $rows;
	}
	
	/**
	 * 根据文件大小（字节数）格式化一个友好的文件尺寸字符
	 * 
	 * @param float $bytes
	 */
	public static function FormatSize($bytes)
	{
		$retval = "";
		
		if ($bytes >= 1048576) {
			$retval = round($bytes / 1048576 * 100 ) / 100 . 'MB';
		} else if ($bytes  >= 1024) {
			$retval = round($bytes / 1024 * 100 ) / 100 . 'KB';
		} else {
			$retval = $bytes . 'B';
		}
		
		return $retval;		
	}

	/**
	 * 根据文件类型解析文件类型图标
	 * 
	 * @param string $mime
	 */
	public static function &parseMimeIcon($mime)
	{
		switch ($mime) {
			case 'image/png':
			case 'image/jpeg':
			case 'image/pjpeg':
			case 'image/jpg':
			case 'image/gif':
				$icon = 'gif';
				break;
			default:
				$icon = 'unknown';
				break;
		}
		
		return $icon;
	}
	
	/**
	 * 获取数据库中附件的Meta信息
	 * 
	 * @param string $fid
	 */
	public static function &Meta($fid)
	{
		$cacher = &Msd_Cache_Remote::getInstance();
		$key = 'attachment_meta_'.$fid;
		$meta = $cacher->get($key);

		if (!$meta && $fid) {
			$attachTable = &Msd_Dao::table('attachment');
			$d = $attachTable->get($fid);			

			$meta['meta'] = &$d;
			$cacher->set($key, $meta);
		}
		
		return $meta;
	}
	
	/**
	 * 输出一个附件
	 * 
	 * @param string $fid
	 */
	public static function Output($fid)
	{
		$meta = &self::Meta($fid);

		if ($meta['meta']['FileId']) {
			$d = &$meta['meta'];
			$s = &$meta['data'];
			ob_end_clean();

			Msd_Dao::table('attachment')->increase('ReadTimes', $fid);

			header('Content-Type: '.$d['MimeType']);
			header('Content-Length: '.$d['Size']);
			header('Accpet-Ranges: bytes');
			header('Content-Disposition: attachment; filename='.$d['Name']);
			header('Etag: '.$d['FileId']);
			header('Cache-Control: no-store, no-cache, must-revalidate');
			header('Cache-Control: pre-check=0, post-check=0, max-age=0', false);
			header('Pragma: no-cache');
			header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
			header('Conection: keep-alive');
			
			if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) || !empty($_SERVER['HTTP_IF_NONE_MATCH'])) {
				header('HTTP/'.$_SERVER['HTTP_PROTOCOL'].' 304 Not Modified');
			}
			
			echo base64_decode($s['Data']);
			ob_end_flush();
		}
		
		exit(0);		
	}
	
	/**
	 * 删除附件
	 * 
	 * @param string $fid
	 */
	public static function Del($fid)
	{
		$dao = &Msd_Dao::table('attachment');
		$d = $dao->get($fid);
		
		if ($d) {
			$dao->doDelete($fid);
			
			$path = Msd_Config::cityConfig()->save_path->article.DIRECTORY_SEPARATOR;
			$path .= substr($fid, 0, 1).DIRECTORY_SEPARATOR.substr($fid,1, 1).DIRECTORY_SEPARATOR.$fid.'.'.$d['Ext'];
			
			if (file_exists($path)) {
				@unlink($path);
			}
		}
	}
}