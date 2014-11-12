<?php

class Msd_View_Helper_Base
{
	public $view;
	
	public function &__call($method, $params)
	{
		$result = '';

		Msd_Log::getInstance()->view("Method: ".$method."\n".var_export($params, true));

		return $result;
	}
	
	/**
	 * 折扣率
	 * 
	 * @param unknown $origin
	 * @param unknown $now
	 */
	public function Cutoffprice($origin, $now)
	{
		$origin = (float)$origin;
		$now = (float)$now;
		$result = 10;
		
		if ($now>0 && $origin>0) {
			$result = round(($now*10/$origin), 1);
		}
		
		return $result;
	}
	
	public function Cutstr($str, $len=20)
	{
		$result = $str;
		
		if (Msd_Iconv::ustrlen($str)>$len) {
			$result = Msd_Iconv::usubstr($str, 0, $len).'...';
		}
		
		return $result;
	}
	
	public function Fvendorname($VendorName)
	{
		return Msd_Waimaibao_Vendor::FilterVendorName($VendorName);
	}
	
	public function Minutes2hm($minutes)
	{
		$minutes = (int)$minutes;
		if ($minutes>60) {
			$hours = intval($minutes/60);
			$hours<10 && $hours = '0'.$hours;
			
			$minutes = $minutes - $hours*60;
			$minutes<10 && $minutes = '0'.$minutes;
			$str = $hours.':'.$minutes;
		} else {
			$str = '00:'.$minutes;
		}
		
		return $str;
	}
	
	public function Basehref()
	{
		$base_href = $this->view->CityConfig->meta->base_href;
		
		if (getenv('BASE_HREF')) {
			$base_href = getenv('BASE_HREF');	
		}
		
		return strtolower($base_href);
	}

	/**
	 * 显示运费
	 * 
	 * @param unknown_type $CoordGuid
	 * @param unknown_type $VendorGuid
	 */
	public function Freight($CoordGuid, $VendorGuid)
	{
		$freight = 0;
		
		if ($CoordGuid) {
			$freight = Msd_Waimaibao_Freight::calculate($CoordGuid, $VendorGuid);
		}
		
		return $freight ? $freight.'元' : '--';
	}

	/**
	 * 当前运力状态显示
	 * 
	 * @param unknown_type $force
	 */
	public function Expresstip($force) 
	{
		switch (intval($force)) {
			case 1:
				$result = '当前运力紧张';
				break;
			case 2:
				$result = '当前暂停运营';
				break;
			case 0:
			default:
				$result = '当前运力正常';
				break;
		}
		
		return $result;
	}
	
	public function HideIp($ip)
	{
		list($p1, $p2, $p3, $p4) = explode('.', trim($ip));
		
		return $p1.'.'.$p2.'.*.*';
	}
	
	public function Wrapkeyword($string, $keyword='')
	{
		$result = $string;
		
		if (strlen($keyword)) {
			$result = str_replace($keyword, '<span style="color:red;font-weight:bold">'.$keyword.'</span>', $result);
		}
		
		return $result;
	}
	
	/**
	 * 商家服务时间格式化
	 * 
	 * @param unknown_type $VendorGuid
	 */
	public function Vendorservicetime($VendorGuid, $ServiceName='普通')
	{
		$str = '';
		if ($VendorGuid) {
			$serviceTimes = Msd_Waimaibao_Vendor::C_ServiceTime($VendorGuid, $ServiceName);
			foreach ($serviceTimes as $st) {
				$str .= $this->view->Sdt($st['StartTime'], 'hm').'~'.$this->view->Sdt($st['EndTime'], 'hm').' ';
			}
		}
	
		return $str;
	}
	
	public function Hovermenu($module, $controller='', $keyword='')
	{
		$config = &Msd_Config::appConfig();
		$css = '';
		if ($controller!='' && !is_array($controller)) {
			$controller = array($controller);
		}
	
		$cModule = $this->view->controllerParams['module'];
		$cController = $this->view->controllerParams['controller'];
		$cAction = $this->view->controllerParams['action'];
		$cParams = $this->view->controllerParams;
		$cModule || $cModule = 'default';
		$ctg = $cParams['ctg'];
		$cname = $cParams['category'] || ($cParams['service']=='下午茶' || $cParams['service']=='夜宵');

		if (($this->view->is_noon || $cParams['service']=='下午茶') && $keyword) {
			$cKeyword = '下午茶';
		} else if (($this->view->is_night || $cParams['service']=='夜宵') && $keyword) {
			$cKeyword = '夜宵';
		} else {
			if (($cController=='vendor'|| $cController=='step') && $cAction!='search' && $cAction!='category' && $keyword=='_fucking_') {
				$cKeyword = (($cname!='夜宵' && $cname!='下午茶') && (!$ctg || ($ctg && $ctg!=$config->db->night_guid && $ctg!=$config->db->noon_guid))) ? '_fucking_' : '';
			} else if ($ctg==$config->db->night_guid || $cname=='夜宵') {
				$cKeyword = '夜宵';
			} else if ($ctg==$config->db->noon_guid || $cname=='下午茶') {
				$cKeyword = '下午茶';
			} else {
				$cKeyword = trim(urldecode($cParams['keyword']));
			}
		}

		if ($cModule==$module && ($controller=='' || in_array($cController, $controller))) {
			if ($keyword && $cKeyword==$keyword) {
				$css = 'act';
			} else if (!$keyword) {
				$css = 'act';
			}
		}
	
		return $css;
	}
	
