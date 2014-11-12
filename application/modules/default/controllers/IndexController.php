<?php

/**
 * 网站首页
 *
 * @author frankqian
 *
 */
class IndexController extends Msd_Controller_Default
{
    public function indexAction()
    {
        $city_config = Msd_Config::cityConfig();
        if (( int )$city_config->navi->idx_bootstrap > 0) {
            echo $this->view->render('index/' . $city_config->city_id . 'bootstrap.phtml');
        } else {
            $this->homeAction();
        }
        exit (0);
    }

    public function homeAction()
    {
        $city_config = Msd_Config::cityConfig();
        $this->view->city = $city_config->city_id;
        $this->view->previousAddresses = $this->previousAddresses();
        $this->view->data = Msd_Cache_Loader::siteIndex();
        $this->view->need_scroll = (self::$browser->isIOS() || (preg_match('/' . $_SERVER ['SERVER_NAME'] . '/i', $_SERVER ['HTTP_REFERER']) && $_SERVER ['HTTP_REFERER'] != 'http://' . $_SERVER ['SERVER_NAME'] && $_SERVER ['HTTP_REFERER'] != 'http://' . $_SERVER ['SERVER_NAME'] . '/')) ? false : true;

        $this->view->order_announce = Msd_Cache_Loader::orderAnnounce();
        echo $this->view->render('index/index.phtml');
        exit (0);
    }

    public function coordAction()
    {
        $param = $this->_request->getParam('q');
        $cacher = & Msd_Cache_Remote::getInstance();
        $cacheKey = 'coords';
        $coords = $cacher->get($cacheKey);

        if (!$coords) {
            $city_config = Msd_Config::cityConfig();
            $coordtable = Msd_Dao::table('coordinate');
            $coords = $coordtable->fetchall($city_config->city_id);
            $cacher->set($cacheKey, $coords);
        }

        $coord_val = '';
        $i = 0;
        foreach ($coords as $coord) {
            if (strpos($coord ['CoordName'] . $coord ['InputCode'], $param) !== false) {
                $coord_val .= $coord ['CoordName'] . '@' . $coord ['InputCode'] . '|' . $coord ['CoordGuid'] . '*' . $coord ['Longitude'] . '*' . $coord ['Latitude'] . "\n";
                $i += 1;
            }
        }
        echo $coord_val;
        exit ();
    }

    public function lsaddsAction()
    {
        if ($this->_request->isPost()) {
            echo Msd_Lsadds::getlsadds($this->member->uid(), $this->_request->getPost('phone'));
        } else {
            $this->redirect('index');
        }
        exit ();
    }

    /**
     * ajax验证礼品券号
     */
    public function checkGiftAction()
    {
        //控制请求次数
        if (intval($this->sess->get('flag')) < 50) {
            $giftId = $this->_request->getParam('giftid');
            $giftId = md5(strtoupper($giftId));

            if (preg_match('/^[A-Z\d]{12}$/', $giftId)) {
                $gtTable = Msd_Dao::table('giftticket');
                $row = $gtTable->verify($giftId);

                if ($row) {
                    $msg = $row [0] ['UsedState'] == 'NoUsed' ? '可使用' . $row [0] ['TName'] : '已使用';
                } else {
                    $msg = '礼品券号不存在';
                }

            } else {
                $msg = '验证失败';
            }

            $this->sess->set('flag', intval($this->sess->get('flag')) + 1);
        } else {
            $msg = '错误-请联系饭店网:4001147777';
        }
        echo $msg;
        exit;
    }
}
