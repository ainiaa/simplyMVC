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
        $this->assign('list', $categoryList);

        $this->setMainTpl('category_list.tpl.html');
        $this->assign('title', 'Simply MVC backend - table list');
        $this->assign('tableHeaderList', ['name', 'desc', 'parent_id','path', 'depth']);

        $this->display();
    }

    /**
     * @author Jeff Liu<jeff.liu.guo@gmail.com>
     */
    public function addAction()
    {
        $this->assign('action', './?debug=1&b=2&m=default&c=category&g=backend&a=add');
        if (IS_POST) {
            $name           = $_POST['name'];
            $desc           = $_POST['desc'];
            $parentId       = $_POST['parentid'];
            $parentCategory = $this->CategoryService->getCategoryInfo($parentId);
            $data           = ['name' => $name, 'desc' => $desc, 'parent_id' => $parentId];
            if ($parentCategory) {
                $data['path']  = $parentCategory['path'] . $parentId . ',';
                $data['depth'] = (int)$parentCategory['depth'] + 1;
            } else {
                $data['path']  = ',0,';;
                $data['depth'] = 1;
            }
            $data = $this->CategoryService->buildCategoryData($data);
            $id   = $this->CategoryService->addCategory($data);
            if (empty($id)) {
                $error = $this->CategoryService->getDbError();
                $this->displayError($error);
            } else {
                $this->redirect('index.php?m=default&c=category&g=backend&a=index');
            }
        } else {
            $categoryList          = $this->CategoryService->getCategoryList();
            $parentCategorySelector = $this->CategoryService->generateParentsSelector(-1);
            $this->assign('list', $categoryList);
            $this->assign('parentCategorySelector', $parentCategorySelector);

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
