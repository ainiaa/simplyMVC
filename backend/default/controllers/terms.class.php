<?php

/**
 * terms 管理相关
 * Class AdminController
 */
class TermsController extends BackendController
{

    /**
     * @var TermsService
     */
    public $TermsService;

    /**
     * terms列表
     * @author Jeff Liu
     */
    public function indexAction()
    {
        $termsList = $this->TermsService->getList();

        $this->setMainTpl('terms_list.tpl.html');

        $this->assign('title', 'Simply MVC backend - table list');
        $this->assign('tableHeaderList', array('term_id', 'name', 'slug',));
        $this->assign('termsList', $termsList);
        $this->display();
    }
}
