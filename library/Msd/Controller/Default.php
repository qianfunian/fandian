<?php

/**
 * 网站控制器基类
 * 
 * @author pang
 *
 */

class Msd_Controller_Default extends Msd_Controller
{
	protected $sess = null;
	protected $member = null;
	protected $hash = '';
	protected $orderHelper = null;
	protected $order_data = array();
	protected $express_force = array(
			'Force' => 0,
			'AddTime' => ''
			);
	protected $metaKey = 'index';
	
	public function init()
	{
		parent::init();
		
		$cName = strtolower(str_replace('Controller', '', get_class($this)));
		$this->sess = &Msd_Session::getInstance();
		$this->member = &Msd_Member::getInstance($this->sess->get('uid'));
		
		$this->view->cName = $cName;
		$this->view->scriptUrl = $this->scriptUrl = 'http://'.$_SERVER['SERVER_NAME'].$this->baseUrl;
		$this->view->uid = $this->member->uid();

		
		$this->view->CityConfig = Msd_Config::cityConfig();
		
		$hash = trim(urldecode($this->sess->get('order_hash')));
		$hash && $this->hash = $hash;
		
		$CoordGuid = $Address = '';
		
		$memberView = array();
		if ($this->member->uid()) {
			$memberView = array(
					'UserName' => $this->member->Username
			);
			$extend = &$this->member->extend();
			
			$_COOKIE['contactor'] || Msd_Cookie::set('contactor', $extend['RealName']);
			$_COOKIE['phone'] || Msd_Cookie::set('phone', $extend['Cell']);
			
			if (!$_COOKIE['address']) {
				if ($extend['AddressBook']['Address']) {
					$Address = $extend['AddressBook']['Address'];
					$CoordGuid = $extend['AddressBook']['CoordGuid'];
				} else if ($extend['Address']) {
					$Address = $extend['Address'];
					if ($extend['Coord']['CoordGuid']) {
						$CoordGuid = $extend['Coord']['CoordGuid'];
					}
				} else {
					$Address = $this->member->Address;
				}
				Msd_Cookie::set('address', $Address);
			}
		}
		
		$this->view->member = $memberView;
		
		$CoordGuid || $CoordGuid = $_COOKIE['coord_guid'];
		$Address || $Address = $_COOKIE['coord_name'];

		if ($CoordGuid) {
			$CoordGuid!=$_COOKIE['coord_guid'] && Msd_Cookie::set('coord_guid', $CoordGuid);
			
			if (!$Address) {
				$row = &Msd_Dao::table('coordinate')->get($CoordGuid);
				if ($row) {
					Msd_Cookie::set('coord_name', $row['CoordName']);
					Msd_Cookie::set('latitude', $row['Latitude']);
					Msd_Cookie::set('longitude', $row['Longitude']);
				}
			}
		}

		$this->express_force = $this->view->express_force = Msd_Cache_Loader::ExpressForce();
		
		$_vars = &Msd_Cache_Loader::Systemvars();
		$ca = unserialize($_vars['close_anounce']);
		$this->view->close_announce = $ca;
		
		$this->view->top_banner = array(
			'url' => '',
			'link' => ''	
			);
		if ($_vars['top_banner']) {
			try {
				$__t = unserialize($_vars['top_banner']);
				
				$this->view->top_banner['url'] = $__t['url'];
				$this->view->top_banner['link'] = $__t['link'];
			} catch (Exception $e) {}
		}

		if (!$this->isAjax) {
			//	parse meta title/keywords/description
			$params = $this->getRequest()->getParams();
			$C = $params['controller'];
			$A = $params['action'];
			$config = &Msd_Config::cityConfig()->toArray();
			
			if ($C=='vendor' && !preg_match('/^([a-z].+)$/i', $A)) {
				$A = 'show';
			}
			
			if (isset($config['meta'][$C.'_'.$A])) {
				$M = &$config['meta'][$C.'_'.$A];
			} else if (isset($config['meta'][$C])) {
				$M = &$config['meta'][$C];
			} else {
				$M = &$config['meta']['index'];
			}

			$this->view->MetaPara = $M;	
		}
		
		$this->sess->set('last_domain', $_SERVER['SERVER_NAME']);
		
		$this->fiestParams();
	}

	/**
	 * @desc 获取用户最近5个订餐地址
	 * @return multitype:unknown
	 */
    protected function previousAddresses() {
		$parsed = $rows = array ();
		$uid = &$this->member->uid ();
		if ($uid) {
			$tmp = &Msd_Member_Address::getInstance ( $uid )->last5Address ();
			foreach ( $tmp as $row ) {
				if (! in_array ( $row ['CustAddress'], $parsed )) {
					$rows [] = $row;
					$parsed [] = $row ['CustAddress'];
				}
			}
		}
		return $rows;
	}
    
    protected function cacheOrderParams(array $params)
    {
    	$data = $this->sess->get('order_params');
    	$data || $data = array();
    	
    	foreach ($params as $key=>$val) {
    		$data[$key] = $val;
    	}
    	
    	$this->sess->set('order_params', $data);
    }
}