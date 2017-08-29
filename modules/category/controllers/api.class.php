<?php

/**
 * 分类管理控制器
 * Class ApiController
 */
class ApiController extends BaseController
{
    /**
     * @var CategoryService
     */
    public $CategoryService;

    public function getListAction()
    {
        $list = $this->CategoryService->getCategoryList();
        $this->ajaxReturn($list);
    }

    public function getInfoAction()
    {
        $id = I('get.id');
        $data = $this->CategoryService->getCategoryInfo($id);
        $this->ajaxReturn($data);
    }
}
