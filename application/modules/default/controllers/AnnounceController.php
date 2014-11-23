<?php

/**
 *
 * 网站公告
 * @author pang
 *
 */
class AnnounceController extends Msd_Controller_Default
{

    public function indexAction()
    {
        $id = (int)$this->getRequest()->getParam('id', 0);
        $title = trim(urldecode($this->getRequest()->getParam('title', '')));

        $this->pager_init();
        $table = & Msd_Dao::table('article');
        $config = & Msd_Config::appConfig();
        $cConfig = & Msd_Config::cityConfig();

        $rows = $table->search($this->pager, array(
            'CategoryId' => array(
                $cConfig->db->article->category->announce
            ),
            'PubFlag' => '1',
            'passby_pager' => true,
            'Regions' => Msd_Waimaibao_Region::RegionGuids()
        ), array(
            'OrderNo' => 'ASC',
            'PubTime' => 'DESC'
        ));

        if (is_array($rows) && count($rows) > 0) {
            if ($id || strlen($title)) {
                foreach ($rows as $row) {
                    if ($row['ArticleId'] == $id || $title == $row['Title']) {
                        $this->view->data = $row;
                        break;
                    }
                }

                if (!$this->view->data) {
                    $this->_helper->getHelper('Redirector')->gotoUrl($this->scriptUrl . 'announce');
                }
            } else {
                $this->view->data = $rows[0];
            }
        }

        $this->view->rows = $rows;
        $this->view->page_links = $this->page_links();
        $this->view->request = $_REQUEST;
        $this->view->views = Msd_Article::updateViews($this->view->data['ArticleId']);
    }
}