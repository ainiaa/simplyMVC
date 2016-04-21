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
        $postList = $this->PostService->getPostList();
        $this->setMainTpl('post_list.tpl.html');

        $this->assign('title', 'Simply MVC backend - table list');
        $this->assign('tableHeaderList', array('post_author', 'post_date', 'post_title', 'post_excerpt',));
        $this->assign('list', $postList);
        $this->display();
    }

    /**
     * @author Jeff Liu<jeff.liu.guo@gmail.com>
     */
    public function addAction()
    {

        $this->assign('postContent', "hello, world!!");

        if (IS_POST) {
            $postTitle = $_POST['post_title'];
            $postContent = $_POST['post_content'];
            $data = array('post_title' => $postTitle,'post_content' => $postContent);
            $data = $this->PostService->buildPostData($data);
            $id = $this->PostService->addPost($data);
            if (empty($id)) {
                $error = $this->PostService->getDbError();
                $this->displayError($error);
            } else {
                $this->redirect('index.php?m=default&c=post&g=backend&a=index');
            }
        } else {
            $this->setMainTpl('post_add.tpl.html');
            $this->display();
        }


    }
}
