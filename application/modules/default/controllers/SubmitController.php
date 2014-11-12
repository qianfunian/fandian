<?php

class SubmitController extends Msd_Controller_Default
{
    protected static $status = array();

    /**
     * 获得配置中的订单状态
     *
     */
    public static function &getStatus()
    {
        if (count(self::$status) == 0) {
            $config = & Msd_Config::appConfig()->order->status->toArray();
            foreach ($config as $key => $val) {
                self::$status[$key] = $val;
            }
        }

        return self::$status;
    }

    /**
     * 某个用户是否曾经下单过
     *
     * @param string $CustGuid
     */
    public static function CustHasOrder($CustGuid)
    {
        $cacher = & Msd_Cache_Remote::getInstance();
        $key = 'inc_' . $CustGuid;
        $result = (int)$cacher->get($key);

        if (!$result) {
            $tmp = Msd_Dao::table('order')->CustHasOrder($CustGuid);
            if ($tmp['OrderGuid']) {
                $result = 1;
                $cacher->set($key, $result);
            }
        }

        return $result;
    }

    /**
     * 生成新的订单号
     *
     */
    public static function newOrderid($CityId = 'wx')
    {
        $id = Msd_Dao::table('order')->newOrderId($CityId);
        return $id;
    }

    public function  confirmAction()
    {
        // USER CONFIG
        include('./jcart/jcart-config.php');
        // DEFAULT CONFIG VALUES
        include('./jcart/jcart-defaults.php');
        // INITIALIZE JCART AFTER SESSION START
        $cart =& $_SESSION['jcart'];
        if (!is_object($cart)) $cart = new Msd_Jcart();

        $flag = 0;
        $cid = Msd_Config::cityConfig()->city_id;
        foreach ($cart->vendor_total as $row) {
            if ($cid == 'wx' || $cid == 'cz') {
                if ($row >= 48) {
                    $flag = 1;
                    break;
                }
            } else {
                if ($row >= 58) {
                    $flag = 1;
                    break;
                }
            }
        }

        $vendors = $cart->get_vendorsguid();
        if ($_COOKIE['coord_guid'] && $_COOKIE['longitude'] && $_COOKIE['latitude']) {
            foreach ($vendors as $vendorguid) {
                //根据商家经纬度获取距离，然后根据距离获取运费
                $vaTable = Msd_Dao::table('vendor/address');
                $distance = $vaTable->getDistance($vendorguid, $_COOKIE['longitude'], $_COOKIE['latitude']);
                $freight = Msd_Waimaibao_Freight::calculate($distance, $vendorguid);
                $cart->set_freight($vendorguid, $freight);
                $cart->set_distance($vendorguid, $distance);
            }
        } else {
            foreach ($vendors as $vendorguid) {
                $distance = $freight = null;
                $cart->set_freight($vendorguid, $freight);
                $cart->set_distance($vendorguid, $distance);
            }
        }
        $res = '';
        if (((int)$this->_request->getPost('OrderPaymethod') || $cid == 'wx' || $cid == 'cz' || $cid == 'nj') && $flag) {
            $custdis = Msd_Dao::table('CustDiscount');
            $res = $custdis->search(trim($this->_request->getPost('OrderPhone')), $cid);
        }
        $this->view->res = $res;
        $this->view->total = $cart->total;
        $this->view->cart = $cart->display_cart($jcart);
    }

