<?php

class Msd_Hook_Image extends Msd_Hook_Base
{
	protected static $instance = null;
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function MemberChanged(array $params=array())
	{
		$uid = $params['CustGuid'];

		if (isset($params['params']['Avatar']) && $params['params']['Avatar']!='') {
			
			$tmp_name = isset($params['params']['file']['tmp_name']) ? $params['params']['file']['tmp_name'] : $params['params']['tmp_name'];
			
			$config = &Msd_Config::appConfig()->attachment;
			$cConfig = &Msd_Config::appConfig()->attachment;
			
			$handler = Msd_Image::getHandler($tmp_name);
			$meta = Msd_Files::Meta($params['params']['AvatarId']);

			if (count($meta['meta'])>0) {
				//	normal
				$mw = $config->avatar->normal->width;
				$mh = $config->avatar->normal->height;
				$normal = $handler->scale($mw, $mh);
				
				$fid = sha1(uniqid(mt_rand()));
				$s = array();
				$s['FileId'] = $fid;
				$s['Name'] = $meta['meta']['Name'];
				$s['MimeType'] = $meta['meta']['MimeType'];
				$s['Size'] = $handler->size;
				$s['Uid'] = $uid;
				$s['Hash'] = $params['params']['Avatar'];
				$s['Description'] = '';
				$s['Ext'] = $meta['meta']['Ext'];
				$s['Usage'] = $config->usage->avatar_normal;
				$s['Width'] = $handler->width;
				$s['Height'] = $handler->height;
				Msd_Dao::table('attachment')->insert($s);
				
				$SavePath = $cConfig->save_path->avatar;
				$SavePath .= substr($fid, 0, 1).DIRECTORY_SEPARATOR.substr($fid, 1, 1).DIRECTORY_SEPARATOR;
				
				if (!is_dir($SavePath)) {
					mkdir($SavePath, 0777, true);
				}
				
				$SaveFile = $SavePath.$fid.'.'.$s['Ext'];
				copy($handler->new_file, $SaveFile);
	
				//	small
				$mw = $config->avatar->small->width;
				$mh = $config->avatar->small->height;
				$normal = $handler->scale($mw, $mh);
					
				$fid = sha1(uniqid(mt_rand()));
				$s = array();
				$s['FileId'] = $fid;
				$s['Name'] = $meta['meta']['Name'];
				$s['MimeType'] = $meta['meta']['MimeType'];
				$s['Size'] = $handler->size;
				$s['Uid'] = $uid;
				$s['Hash'] = $params['params']['Avatar'];
				$s['Description'] = '';
				$s['Ext'] = $meta['meta']['Ext'];
				$s['Usage'] = $config->usage->avatar_small;
				$s['Width'] = $handler->width;
				$s['Height'] = $handler->height;
				Msd_Dao::table('attachment')->insert($s);
				
				$SavePath = $cConfig->save_path->avatar;
				$SavePath .= substr($fid, 0, 1).DIRECTORY_SEPARATOR.substr($fid, 1, 1).DIRECTORY_SEPARATOR;
				
				if (!is_dir($SavePath)) {
					mkdir($SavePath, 0777, true);
				}
				
				$SaveFile = $SavePath.$fid.'.'.$s['Ext'];
				copy($handler->new_file, $SaveFile);
				
				Msd_Member_Avatar::getInstance($uid)->delete($params['params']['Avatar']);
			}	
		} else if (isset($params['params']['DeleteAvatar']) && $params['params']['DeleteAvatar']) {
			Msd_Member_Avatar::getInstance($uid)->delete();
		}
	}	
}