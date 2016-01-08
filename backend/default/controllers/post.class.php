<?php

/**
 * terms 管理相关
 * Class AdminController
 */
class PostController extends BackendController
{

    /**
     * @var PostService
     */
    public $PostService;

    /**
     * terms列表
     * @author Jeff Liu
     */
    public function indexAction()
    {
        $postList = $this->PostService->getList();
        $this->setMainTpl('post_list.tpl.html');

        $this->assign('title', 'Simply MVC backend - table list');
        $this->assign('tableHeaderList', array('term_id', 'name', 'slug',));
        $this->assign('list', $postList);
        $this->display();
    }

    /**
     * @author Jeff Liu<jeff.liu.guo@gmail.com>
     */
    public function addAction()
    {

        $this->assign('postContent', "hello, world!!");

        $this->setMainTpl('post_add.tpl.html');
        $this->display();
    }
}
