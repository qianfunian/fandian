<?php

class Msd_Dao_Table_Server_Order extends Msd_Dao_Table_Server
{
    protected static $instance = null;

    public function __construct()
    {
        parent::__construct();

        $this->_name = $this->prefix . 'Order';
        $this->_primary = 'OrderGuid';
        $this->_realPrimary = 'OrderGuid';
        $this->_orderKey = 'AddTime';
    }

    public static function &getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     *
     * @param array $CustGuids
     */
    public function &orderedVendorsByCustGuid($CustGuid)
    {
        $rows = array();

        $sTable = & $this->t('sales');
        $select = & $this->s();
        $select->from($this->sn('o'), array(
            'o.VendorGuid', 'o.VendorName', $this->expr('COUNT(o.OrderGuid) AS total')
        ));
        $select->join($sTable->sn('s'), 's.SalesGuid=o.SalesGuid', array());
        $select->group(array(
            'o.VendorGuid',
            'o.VendorName'
        ));
        $select->order('total DESC');
        $select->where('s.CustGuid=?', $CustGuid);

        $rows = $this->all($select);

        return $rows;
    }

    /**
     * 根据订单号查找商家
     *
     * @param array $OrderGuids
     */
    public function &orderedVendorsByGuids(array $OrderGuids)
    {
        $rows = array();

        $select = & $this->s();
        $select->from($this->sn('o'), array(
            'o.VendorGuid',
            'o.VendorName',
            $this->expr('COUNT(o.OrderGuid) AS total')
        ));
        $select->group(array(
            'o.VendorGuid',
            'o.VendorName'
        ));
        $select->order('total DESC');
        $select->where('o.OrderGuid IN (?)', $OrderGuids);

        $rows = $this->all($select);

        return $rows;
    }

    /**
     * 从某个指定时间段开始的、已完结的订单
     *
     * @param unknown_type $from
     */
    public function &lastCompleted($from)
    {
        $rows = array();
        $config = & Msd_Config::appConfig();
        $oaTable = & $this->t('order/analysis');

        $select = & $this->s();
        $select->from($this->sn('o'), array(
            'o.OrderGuid',
            'o.CityId',
            'o.VersionId',
            'o.OrderId',
            'o.StatusId',
            'o.IsClosed',
            'o.IsCanceled',
            'o.SalesGuid',
            'o.VendorGuid',
            'o.VendorId',
            'o.VendorName',
            'o.VendorCoord',
            'o.Variable',
            'o.ItemCount',
            'o.ItemAmount',
            'o.BoxQty',
            'o.BoxAmount',
            'o.SumAmount',
            'o.Distance',
            'o.Freight',
            'o.FreightOrigin',
            'o.TotalAmount',
            'o.PaymentMethod',
            'o.TransportMethod',
            'o.ReqTimeStart',
            'o.TimeDirection',
            'o.ReqTimeEnd',
            'o.CustChanged',
            'o.InfoChanged',
            'o.ItemChanged',
            'o.InitTime',
            'o.CreatedTime',
            $this->expr('CONVERT(NVARCHAR, o.AddTime, 120) AS AddTime')
        ));
        $select->joinleft($oaTable->sn('oa'), 'oa.OrderGuid=o.OrderGuid', array(
            $this->expr('oa.OrderGuid AS oaOrderGuid')
        ));
        $select->where('o.StatusId=?', $config->order->status->delivered);
        $select->where('oa.OrderGuid IS NULL OR oa.RecalFlag=1 ' . ($add ? 'OR' : ''));
        $select->where('o.VendorName NOT LIKE ?', '%' . $config->db->n->vendor_name->mini_market . '%');

        $rows = $this->all($select);

        return $rows;
    }