	/**
	 * 日期格式化
	 * 
	 * @param unknown_type $string
	 * @param unknown_type $format
	 */
	public function Dt($string, $format='datetime') 
	{
		return Msd_Functions::Dt($string, $format);
	}
	
	/**
	 * 生成一个radiobox类型的表单元素
	 * 
	 * @param array $cfg
	 */
	public function Radiobox(array $cfg)
	{
		$str	=	''	;
	
		$name	=	$cfg['name']	;
		$value	=	$cfg['value']	;
		$user_value = $cfg['user_value']	;
		$label =$cfg['label']	;
		$id = $cfg['id'] ? $cfg['id'] : "radio_".md5(rand())."_".$name	;
		$onclick = isset($cfg['onclick']) ? " onclick=\"".$cfg['onclick']."\"" : '';
	
		$str .= "<input type=\"radio\" name=\"{$name}\" value=\"{$value}\" id=\"{$id}\"".$onclick;
		$str .= $value==$user_value  ? ' checked="checked"' : "";
		$str .= "><label for=\"{$id}\">{$label}</label>";
	
		return $str	;
	}

	/**
	 * 生成一个select类型的表单元素
	 * 
	 * @param unknown_type $select_arr
	 * @param unknown_type $select_name
	 * @param unknown_type $select_value_input
	 * @param unknown_type $js_ext
	 * @param unknown_type $class
	 * @param unknown_type $key_name
	 * @param unknown_type $value_name
	 * @param unknown_type $title_key
	 * @param unknown_type $title_value
	 */
	public function Select($select_arr,$select_name='',$select_value_input='',$js_ext="",$class="class='flat1'"
						,$key_name="",$value_name="",$title_key="",$title_value="") 
	{
		
		$classname = '';
		
		if ($title_key!="")  {
			$select_value=$title_key;
		} else {
			$select_value=@key($select_arr);
		}
		
		if ($select_value_input!="")  {
			for ($i=1;$i<=count($select_arr);$i++)   {
				if (strval($select_value_input)==strval(@key($select_arr)))  {
					$select_value=key($select_arr);
					break;
				}
				@next($select_arr);
			}
		}
		
		@reset($select_arr);
		$temp ="<select id='{$select_name}' ".$class." name=\"".$select_name."\" ".$js_ext." >\n";
		if (($title_key!="")and($title_value!="")) {
			$temp.="  <option ".$classname;
			$temp.="   value='".$title_key."'>".$title_value ;
			$temp.="</option>	\n";
		}
		
		for($i=1;$i<=count($select_arr);$i++) {
			if ($key_name=="") {
				$key=@key($select_arr);
			} else {
				$key=$select_arr[@key($select_arr)][$key_name];
			}
		
			if ($value_name=="") {
				$value=$select_arr[@key($select_arr)];
			} else {
				$value=$select_arr[@key($select_arr)][$value_name];
			}
		
			$temp.="  <option ".$classname;
			$temp.="   value='".$key."'>".$value ;
			$temp.="  </option>	\n";
				
			@next($select_arr);
		}
		$temp.="</select>";
		
		$tmp_before="value='".$select_value."'";
		$tmp_after ="value='".$select_value."' selected ";
		$select_item_str=str_replace($tmp_before,$tmp_after , $temp);
		
		return $select_item_str;
	}
	
	/**
	 * 时间格式化
	 * 
	 * @param unknown_type $string
	 * @param unknown_type $format
	 */
	public function Sdt($string, $format='datetime')
	{
		try {
			$dt = new DateTime($string);
			$string = date('Y-m-d H:i:s', $dt->getTimestamp());
		} catch (Exception $e) {}
		
		switch ($format) {
			case 'date':
				$result = substr($string, 0, 10);
				break;
			case 'time':
				$result = substr($string, 11, 10);
				break;
			case 'hm':
				$result = substr($string, 11, 5);
				break;
			case 'full':
				$result = $string;
				break;
			default:
				$result = substr($string, 0, -3);
				break;
		}
	
		return $result;
	}	
	
