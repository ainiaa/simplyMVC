<?php

/**
 * 分类管理控制器
 * Class CategoryController
 */
class CategoryController extends CategoryBaseController
{

    /**
     * @var CategoryService
     */
    public $CategoryService;

    /**
     * terms列表
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
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
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     */
    public function addAction()
    {
        $this->assign('action', make_url('modules/category/category/add'));
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
                $this->redirect(make_url('modules/category/category/index'));
            }
        } else {
            $categoryList           = $this->CategoryService->getCategoryList();
            $parentCategorySelector = $this->CategoryService->generateCategorySelector(-1, 0, 'parent_id');
            $this->assign('list', $categoryList);
            $this->assign('parentCategorySelector', $parentCategorySelector);

            $this->setMainTpl('category_add.tpl.html');
            $this->display();
        }
    }

    /**
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     */
    public function detailAction()
    {
        $id = I('id');
        if ($id) {
            $categoryInfo = $this->CategoryService->getCategoryInfo($id);
            if ($categoryInfo) {
                $parentId           = isset($categoryInfo['parent_id']) ? $categoryInfo['parent_id'] : '';
                $parentCategoryInfo = $this->CategoryService->getCategoryInfo($parentId);
                $this->assign('parentCategoryInfo', $parentCategoryInfo);
                $this->assign('categoryInfo', $categoryInfo);
                $this->setMainTpl('category_detail.tpl.html');
                $this->assign('id', $id);
                $this->display();
            } else {
                //todo 分类不存在
            }
        } else {
            //todo id传递有问题
        }
    }

    /**
     * 编辑
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     */
    public function editAction()
    {
        $this->assign('action', make_url('modules/category/category/edit'));
        $id = I('id');
        if ($id) {
            if (IS_POST) {
                $name           = I('post.name');
                $desc           = I('post.desc');
                $parentId       = I('post.parent_id');
                $parentCategory = $this->CategoryService->getCategoryInfo($parentId);
                //todo 需要校验parentId的合法性
                $data = ['name' => $name, 'desc' => $desc, 'parent_id' => $parentId];
                if ($parentCategory) {
                    $data['path']  = $parentCategory['path'] . $parentId . ',';
                    $data['depth'] = (int)$parentCategory['depth'] + 1;
                } else {
                    $data['path']  = ',0,';
                    $data['depth'] = 1;
                }
                $data = $this->CategoryService->buildCategoryData($data);
                $id   = $this->CategoryService->updateCategory($data, ['id' => $id]);
                if (empty($id)) {
                    $error = $this->CategoryService->getDbError();
                    $this->displayError($error);
                } else {
                    $this->redirect(make_url('modules/category/category/index'));
                }
            } else {//显示
                $categoryInfo = $this->CategoryService->getCategoryInfo($id);
                if ($categoryInfo) {
                    $categoryList           = $this->CategoryService->getCategoryList();
                    $parentCategorySelector = $this->CategoryService->generateCategorySelector(
                            $id,
                            $categoryInfo['parent_id']
                    );
                    $this->assign('list', $categoryList);
                    $this->assign('parentCategorySelector', $parentCategorySelector);
                    $this->assign('categoryInfo', $categoryInfo);
                    $this->setMainTpl('category_add.tpl.html');
                    $this->assign('id', $id);
                    $this->display();
                } else {
                    //todo 分类不存在
                }
            }
        } else {
            //todo id传递有问题
        }
    }

    /**
     * 删除
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     */
    public function deleteAction()
    {
        $id = I('get.id');
        $this->CategoryService->deleteCategoryById($id);
        $this->redirect(make_url('modules/category/category/index'));
    }
}