    public function indexAction()
    {
        if (!$this->_request->isPost()) {
            $this->_redirect('');
            exit;
        }

        $PayMethod = (int)$this->_request->getParam('paymethod', 0);
        $Address = trim($this->_request->getPost('OrderAddress'));
        $CoordGuid = $this->_request->getPost('OrderCoordGuid');
        $phone = trim($this->_request->getPost('OrderPhone'));
        $CustName = trim($this->_request->getPost('OrderContactor'));
        $gguid = $this->_request->getPost('giftcard');
        $_this_express_setting = (int)$this->_request->getPost('OrderExpressTime');

        $ccgc = $this->_request->getPost('ccgc');

        $partner_data = array();
        //订单备注
        $RequestRemark = trim($this->_request->getPost('OrderRemark'));
        $RequestRemark = $RequestRemark != '' ? $RequestRemark : NULL;

        if (!Msd_Validator::isCell($phone)) {
            $this->_redirect('submit/confirm?error=1');
        }

        $cart = & $_SESSION['jcart'];
        if (!is_object($cart)) $cart = new Msd_Jcart();
        foreach ($cart->get_contents() as $item) {
            $contents[$item['vendorguid']][] = $item;
        }

        $result = array();
        self::getStatus();

        $cacher = & Msd_Cache_Remote::getInstance();
        $config = & Msd_Config::appConfig();
        $cityConfig = & Msd_Config::cityConfig();

        $sgCache = & Msd_Cache_Loader::ServiceGroup();
        $sCache = & Msd_Cache_Loader::Services();

        //订单来源
        $defaultName = $config->db->status->name->default;
        $user =  & $this->member;
        $hash = sha1(uniqid(mt_rand()));
        $this->sess->set('hash', $hash);

        $SalesSource = $config->order->web_source;
        $SalesAttribute = '';
        $SrvGrpGuid = $cityConfig->db->guids->service_group;

        $ServiceGuid = trim($this->_request->getPost('serviceGuid', $cityConfig->db->guids->service));
        $ServiceName = trim($this->_request->getPost('serviceName', $config->db->n->service_name->normal));
        $FirstItemName = '';

        $CityId = $cityConfig->city_id;

        $CityGuid = $cityConfig->db->guids->city;
        $AreaGuid = array_pop($cityConfig->db->guids->area->toArray());

        $oTable = & Msd_Dao::table('order');
        $oiTable = & Msd_Dao::table('order/item');
        $sTable = & Msd_Dao::table('sales');
        $vTable = & Msd_Dao::table('vendor');
        $vaTable = & Msd_Dao::table('vendor/address');
        $cpTable = & Msd_Dao::table('customer/phone');
        $caTable = & Msd_Dao::table('customer/address');
        $coTable = & Msd_Dao::table('coordinate');
        $cTable = & Msd_Dao::table('customer');
        $hTable = & Msd_Dao::table('order/hash');
        $opTable = & Msd_Dao::table('order/payment');
        $oslTable = & Msd_Dao::table('order/status/log');

        $fp = fopen('/p/www/fandian.com/logs/production/lock.txt', 'a+');

        try {
            $ts = & $oTable->transaction();
            $ts->start();

            $Freight = 0;
            $Distance = 0;

            $Longitude = 0;
            $Latitude = 0;
            $CoordName = '';
            $StatusId = '';
            $PhoneGuid = '';
            $Category = '';

            if (Msd_Validator::isGuid($CoordGuid)) {
                $d = $coTable->cget($CoordGuid);
                if (!empty($d)) {
                    $Longitude = $d['Longitude'];
                    $Latitude = $d['Latitude'];
                    $CoordName = $d['CoordName'];
                }
            }

            $cellInfo = $cpTable->OrderCellCheck($phone);
            $userGuid = Msd_Validator::isGuid(trim($cellInfo['CustGuid'])) ? $cellInfo['CustGuid'] : 0;

            if ($userGuid) {
                $CustGuid = $userGuid;
                $PhoneGuid = $cellInfo['PhoneGuid'];
            } else {
                $CustGuid = $cTable->genGuid();
                $cTable->insert(array(
                    'CustGuid' => $CustGuid,
                    'CustName' => $CustName,
                    'Company' => '',
                    'Mail' => '',
                    'Remark' => '',
                    'AddUser' => $defaultName
                ));
            }

            if (Msd_Validator::isGuid($CustGuid)) {
                $__row = $cTable->get($CustGuid);
                if ($__row['CtgGroupGuid'] == $cityConfig->db->guids->customer->vip) {
                    $Category = '一级';
                }
            }

            $IsNewCust = (int)!(bool)self::CustHasOrder($CustGuid);

            if (!$PhoneGuid) {
                $PhoneGuid = $cpTable->genGuid();
                $cpTable->insertCell(array(
                    'PhoneGuid' => $PhoneGuid,
                    'CustGuid' => $CustGuid,
                    'PhoneNumber' => $phone,
                    'Remark' => '',
                    'AddUser' => $defaultName
                ));
            }

            $AddressGuid = $caTable->addressExists($Address, $CustGuid);
            if (!$AddressGuid) {
                $AddressGuid = $caTable->genGuid();
                $caTable->insert(array(
                    'AddressGuid' => $AddressGuid,
                    'CustGuid' => $CustGuid,
                    'CustAddress' => $Address,
                    'AddUser' => $defaultName,
                    'CoordGuid' => $CoordGuid,
                    'Longitude' => $Longitude,
                    'Latitude' => $Latitude,
                    'CityId' => $CityId,
                    'CityGuid' => $CityGuid
                ));
            }

            $SalesGuid = $sTable->genGuid();
            $SalesInserted = false;
            //Msd_Log::getInstance()->debug(var_export($sCache, true));

            //订单处理
            /*
            foreach ($sCache as $_sguid=>$_sg) {
                if ($_sg['SrvGrpGuid']==$SrvGrpGuid) {
                    $ServiceGuid = $_sg['SrvGuid'];
                    break;
                }
            }
            */
            foreach ($contents as $VendorGuid => $items) {

                $Freight = $cart->freight[$VendorGuid] ? $cart->freight[$VendorGuid] : 0;
                $Distance = $cart->distance[$VendorGuid] ? $cart->distance[$VendorGuid] : 0;

                $total = $boxes = $box = 0;

                $OrderGuid = $oTable->genGuid();

                $Vendor = $vTable->get($VendorGuid);
                $VendorAddress = $vaTable->get($VendorGuid);
                $idx = 0;

                foreach ($items as $item) {

                    $OrdItemGuid = $oiTable->genGuid();
                    $ItemGuid = $item['id'];
                    $count = (int)$item['qty'];
                    $UnitPrice = $item['price'];
                    $BoxQty = $item['boxqtys'];
                    $BoxAmount = $count * $BoxQty;
                    $ItemAmount = $item['subtotal'];
                    $ItemUnit = $item['unitname'] != NULL ? $item['unitname'] : '';
                    $boxes += $BoxAmount;
                    $box += $BoxAmount * $item['boxunitprices'];
                    $total += $ItemAmount;

                    if ($FirstItemName == '' && $item['name'] != '米饭') {
                        $FirstItemName = $item['name'];
                    }

                    $params = array(
                        'OrdItemGuid' => $OrdItemGuid,
                        'OrderGuid' => $OrderGuid,
                        'LineIndex' => $idx + 1,
                        'ItemGuid' => $ItemGuid,
                        'ItemId' => '',
                        'ItemName' => $item['name'],
                        'SetMealType' => 0,
                        'ItemPrice' => $item['price'],
                        'ItemQty' => $count,
                        'MinOrderQty' => '',
                        'ItemUnit' => $ItemUnit,
                        'ItemAmount' => $ItemAmount,
                        'BoxQty' => $BoxQty * $count,
                        'BoxRatioQty' => $BoxQty,
                        'ItemRatioQty' => $count,
                        'BoxPrice' => $item['boxunitprices'],
                        'BoxAmount' => $BoxAmount * $item['boxunitprices'],
                        'TotalAmount' => $BoxAmount * $item['boxunitprices'] + $ItemAmount,
                        'ItemReq' => '',
                        'Remark' => '',
                        'ItemPriceOrigin' => $item['price'],
                        'ItemPriceLastModified' => $item['price'],
                        'ItemQtyLastModified' => $count,
                        'AddUser' => $defaultName,
                        'CityId' => $CityId
                    );
                    $oiTable->insert($params);
                    $idx++;
                }

                //	Sales
                if (!$SalesInserted) {

                    if ($_this_express_setting) {
                        //	预订
                        $ReqDate = date("Y-m-d", strtotime("+" . $this->_request->getPost('day') . " day"));
                        $ReqTimeStart = $this->_request->getPost('hour') . ':' . $this->_request->getPost('minutes') . ':00.000';
                        $TimeDirection = 3;
                    } else {
                        //	尽快
                        $ReqDate = date('Y-m-d');
                        $ReqTimeStart = '';
                        $TimeDirection = 0;
                    }


                    $sd = array(
                        'SalesGuid' => $SalesGuid,
                        'SalesSource' => $SalesSource,
                        'VersionId' => 0,
                        'IsNewCust' => $IsNewCust,
                        'CustGuid' => $CustGuid,
                        'PhoneGuid' => $PhoneGuid,
                        'CallPhone' => $phone,
                        'RequestRemark' => $RequestRemark,
                        'ReqDate' => $ReqDate,
                        'CustName' => $CustName,
                        'AddressGuid' => $AddressGuid,
                        'CustAddress' => $Address,
                        'CoordGuid' => $CoordGuid,
                        'CoordName' => $CoordName,
                        'Longitude' => $Longitude,
                        'Latitude' => $Latitude,
                        'SrvGrpGuid' => $SrvGrpGuid,
                        'ServiceGuid' => $ServiceGuid,
                        'SrvGrpName' => $sgCache[$SrvGrpGuid]['SrvGrpName'],
                        'ServiceName' => $ServiceName,
                        'CityId' => $CityId,
                        'CityGuid' => $CityGuid,
                        'AreaGuid' => $AreaGuid,
                        'Category' => $Category,
                        'Paid' => 0,
                        'Invoice' => 0,
                        'AddUser' => $defaultName
                    );

                    if (Msd_Validator::isGuid($SalesAttribute)) {
                        $attr = Msd_Dao::table('sales/attribute')->cget($SalesAttribute);
                        $sd['SalesAttribute'] = $attr['AttributeName'];
                    }

                    $sTable->insert($sd);
                    $SalesInserted = true;
                }

                if (flock($fp, LOCK_EX)) {
                    $OrderId = self::newOrderid($CityId);
                    fwrite($fp, date('Y-m-d H:i:s') . '-' . microtime(true) . '-' . $OrderId . PHP_EOL);
                    flock($fp, LOCK_UN);
                }

                $od = array(
                    'OrderGuid' => $OrderGuid,
                    'OrderId' => $OrderId,
                    'SalesGuid' => $SalesGuid,
                    'VersionId' => 0,
                    'StatusId' => self::$status['posted'],
                    'TotalAmount' => $total + $box + $Freight,
                    'PaymentMethod' => $PayMethod,
                    'TransportMethod' => 0,
                    'Distance' => $Distance,
                    'FreightOrigin' => $Freight,
                    'Freight' => $Freight,
                    'ReqTimeStart' => $ReqTimeStart,
                    'TimeDirection' => $TimeDirection,
                    'CityId' => $CityId,
                    'VendorGuid' => $VendorGuid,
                    'VendorName' => $Vendor['VendorName'],
                    'VendorId' => $Vendor['VendorId'],
                    'VendorLongitude' => $VendorAddress['Longitude'],
                    'VendorLatitude' => $VendorAddress['Latitude'],
                    'ItemCount' => count($items),
                    'ItemAmount' => $total,
                    'BoxQty' => $boxes,
                    'BoxAmount' => $box,
                    'SumAmount' => $total + $box,
                    'Remark' => $RequestRemark,
                    'AddUser' => $defaultName
                );

                $oTable->insert($od);

                //	OrderPayment
                $PaymentGuid = $opTable->genGuid();
                $opTable->insert(array(
                    'PaymentGuid' => $PaymentGuid,
                    'OrderGuid' => $OrderGuid,
                    'Hash' => $hash,
                    'BankApi' => '',
                    'BankId' => '',
                    'PaidMoney' => 0,
                    'CallbackSign' => ''
                ));

                //	OrderStatusLog
                $StatusLogGuid = $oslTable->genGuid();
                $oslTable->insert(array(
                    'StatusLogGuid' => $StatusLogGuid,
                    'OrderGuid' => $OrderGuid,
                    'StatusId' => self::$status['posted'],
                    'CityId' => $CityId
                ));

                $result['OrderGuid'][] = $OrderGuid;

                $ckey = 'OHash_' . md5($OrderGuid);
                $cacher->set($ckey, $hash);

                $hTable->insert(array(
                    'Hash' => $hash,
                    'OrderGuid' => $OrderGuid,
                    'PayMethod' => $PayMethod,
                    'Payed' => 0,
                    'BankApi' => '',
                    'BankId' => '',
                    'CityId' => $CityId
                ));

                $HookParams = array(
                    'OrderGuid' => $OrderGuid,
                    'OrderId' => $OrderId,
                    'CoordGuid' => $CoordGuid,
                    'CoordName' => $CoordName,
                    'CustAddress' => $Address,
                    'CustName' => $CustName,
                    'VendorName' => $Vendor['VendorName'],
                    'VendorGuid' => $Vendor['VendorGuid'],
                    'FirstItemName' => $FirstItemName,
                    'Hash' => $hash,
                    'SalesAttribute' => $SalesAttribute,
                    'PartnerData' => $partner_data,
                    'SalesGuid' => $SalesGuid,
                    'CityId' => $CityId
                );
                Msd_Hook::run('NewOrderCreated', $HookParams);
            }

            $ts->commit();
        } catch (Exception $e) {
            $ts->rollback();
            Msd_Log::getInstance()->debug(var_export($od, true));
            Msd_Log::getInstance()->order($e->getMessage() . "\n" . $e->getTraceAsString());

            Msd_Hook::run('OrderBug', $e->getMessage());
        }

        fclose($fp);

        if ($result) {
            if (Msd_Validator::isGuid($ccgc)) {
                $oTable->ccgc($ccgc);
            }
            try {

                if (!empty($gguid)) {
                    $gtTable = Msd_Dao::table('giftticket');
                    foreach ($gguid as $row) {
                        $code = md5(strtoupper(trim($row)));
                        $data = $gtTable->verify($code);
                        if ($data) {
                            $codes[] = $code;
                        } else {
                            $codes[] = '';
                            Msd_Log::getInstance()->gift('调用失败-礼品好不存在:' . $row);
                        }
                    }
                    if (!empty($codes)) {
                        $oTable->giftccgc($phone, $codes);
                    }
                }
            } catch (Exception $e) {
                Msd_Log::getInstance()->debug(var_export($e, true));
            }

            $cart->empty_cart();
            $sc = $this->sess->get('order_params');
            $sc || $sc = array();
            $sc['UsedAddress'] || $sc['UsedAddress'] = array();

            $sc['Contactor'] = trim($_POST['OrderContactor']);
            $sc['Address'] = trim($_POST['OrderAddress']);
            $sc['Phone'] = trim($_POST['OrderPhone']);
            $sc['UsedAddress'][] = array();

            $this->sess->set('order_params', $sc);

            $order_ids = explode(',', $this->sess->get('oids'));
            $order_ids || $order_ids = array();
            foreach ($result['OrderGuid'] as $OrderGuid) {
                $order_ids[] = $OrderGuid;
            }

            $this->sess->set('oids', implode(',', $order_ids));

            header("Location:./order/hash?hash=" . $hash);
        } else {
            throw new Msd_Exception('下单失败');
        }
        exit;
    }

}