	/**
	 * 附件图片解析
	 * 
	 * @param unknown_type $fid
	 * @param unknown_type $usage
	 */
	public function Attachurl($fid, $usage='article')
	{
		$config = &Msd_Config::cityConfig();
		$prefix = $this->view->staticUrl;
		
		if (!$config->attachment->web_url->$usage) {
			$usage = 'article';
		}
		
		$meta = &Msd_Files::Meta($fid);
		if ($meta['meta']) {
			$prefix .= $config->attachment->web_url->$usage;
			$prefix .= substr($fid, 0, 1).'/'.substr($fid, 1, 1).'/'.$fid.'.'.$meta['meta']['Ext'];
		} else {
			$prefix = '';
		}
		
		return $prefix;
	}
	
	/**
	 * 用户头像
	 * 
	 * @param unknown_type $avatar
	 */
	public function Avatar($avatar, $prefix='')
	{
		$url = $this->Attachurl($avatar, 'avatar');
		if (!$url) {
			$url = ($prefix ? $prefix : $this->staticUrl).'images/noavatar_origin.jpg';
		}
		
		return $url;
	}
	
	/**
	 * 菜品图片地址解析
	 * 
	 * @param unknown_type $params
	 * @param unknown_type $default
	 */
	public function Itemurl($params, $default='') 
	{
		return Msd_Waimaibao_Item::imageUrl($params, $default);
	}
	
	/**
	 * 菜品大图片地址解析
	 *
	 * @param unknown_type $params
	 * @param unknown_type $default
	 */
	public function Itembigurl($params, $default='')
	{
		return Msd_Waimaibao_Item::imageBigUrl($params, $default);
	}	
	
	/**
	 * 菜品团膳地址解析
	 * 
	 * @param unknown_type $params
	 * @param unknown_type $default
	 */
	public function Itemtuanurl($params, $default='') 
	{
		return Msd_Waimaibao_Item::imageTuanUrl($params, $default);
	}	
	
	/**
	 * 特价套餐菜品图片地址解析
	 * 
	 * @param unknown_type $params
	 * @param unknown_type $default
	 */
	public function Itemspecialurl($params, $default='') 
	{
		return Msd_Waimaibao_Item::imageSpecialUrl($params, $default);
	}

	/**
	 * 生成一个checkbox类型的表单元素
	 * 
	 * @param array $config
	 */
	public function Checkbox(array $config) 
	{
		$label		=	 $config['label'];
		$id_name	=	$config['name']	;
		$name	=	($config['type']=='array') ? $config['name'].'[]' : $config['name'];
		$value	=	$config['value'];
		$user_value	=	$config['user_value'];
		$color	=	$config['color'] ? $config['color'] : '#000000';
		$id			=	$id_name . '_'.md5(microtime());
		$checked	=	($value==$user_value) ? (" checked='checked'") : ("");

		$html="
				<input type='checkbox' name=\"{$name}\" id=\"{$id}\" value=\"{$value}\" {$checked} />&nbsp;
				<label for=\"{$id}\"><font color=\"{$color}\">{$label}</font></label>
		";		

		return $html;
	}
	
	/**
	 * <br />转换为\n
	 * @param unknown_type $t
	 */
	public function Br2nl($t)
	{
		$t = preg_replace( "#(?:\n|\r)?<br />(?:\n|\r)?#", "\n", $t );
		$t = preg_replace( "#(?:\n|\r)?<br>(?:\n|\r)?#"  , "\n", $t );
	
		return $t;
	}
	
	/**
	 * 订单状态名称转换
	 * 
	 * @param unknown_type $StatusId
	 */
	public function OSName($StatusId)
	{
		return Msd_Waimaibao_Order_Status::publicStatusName($StatusId);
	}
	
	/**
	 * 一个文章列表
	 * 
	 * @param unknown_type $prefix
	 * @param unknown_type $category
	 * @param unknown_type $limit
	 */
	public function Articlelist($prefix, $category, $limit=5)
	{
		$html = '';
		$cityConfig = &Msd_Config::cityConfig();
		$cacher = &Msd_Cache_Remote::getInstance();
		$key = 'articlelist_'.$category;
		$html = $cacher->get($key);

		if (!$html) {
			$pager = array(
				'page' => 1,
				'limit' => $limit,
				'offset' => 0,	
				);
			$params = array(
				'CategoryId' => array(
					$category
					),
				'PubFlag' => 1,
				'passby_pager' => true,
				'Regions' => $cityConfig->db->guids->area->toArray()
				);
			$sort = array(
				'OrderNo' => 'ASC'	
				);

			$rows = &Msd_Dao::table('article')->search($pager, $params, $sort);
            $this->Basehref();
			foreach ($rows as $row) {
				$html .= "
				<li><span class='footer-arrow'><a href='".$this->Basehref()."article/".$prefix."/".$row['Title']."'>".$row['Title']."</a></span></li>
				";
			}
			
			$html = trim($html, '0');
			$cacher->set($key, $html);
		}
		
		$html = trim($html, '0');
		
		return $html;
	}
	
	public function setView(Zend_View_Interface $view)
	{
		$this->view = $view;
	}
}