    public function &search(&$pager, array $where = array(), array $order = array())
    {
        $rows = array();
        $count = $pager['limit'] ? (int)$pager['limit'] : 20;
        $page = $pager['page'] ? (int)$pager['page'] : 1;
        $offset = $pager['offset'] ? (int)$pager['offset'] : 0;

        $sTable = & $this->t('sales');
        $osTable = & $this->t('order/status');
        $cTable = & $this->t('customer');
        $vTable = & $this->t('vendor');
        $vaTable = & $this->t('vendor/address');
        $odmTable = & $this->t('order/deliveryman');
        $odmvTable = & $this->t('order/deliveryman/version');
        $dmTable = & $this->t('deliveryman');
        $hTable = & $this->t('order/hash');

        $select = $this->s();
        $cSelect = $this->s();

        $select->from($this->sn('o'));
        $cSelect->from($this->sn('o'), 'COUNT(*) AS total');

        $select->join($sTable->sn('s'), 's.SalesGuid=o.SalesGuid', array(
            'CustAddress', 'CustName', 'CoordName'
        ));
        $cSelect->join($sTable->sn('s'), 's.SalesGuid=o.SalesGuid', '');

        $select->join($osTable->sn('os'), 'os.StatusId=o.StatusId', array(
            'os.StatusName'
        ));
        $cSelect->join($osTable->sn('os'), 'os.StatusId=o.StatusId', '');

        $select->join($cTable->sn('c'), 'c.CustGuid=s.CustGuid', '');
        $cSelect->join($cTable->sn('c'), 'c.CustGuid=s.CustGuid', '');

        $select->join($vaTable->sn('va'), 'o.VendorGuid=va.VendorGuid', array(
            $this->expr('va.Address AS vaAddress'), $this->expr('va.Longitude AS vaLongitude'), $this->expr('va.Latitude AS vaLatitude')
        ));
        $cSelect->join($vaTable->sn('va'), 'o.VendorGuid=va.VendorGuid', '');

        $select->joinleft($hTable->sn('h'), 'h.OrderGuid=o.OrderGuid', array(
            'h.Hash'
        ));
        $cSelect->joinleft($hTable->sn('h'), 'h.OrderGuid=o.OrderGuid', '');

        if ($where['CustGuid']) {
            $select->where('c.CustGuid=?', $where['CustGuid']);
            $cSelect->where('c.CustGuid=?', $where['CustGuid']);
        }

        if (isset($where['PaymentMethod'])) {
            $select->where('o.PaymentMethod=?', (int)$where['PaymentMethod']);
            $cSelect->where('o.PaymentMethod=?', (int)$where['PaymentMethod']);

            $select->where('o.OrderId LIKE ?', 'W%');
            $cSelect->where('o.OrderId LIKE ?', 'W%');
        }

        if (isset($where['Payed'])) {
            $select->where('h.Payed=?', (int)$where['Payed']);
            $cSelect->where('h.Payed=?', (int)$where['Payed']);
        }

        if (isset($where['StatusId'])) {
            $select->where('o.StatusId=?', $where['StatusId']);
            $cSelect->where('o.StatusId=?', $where['StatusId']);
        }

        if (isset($where['TimeStart'])) {
            $select->where('o.AddTime>?', $where['TimeStart']);
            $cSelect->where('o.AddTime>?', $where['TimeStart']);
        }

        if (is_array($where['OrderGuids'])) {
            $where['OrderGuids'][] = $this->genGuid();
            $select->where('o.OrderGuid IN (?)', $where['OrderGuids']);
            $cSelect->where('o.OrderGuid IN (?)', $where['OrderGuids']);
        }

        if (count($order) > 0) {
            foreach ($order as $key => $val) {
                $select->order($key . ' ' . $val);
            }
        } else {
            $select->order($this->_orderKey . ' DESC');
        }

        $select->limitPage($page, $count);

        Msd_Dao_Debug::StartDebugTimer(__METHOD__);
        $result = $this->all($select);
        Msd_Dao_Debug::EndDebugTimer(__METHOD__);

        $i = 0;
        foreach ($result as $row) {
            $row['_seq'] = $offset + 1 + ($i++);
            $rows[] = $row;
        }

        if (!isset($where['passby_pager'])) {
            $tmp = $this->one($cSelect);
            $pager['total'] = $tmp['total'];
        }

        return $rows;
    }

