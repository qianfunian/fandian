<?php

class Msd_View_Helper_Fadmin extends Msd_View_Helper_Base
{
	/**
	 * 系统权限
	 * 
	 * @param unknown_type $Sysrights
	 */
	public function Sysrights($Sysrights)
	{
		$str = '';
		
		$rights = explode('|', $Sysrights);
		$config = &Msd_Config::cityConfig();
		$rs = $config->acl->fadmin->toArray();
		$RS = array();
		foreach ($rs as $key=>$row) {
			if (in_array($key, $rights)) {
				$RS[] = $row['name'];
			}
		}
		
		$str = implode(',', $RS);
		
		return $str;
	}
	
	/**
	 * 生成后台树形菜单
	 * 
	 * @param unknown_type $rows
	 * @param unknown_type $suffix
	 */
	public function Organizationtree($rows, $suffix='') 
	{
		if (is_array($rows) && count($rows)>0) {
			$html = '<ul '.$suffix.'>'."\n";
			
			foreach ($rows as $row) {
				if (is_array($row['sub_orgs']) && count($row['sub_orgs'])>0) {
					$class = 'folder';
				} else {
					$class = 'file';
				}
				
				$html .= '<li><span class="'.$class.'" oid="'.$row['_id'].'">'.$row['name'].'</span>'."\n";
				$html .= $this->Organizationtree($row['sub_orgs']);
				$html .= '</li>'."\n";
			}
			
			$html .= '</ul>'."\n";
		}
		
		return $html;
	}

	/**
	 * 积分分类名称
	 * 
	 * @param unknown_type $type
	 */
	public function Credittypename($type) 
	{
		$name = '未知';
		$categories = &Msd_Waimaibao_Credit::Categories();
		isset($categories[$type]) && $name = $categories[$type];
		
		return $name;
	}

	/**
	 * 附件用途解析
	 * 
	 * @param unknown_type $usage
	 */
	public function Attachmentusage($usage) 
	{
		switch ($usage) {
			case '1':
				$name = '用户头像(origin)';
				break;
			case '2':
				$name = '用户头像(normal)';
				break;
			case '3':
				$name = '用户头像(small)';
				break;
			default:
				$name = '文章';
				break;
		}
		
		return $name;
	}
	
	/**
	 * 附件类型图标解析
	 * 
	 * @param array $row
	 */
	public function Attachicon(array $row)
	{
		$icon = '';
	
		if ($row['MimeType']) {
			$icon = Msd_Files::parseMimeIcon($row['MimeType']);
			$icon = "<img src='".$this->baseUrl."/images/mime_types/".$icon.".gif' alt='".$row['FileName']."' />";
		}
	
		return $icon;
	}
	
	/**
	 * 权限名称
	 * 
	 * @param unknown_type $acl
	 */
	public function Acl($acl)
	{
		return Msd_Acl::aclName($acl);
	}

	/**
	 * 操作类型名称解析
	 * 
	 * @param unknown_type $type
	 */
	public function Actionname($type) 
	{
		switch ($type) {
			case 'browse':
				$name = '浏览';
				break;
			case 'insert':
				$name = '新增';
				break;
			case 'update':
				$name = '修改';
				break;
			case 'delete':
				$name = '删除';
				break;
			case 'login':
				$name = '登录';
				break;
			case 'logout':
				$name = '注销';
				break;
			default: 
				$name = $type;
				break;
		}
		
		return $name;
	}
}