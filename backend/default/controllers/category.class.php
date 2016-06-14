<?php

/**
 * 分类 管理相关
 * Class AdminController
 */
class CategoryController extends BackendController
{

    /**
     * @var CategoryService
     */
    public $CategoryService;

    /**
     * terms列表
     * @author Jeff Liu
     */
    public function indexAction()
    {
        $categoryList = $this->CategoryService->getCategoryList();
        $this->setMainTpl('category_list.tpl.html');

        $s = $this->CategoryService->getValidParents(1);
        var_export($s);exit;
        $this->assign('title', 'Simply MVC backend - table list');
        $this->assign('tableHeaderList', array('name', 'desc'));
        $this->assign('list', $categoryList);
        $this->display();
    }

    /**
     * @author Jeff Liu<jeff.liu.guo@gmail.com>
     */
    public function addAction()
    {
        $this->assign('action', './?debug=1&b=2&m=default&c=category&g=backend&a=add');
        if (IS_POST) {
            $name = $_POST['name'];
            $desc = $_POST['desc'];
            $data = array('name' => $name, 'desc' => $desc);
            $data = $this->CategoryService->buildCategoryData($data);
            $id   = $this->CategoryService->addCategory($data);
            if (empty($id)) {
                $error = $this->CategoryService->getDbError();
                $this->displayError($error);
            } else {
                $this->redirect('index.php?m=default&c=category&g=backend&a=index');
            }
        } else {
            $this->setMainTpl('category_add.tpl.html');
            $this->display();
        }
    }

    /**
     *
     */
    public function editAction()
    {
        $this->assign('postContent', "hello, world!!");
        $this->assign('action', './?debug=1&b=2&m=default&c=post&g=backend&a=edit');
        if (IS_POST) {
            $postTitle   = $_POST['post_title'];
            $postContent = $_POST['post_content'];
            $data        = array('post_title' => $postTitle, 'post_content' => $postContent);
            $id          = $_REQUEST['id'];
            $rs          = $this->CategoryService->updatePost($data, ['id' => $id]);
            if (empty($rs)) {
                $error = $this->CategoryService->getDbError();
                $this->displayError($error);
            } else {
                $this->redirect('index.php?m=default&c=post&g=backend&a=index');
            }
        } else {
            $id = $_REQUEST['id'];
            if (empty($id)) {
                $this->displayError("id is empty");
            }
            $post = $this->CategoryService->getOnePost(['id' => $id]);
            $this->setMainTpl('post_add.tpl.html');
            $this->assign('post', $post);
            $this->assign('id', $id);
            $this->display();
        }
    }
}