    public function insert(array $params)
    {
        $params['AddUser'] || $params['AddUser'] = Msd_Config::appConfig()->db->status->name->default;
        $params['AddTime'] = $this->expr('GETDATE()');
        $params['InitTime'] = $this->expr('GETDATE()');
        $params['CreatedTime'] = $this->expr('GETDATE()');
        $params['IsClosed'] = 0;
        $params['IsCanceled'] = 0;
        $params['Variable'] = 0;

        if (!$params['TimeDirection']) {
            unset($params['ReqTimeStart']);
        }

        if (Msd_Validator::isLngLat($params['VendorLongitude'], $params['VendorLatitude'])) {
            $params['VendorCoord'] = $this->wrapPoint($params['VendorLongitude'], $params['VendorLatitude']);
            unset($params['VendorLongitude']);
            unset($params['VendorLatitude']);
        } else if ($params['VendorGuid'] == Msd_Config::cityConfig()->db->guids->mini_market) {
            unset($params['VendorLongitude']);
            unset($params['VendorLatitude']);
        }

        isset($params['InfoChanged']) || $params['InfoChanged'] = 0;
        isset($params['CustChanged']) || $params['CustChanged'] = 0;
        isset($params['ItemChanged']) || $params['ItemChanged'] = 0;
        isset($params['SetMealChanged']) || $params['SetMealChanged'] = 0;

        return parent::insert($params);
    }

    public function newOrderId($CityId = 'wx')
    {
        $prefix = 'W' . date('ymd', time());
        $select = & $this->s();
        $select->from($this->sn('o'), array(
            'OrderId'
        ));
        $select->where('o.OrderId LIKE ?', $prefix . '%');
        $select->where('o.CityId=?', $CityId);
        $select->order('o.OrderId DESC');
        $select->limit(1);

        $row = $this->db->fetchRow($select);

        Msd_Log::getInstance()->debug();
        if ($row) {
            $suffix = intval(substr($row['OrderId'], -4)) + rand(2, 5);
            $suffix = str_repeat('0', 4 - strlen($suffix)) . $suffix;
        } else {
            $suffix = '0001';
        }
        return $prefix . $suffix;
    }

    public function &getByOrderId($OrderId, $CityId = 'wx')
    {
        $select = & $this->s();
        $select->from($this->sn('o'));
        $select->where('OrderId=?', $OrderId);
        $select->where('CityId=?', $CityId);
        $select->order('AddTime ASC');
        $select->limit(1);

        $row = $this->one($select);

        return $row;
    }

    /**
     * 转换招行订单号为系统订单号
     *
     * @param string $BillNo
     */
    public function CmbBid($BillNo)
    {
        $select = & $this->s();
        $select->from($this->sn('o'), array(
            'OrderId'
        ));
        $select->where('OrderId LIKE ?', 'W%' . $BillNo);

        $row = $this->one($select);

        return $row['OrderId'] ? $row['OrderId'] : '';
    }

