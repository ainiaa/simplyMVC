<?php

/**
 * 分类管理控制器
 * Class CategoryController
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
        $this->assign('tableHeaderList', ['name', 'desc', 'parent_id', 'path', 'depth']);

        $this->display();
    }

    /**
     * 新增
     * @author Jeff Liu<jeff.liu.guo@gmail.com>
     */
    public function addAction()
    {
        $this->assign('action', './?debug=1&b=2&m=default&c=category&g=backend&a=add');
        if (IS_POST) {
            $name           = I('post.name');
            $desc           = I('post.desc');
            $parentId       = I('post.parentid');
            $parentCategory = $this->CategoryService->getCategoryInfo($parentId);
            $data           = ['name' => $name, 'desc' => $desc, 'parent_id' => $parentId];
            if ($parentCategory) {
                $data['path']  = $parentCategory['path'] . $parentId . ',';
                $data['depth'] = (int)$parentCategory['depth'] + 1;
            } else {
                $data['path']  = ',0,';
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
            $categoryList           = $this->CategoryService->getCategoryList();
            $parentCategorySelector = $this->CategoryService->generateParentsSelector(-1);
            $this->assign('list', $categoryList);
            $this->assign('parentCategorySelector', $parentCategorySelector);

            $this->setMainTpl('category_add.tpl.html');
            $this->display();
        }
    }

    /**
     * 编辑
     */
    public function editAction()
    {
        //todo 还没有实现
    }

    /**
     * 删除
     */
    public function deleteAction()
    {
        //todo 还没有实现
        $id           = I('get.id');
        $deleteStatus = $this->CategoryService->deleteCategoryById($id);
        $this->redirect('index.php?m=default&c=category&g=backend&a=index');
    }
}
