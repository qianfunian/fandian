<?php

/**
 * 前端控制器，继承自Zend
 *
 * @author pang
 *
 */
require_once 'Zend/Controller/Action.php';

class Msd_Controller extends Zend_Controller_Action
{
    protected $baseUrl = '/';
    protected $scriptUrl = '/';
    protected $pager = array();
    protected $imageUrl = '';
    protected $staticUrl = '';
    protected $cssUrl = '';
    protected $jsUrl = '';
    protected $designUrl = '';
    protected $controllerParams = array();
    protected $isAjax = false;
    protected $errorRedirectTimer = 10;
    protected static $browser = null;
    protected $fiestParams = array();
    protected static $outputGzipped = false;

    public function init()
    {
        if (strpos($this->_request->getServer('HTTP_USER_AGENT'), 'MSIE 6.0')) {
            //跳转到提示页面
            //$this->_forward("ie6",'keepalive','default');
            $this->_redirect('/keepalive/ie6');
            return;
        }

        if (strcmp(MSD_FORCE_CITY, 'suzhoudsh') == 0 && time() > strtotime('2013-12-30 17:30:00')) {
            // 跳转到提示页面
            //$this->_forward('close','keepalive','default');
            $this->_redirect('/keepalive/close');
            return;
        }

        $config = & Msd_Config::appConfig();
        $cacher = & Msd_Cache_Remote::getInstance();
        $vars = Msd_Cache_Loader::Systemvars();

        $cName = strtolower(str_replace('Controller', '', get_class($this)));

        if (self::$browser == null) {
            self::$browser = new Msd_Browser();
        }

        $this->view->page_name = strtolower(str_replace('Controller', '', get_class($this)));
        $this->view->baseUrl = $this->baseUrl = $config->base_url;

        $this->imageUrl = $vars['image_url'] ? $vars['image_url'] : $this->baseUrl . 'images/';
        $this->cssUrl = $vars['css_url'] ? $vars['css_url'] : $this->baseUrl . 'css/';
        $this->jsUrl = $vars['js_url'] ? $vars['js_url'] : $this->baseUrl . 'js/';
        $this->staticUrl = $vars['static_url'] ? $vars['static_url'] : $this->baseUrl;
        $this->designUrl = $vars['design_url'] ? $vars['design_url'] : $this->baseUrl;

        $this->view->imageUrl = $this->imageUrl;
        $this->view->cssUrl = $this->cssUrl;
        $this->view->jsUrl = $this->jsUrl;
        $this->view->staticUrl = $this->staticUrl;
        $this->view->designUrl = $this->designUrl;

        $this->controllerParams = & $this->getRequest()->getParams();
        $this->view->controllerParams = $this->controllerParams;

        $this->view->isAjax = $this->isAjax = $this->getRequest()->isXmlHttpRequest() ? true : false;
        $this->view->inDebug = (bool)Msd_Config::appConfig()->global->in_debug;

        $this->view->request = $this->getRequest()->getParams();

        $this->view->errorRedirectTimer = $this->errorRedirectTimer;

        $module = strtolower($this->controllerParams['module']);
        if ($config->enable_pad_css && ($module == 'default' || $module == 'member') && self::$browser->isIOS()) {
            $this->view->setScriptPath(APPLICATION_PATH . '/modules/' . $module . 'pad/views/scripts/');
            $this->view->addScriptPath(APPLICATION_PATH . '/modules/' . $module . 'pad/views/scripts/' . $cName . '/');
        }

        if (!Zend_Controller_Front::getInstance()->getParam('noViewRenderer')) {
            $this->view->addScriptPath(APPLICATION_PATH . '/modules/' . $module . '/views/scripts/' . $cName . '/');
        }

        parent::init();
    }

    public static function gzipped()
    {
        self::$outputGzipped = true;
    }

    public static function isGzipped()
    {
        return self::$outputGzipped;
    }