    /**
     * 网上支付历史
     *
     * @param unknown_type $pager
     * @param unknown_type $where
     * @param unknown_type $order
     */
    public function &payHistory(&$pager, $where = array(), $order = array())
    {
        $rows = array();
        $count = $pager['limit'] ? (int)$pager['limit'] : 20;
        $page = $pager['page'] ? (int)$pager['page'] : 1;
        $offset = $pager['offset'] ? (int)$pager['offset'] : 0;

        $sTable = & $this->t('sales');
        $osTable = & $this->t('order/status');
        $cTable = & $this->t('customer');
        $vaTable = & $this->t('vendor/address');
        $hTable = & $this->t('order/hash');
        $eTable = & $this->t('enum');
        $vTable = & $this->t('vendor');

        $select = $this->s();
        $cSelect = $this->s();

        $select->from($this->sn('o'));
        $cSelect->from($this->sn('o'), 'COUNT(*) AS total');

        $select->join($sTable->sn('s'), 's.SalesGuid=o.SalesGuid', array());
        $cSelect->join($sTable->sn('s'), 's.SalesGuid=o.SalesGuid', '');

        $select->join($osTable->sn('os'), 'os.StatusId=o.StatusId', array(
            'os.StatusId', 'os.StatusName', 'os.PublicName'
        ));
        $cSelect->join($osTable->sn('os'), 'os.StatusId=o.StatusId', '');

        $select->join($cTable->sn('c'), 'c.CustGuid=s.CustGuid', array());
        $cSelect->join($cTable->sn('c'), 'c.CustGuid=s.CustGuid', '');

        $select->join($vTable->sn('v'), 'v.VendorGuid=o.VendorGuid', array());
        $cSelect->join($vTable->sn('v'), 'v.VendorGuid=o.VendorGuid', '');

        $select->join($vaTable->sn('va'), 'va.VendorGuid=o.VendorGuid', array(
            $this->expr('va.Address AS vaAddress'),
            $this->expr('va.Longitude AS vaLongitude'),
            $this->expr('va.Latitude AS vaLatitude')
        ));
        $cSelect->join($vaTable->sn('va'), 'va.VendorGuid=o.VendorGuid', '');

        $select->join($hTable->sn('h'), 'h.OrderGuid=o.OrderGuid', array(
            'h.PayedMoney', 'h.BankApi', $this->expr('h.CreateTime AS PayTime')
        ));
        $cSelect->join($hTable->sn('h'), 'h.OrderGuid=o.OrderGuid', '');

        $select->join($eTable->sn('e'), "e.ElementValue=h.BankApi AND e.EnumName=N'支付银行'", array(
            'e.ElementName'
        ));
        $cSelect->join($eTable->sn('e'), "e.ElementValue=h.BankApi AND e.EnumName=N'支付银行'", '');

        if ($where['s_date']) {
            $select->where('o.AddTime>?', $where['s_date'] . ' 00:00:00');
            $cSelect->where('o.AddTime>?', $where['s_date'] . ' 00:00:00');
        }

        if ($where['e_date']) {
            $select->where('o.AddTime<?', $where['e_date'] . ' 23:59:59');
            $cSelect->where('o.AddTime<?', $where['e_date'] . ' 23:59:59');
        }

        if ($where['AreaGuid']) {
            $select->where('v.AreaGuid IN (?)', (array)$where['AreaGuid']);
            $cSelect->where('v.AreaGuid IN (?)', (array)$where['AreaGuid']);
        }

        if (strlen($where['bank'])) {
            $select->where('h.BankApi=?', (int)$where['bank']);
            $cSelect->where('h.BankApi=?', (int)$where['bank']);
        }

        $select->where('o.OrderId LIKE ?', 'W%');
        $cSelect->where('o.OrderId LIKE ?', 'W%');

        $select->where('h.Payed=?', 1);
        $cSelect->where('h.Payed=?', 1);

        if (count($order) > 0) {
            foreach ($order as $key => $val) {
                $select->order($key . ' ' . $val);
            }
        } else {
            $select->order($this->primary() . ' DESC');
        }

        $select->limitPage($page, $count);

        $result = $this->all($select);
        $i = 0;
        foreach ($result as $row) {
            $row['_seq'] = $offset + 1 + ($i++);
            $rows[] = $row;
        }

        $tmp = $this->one($cSelect);
        $pager['total'] = $tmp['total'];

        return $rows;
    }