    protected static function injectionCheck($param)
    {
        $flag = false;
        $pat = '/(select|insert|update|delete|union|into|load_file|outfile|\.\.\/|\.\.\\|\*|\')/i';

        if (is_array($param)) {
            foreach ($param as $key => $val) {
                $flag = self::injectionCheck($key);

                if ($flag) {
                    Msd_Log::getInstance()->injection('Key:' . $key);
                    return true;
                } else {
                    $flag = self::injectionCheck($val);
                    if ($flag) {
                        return true;
                    }
                }
            }
        } else if (!is_object($param)) {
            $flag = preg_match($pat, $param);
            if ($flag) {
                Msd_Log::getInstance()->injection($param);
            }
        }

        return $flag;
    }

    public static function staticUrl()
    {
        $cacher = & Msd_Cache_Remote::getInstance();
        $vars = Msd_Cache_Loader::Systemvars();
        $baseUrl = Msd_Config::appConfig()->base_url;

        return $vars['static_url'] ? $vars['static_url'] . FANDIAN_APP_VER . '/' : $baseUrl;
    }

    /**
     * 分页初始化
     *
     */
    protected function pager_init($params = array())
    {
        $this->pager = array(
            'page' => 1,
            'limit' => 20,
            'skip' => 0,
            'total' => 0
        );
        foreach ($params as $k => $v) {
            $this->pager[$k] = $v;
        }

        $this->pager['page'] = $params['page'] ? (int)$params['page'] : (int)$this->getRequest()->getParam('pg', (int)$this->getRequest()->getParam('page', 1));
        $this->pager['limit'] = $params['limit'] ? (int)$params['limit'] : (int)$this->getRequest()->getParam('limit', (int)$this->getRequest()->getParam('rows', 20));
        $this->pager['skip'] = $params['skip'] ? (int)$params['skip'] : $this->pager['limit'] * ($this->pager['page'] - 1);
    }

    /**
     * 生成分页字符串
     *
     * @return string
     */
    protected function page_links($view = null)
    {
        $ps = array(
            'total' => $this->pager['total'],
            'perpage' => $this->pager['limit'],
        );
        $pager = new Msd_Pager($ps, $view ? $this : null);

        return $pager->show(1);
    }

    protected function pages()
    {
        $limit = $this->pager['limit'];
        $total = $this->pager['total'];
        $pages = ceil($total / $limit) == intval($total / $limit) ? intval($total / $limit) : ceil($total / $limit);
        $this->pager['pages'] = $pages;

        return $pages;
    }

    /**
     * 显示“建设中”的临时页面
     *
     * @param string $phtml
     */
    protected function building($phtml = 'building/index.phtml')
    {
        echo $this->view->render($phtml);
        exit(0);
    }

    /**
     * 重定向到某个url
     *
     * @param string $url
     */
    public function redirect($url)
    {
        $this->_helper->getHelper('Redirector')->gotoUrl($this->scriptUrl . $url);
        exit(0);
    }

    /**
     * ajax输出
     * @param unknown $output
     */
    protected static function ajaxOutput($output = array(), $params = array())
    {
        @ob_end_clean();

        if (!isset($output['success'])) {
            $output['success'] = 1;
        }

        $buffer = $params['prefix'] ? $params['prefix'] : '';
        $buffer .= json_encode($output);
        $buffer .= $params['suffix'] ? $params['suffix'] : '';

        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: pre-check=0, post-check=0, max-age=0', false);
        header('Pragma: no-cache');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        header('Content-Type: text/html; charset=gbk');
        header('Conection: keep-alive');
        if (preg_match('/gzip/i', $_SERVER['HTTP_ACCEPT_ENCODING'])) {
            $buffer = gzencode($buffer, 2, FORCE_GZIP);
            header('Content-Encoding: gzip');
        }

        header('Content-Length: ' . strlen($buffer));

        echo $buffer;
        ob_end_flush();
        exit(0);
    }

    protected function fiestParams()
    {
        $cValentine = new DateTime('2012-08-23 23:59:59');
        $this->fiestParams['cValentine_ends'] = $cValentine->getTimestamp();

        $this->view->fiestParams = $this->fiestParams;
    }
}