    public function somedayOrders(&$pager, $params = array(), $order = array())
    {
        $rows = array();
        $count = $pager['limit'] ? (int)$pager['limit'] : 20;
        $page = $pager['page'] ? (int)$pager['page'] : 1;
        $offset = $pager['offset'] ? (int)$pager['offset'] : 0;

        $date = $params['date'];
        $s_time = $params['s_date'] . ' ' . $params['s_hour'] . ':' . $params['s_minute'] . ':00';
        $e_time = $params['e_date'] . ' ' . $params['e_hour'] . ':' . $params['e_minute'] . ':59';
        $freight = $params['freight'] ? $params['freight'] : 8;
        $timeout = (int)$params['timeout'];
        $freight = (int)$freight;

        $config = & Msd_Config::appConfig();

        switch ($params['s_date_key']) {
            case 'assigned':
                $s_date_key = $config->order->status->assigned;
                break;
            case 'issued':
                $s_date_key = $config->order->status->issued;
                break;
            default:
                $s_date_key = $config->order->status->confirmed;
                break;
        }

        $sTable = & $this->t('sales');
        $osTable = & $this->t('order/status');
        $oslTable = & $this->t('order/status/log');
        $doTable = & $this->t('delivery/order');
        $dTable = & $this->t('deliveryman');

        $dselect = & $this->s();
        $dselect->from($doTable->sn('_do_'), array(
            $this->expr('MAX(_do_.Sequence) AS Sequence'),
            '_do_.OrderGuid'
        ));
        $dselect->group('_do_.OrderGuid');

        $oslSelect = & $this->s();
        $oslSelect->from($oslTable->sn('_osl_'), array(
            $this->expr('MIN(_osl_.AddTime) AS AddTime'),
            'OrderGuid'
        ));
        $oslSelect->where('_osl_.StatusId=?', $config->order->status->delivered);
        $oslSelect->group('_osl_.OrderGuid');

        $osl2Select = & $this->s();
        $osl2Select->from($oslTable->sn('_osl2_'), array(
            $this->expr('MIN(_osl2_.AddTime) AS AddTime'),
            'OrderGuid'
        ));

        $osl2Select->where('_osl2_.StatusId=?', $s_date_key);

        $osl2Select->group('_osl2_.OrderGuid');

        $select = & $this->s();
        $select->from($this->sn('o'));

        $select->join($sTable->sn('s'), 's.SalesGuid=o.SalesGuid', '');
        $select->join($osTable->sn('os'), 'os.StatusId=o.StatusId', array());
        $select->join($this->expr('(' . $dselect->__toString() . ')'), 't.OrderGuid=o.OrderGuid', '');
        $select->join($doTable->sn('do'), 'do.Sequence=t.Sequence', '');
        $select->join($dTable->sn('d'), 'd.DlvManGuid=do.DlvManGuid', array(
            'd.DlvManName'
        ));

        $select->where('o.AddTime>?', $s_time);
        $select->where('o.AddTime<?', $e_time);
        $select->where('o.StatusId=?', $config->order->status->delivered);

        $select->join($this->expr('(' . $oslSelect->__toString() . ')'), 't_2.OrderGuid=o.OrderGuid', array(
            $this->expr('DATEDIFF(MINUTE, t_3.AddTime, t_2.AddTime) AS minutes_cost'),
            $this->expr('t_2.AddTime AS DeliveredTime')
        ));
        $select->join($this->expr('(' . $osl2Select->__toString() . ')'), 't_3.OrderGuid=o.OrderGuid', array(
            $this->expr('t_3.AddTime AS AssignedTime')
        ));
        $select->where($this->expr('(
				(sv.TimeDirection IS NULL AND (
					(f.Distance<=3000 AND DATEDIFF(MINUTE, t_3.AddTime, t_2.AddTime)>=' . $timeout . ')
					OR
					(f.Distance>3000 AND f.Distance<=5000 AND DATEDIFF(MINUTE, t_3.AddTime, t_2.AddTime)>=' . ($timeout + 10) . ')
					OR
					(f.Distance>5000 AND DATEDIFF(MINUTE, t_3.AddTime, t_2.AddTime)>=' . ($timeout + 20) . ')
				))
				OR
				(sv.TimeDirection IS NOT NULL AND DATEDIFF(MINUTE, CONCAT(sv.ReqDate, \' \', sv.ReqTimeStart), t_2.AddTime)>=5)
				)'));

        if ($freight <= 8) {
            $select->where('o.Distance<=?', 3000);
        } else if ($freight > 8 && $freight <= 15) {
            $select->where($this->expr('o.Distance<=5000 AND o.Distance>3000'));
        } else if ($freight > 15) {
            $select->where('o.Distance<=6000 AND o.Distance>5000');
        }

        if ($params['without_pre']) {
            $select->where($this->expr('o.TimeDirection IS NULL'));
        }

        if ($params['without_chg']) {
            $select->where('(o.OrderId NOT LIKE \'W%\' AND ov.VersionId=\'0\') OR (o.OrderId LIKE \'W%\' AND ov.VersionId=\'1\')');
        }

        if ($params['deliver']) {
            $select->where('d.DlvManName=?', $params['deliver']);
        }

        $select->limitPage($page, $count);

        $select->order('o.TimeDirection DESC');
        $select->order('minutes_cost DESC');
        $select->order('o.AddTime DESC');

        $result = $this->all($select);

        $i = 0;
        foreach ($result as $row) {
            $row['_seq'] = $offset + 1 + ($i++);
            $rows[] = $row;
        }

        $pager['total'] = $this->somedayOrdersCount($params);

        return $rows;
    }

    /**
     * 某日订单总数
     *
     * @param unknown_type $date
     */
    public function somedayOrdersCount($params = array())
    {
        $total = 0;
        $config = & Msd_Config::appConfig();

        $freight = (int)$params['freight'];
        $timeout = (int)$params['timeout'];
        $s_time = $params['s_date'] . ' ' . $params['s_hour'] . ':' . $params['s_minute'] . ':00';
        $e_time = $params['e_date'] . ' ' . $params['e_hour'] . ':' . $params['e_minute'] . ':59';

        $oaTable = & $this->t('order/analysis');
        $sTable = & $this->t('sales');
        $osTable = & $this->t('order/status');
        $oslTable = & $this->t('order/status/log');
        $doTable = & $this->t('delivery/order');
        $dTable = & $this->t('deliveryman');

        switch ($params['s_date_key']) {
            case 'assigned':
                $s_date_key = $config->order->status->assigned;
                break;
            case 'issued':
                $s_date_key = $config->order->status->issued;
                break;
            default:
                $s_date_key = $config->order->status->confirmed;
                break;
        }

        $dselect = & $this->s();
        $dselect->from($doTable->sn('_do_'), array(
            $this->expr('MAX(_do_.Sequence) AS Sequence'),
            '_do_.OrderGuid'
        ));
        $dselect->group('_do_.OrderGuid');

        $oslSelect = & $this->s();
        $oslSelect->from($oslTable->sn('_osl_'), array(
            $this->expr('MIN(_osl_.AddTime) AS AddTime'),
            'OrderGuid'
        ));
        $oslSelect->where('_osl_.StatusId=?', $config->order->status->delivered);
        $oslSelect->group('_osl_.OrderGuid');

        $osl2Select = & $this->s();
        $osl2Select->from($oslTable->sn('_osl2_'), array(
            $this->expr('MIN(_osl2_.AddTime) AS AddTime'),
            'OrderGuid'
        ));

        $osl2Select->where('_osl2_.StatusId=?', $s_date_key);

        $osl2Select->group('_osl2_.OrderGuid');

        $select = & $this->s();
        $select->from($this->sn('o'), 'COUNT(o.OrderGuid) AS total');

        $select->join($sTable->sn('s'), 's.SalesGuid=o.SalesGuid', '');
        $select->join($osTable->sn('os'), 'os.StatusId=o.StatusId', array());
        $select->join($this->expr('(' . $dselect->__toString() . ')'), 't.OrderGuid=o.OrderGuid', '');
        $select->join($doTable->sn('do'), 'do.Sequence=t.Sequence', '');
        $select->join($dTable->sn('d'), 'd.DlvManGuid=do.DlvManGuid', array());

        $select->where('o.AddTime>?', $s_time);
        $select->where('o.AddTime<?', $e_time);
        $select->where('o.StatusId=?', $config->order->status->delivered);

        if ($timeout > 0) {
            $select->join($this->expr('(' . $oslSelect->__toString() . ')'), 't_2.OrderGuid=o.OrderGuid', array());
            $select->join($this->expr('(' . $osl2Select->__toString() . ')'), 't_3.OrderGuid=o.OrderGuid', array());
            $select->where($this->expr('(
					(o.TimeDirection IS NULL AND (
						(o.Distance<=3000 AND DATEDIFF(MINUTE, t_3.AddTime, t_2.AddTime)>=' . $timeout . ')
						OR
						(o.Distance>3000 AND o.Distance<=5000 AND DATEDIFF(MINUTE, t_3.AddTime, t_2.AddTime)>=' . ($timeout + 10) . ')
						OR
						(o.Distance>5000 AND DATEDIFF(MINUTE, t_3.AddTime, t_2.AddTime)>=' . ($timeout + 20) . ')
					))
					OR
					(o.TimeDirection IS NOT NULL AND DATEDIFF(MINUTE, CONCAT(o.ReqDate, \' \', o.ReqTimeStart), t_2.AddTime)>=5)
					)'));
        }

        if ($freight > 0) {
            if ($freight <= 8) {
                $select->where('o.Distance<=?', 3000);
            } else if ($freight > 8 && $freight <= 15) {
                $select->where($this->expr('o.Distance<=5000 AND o.Distance>3000'));
            } else if ($freight > 15) {
                $select->where('o.Distance<=6000 AND o.Distance>5000');
            }
        }

        if ($params['without_pre']) {
            $select->where('o.TimeDirection IS NULL');
        }

        if ($params['without_chg']) {
            $select->where('(SUBSTRING(o.OrderId, 1, 1)!=\'W\' AND ov.VersionId=\'0\') OR (SUBSTRING(o.OrderId, 1, 1)=\'W\' AND ov.VersionId=\'1\')');
        }

        if ($params['deliver']) {
            $select->where('d.DlvManName=?', $params['deliver']);
        }

        $row = $this->one($select);

        return (int)$row['total'];
    }

    /**
     * 超时单汇总
     *
     * @param string $date
     */
    public function timeoutSummary($params = array())
    {
        $data = array();

        $p1 = $p2 = $p3 = $params;
        $p1['timeout'] = 0;
        $p1['without_pre'] = 0;
        $p1['without_chg'] = 0;
        $data['total'] = $this->somedayOrdersCount($p1);

        $p2['freight'] = 8;
        $data['freight_8'] = $this->somedayOrdersCount($p2);

        $p3['freight'] = 15;
        $data['freight_15'] = $this->somedayOrdersCount($p3);

        $p3['freight'] = 18;
        $data['freight_18'] = $this->somedayOrdersCount($p3);

        return $data;
    }

    public function CustHasOrder($CustGuid)
    {
        $sTable = & $this->t('sales');

        $select = & $this->s();
        $select->from($this->sn('o'));
        $select->join($sTable->sn('s'), 's.SalesGuid=o.SalesGuid', array());
        $select->where('s.CustGuid=?', $CustGuid);
        $select->limit(1);

        return $this->one($select);
    }

    //调用优惠券存储过程
    public function ccgc($cguid)
    {
        $this->db->query("exec uspCreateOrderDiscount '" . $cguid . "'");
    }

    //调用优惠券存储过程
    public function giftccgc($phone, $codes)
    {
        $codes = array_values($codes);
        $count = count($codes);
        
        if ($count == 1) {
            $sql = "exec uspUsedGiftTicket '" . $phone . "','" . $codes[0] . "'";
        } elseif ($count == 2) {
            $sql = "exec uspUsedGiftTicket '" . $phone . "','" . $codes[0] . "','" . $codes[1] . "'";
        } elseif ($count == 3) {
            $sql = "exec uspUsedGiftTicket '" . $phone . "','" . $codes[0] . "','" . $codes[1] . "','" . $codes[2] . "'";
        }
        Msd_Log::getInstance()->gift($sql);
        $this->db->query($sql);
    }
}
