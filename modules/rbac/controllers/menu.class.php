<?php

/**
 * 权限管理 菜单
 * @author Jeff.Liu<jeff.liu.guo@gmail.com>
 * @date   2017/05/27
 */
class MenuController extends AdminController
{

    /**
     * @var MenuService
     */
    protected $MenuService;

    /**
     * @var MenuDAO
     */
    private $MenuDAO;

    /**
     * @var PrivilegeDAO
     */
    private $PrivilegeDAO;

    /**
     * 列表
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     * @date   2017/05/27
     */
    public function indexAction()
    {
        $where = [];
        if (isset($_GET['menu_name']) && $_GET['menu_name']) {
            $where['menu_name'] = ['LIKE', '%' . $_GET['menu_name'] . '%'];
        }
        if (isset($_GET['menu_url']) && $_GET['menu_url']) {
            $where['menu_url'] = ['LIKE', '%' . $_GET['priv_type'] . '%'];
        }
        $pageInfo  = $this->MenuService->getPageInfo($where);
        $queryList = $this->MenuDAO->getFullData($where, $pageInfo['limit']);
        $this->assign('queryList', $queryList);
        $this->assign('pageShow', $pageInfo['pageShow']);
        $this->display();
    }

    /**
     * 删除菜单
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     * @date   2017/05/27
     */
    public function deleteAction()
    {
        $id  = I('id');
        $ret = $this->MenuDAO->delete(['id' => $id]);
        if ($ret) {
            $data = ['status' => 1, 'info' => '菜单删除成功'];
        } else {
            $data = ['status' => 0, 'info' => '菜单删除失败'];
        }
        $this->ajaxReturn($data);
    }

    /**
     * 添加菜单
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     * @date   2017/05/27
     */
    public function addAction()
    {
        if (IS_POST) {
            // 获取表单数据
            $getPost   = I('post.');
            $menu_name = I('post.menu_name');
            $menu_url  = I('post.menu_url');
            if (empty($menu_url)) {
                $this->error('菜单url不能为空');
            }
            if (empty($menu_name)) {
                $this->error('菜单名称不能为空');
            }

            $res = $this->MenuDAO->add($getPost);
            if ($res) {
                $this->success('菜单添加成功', ['返回列表页' => make_url('index'),]);
            } else {
                $this->error('菜单添加失败:' . var_export($res, 1));
            }
        } else {
            $validParents = $this->MenuDAO->getValidParentsById(-1);
            $selector     = $this->generateCategorySelector($validParents);
            $this->assign('pidSelector', $selector);
        }
        $this->display();
    }

    /**
     * 编辑菜单
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     * @date   2017/05/27
     */
    public function editAction()
    {
        if (IS_POST) {
            // 获取表单数据
            $getPost   = I('post.');
            $id        = $getPost['id'];
            $menu_name = I('post.menu_name');
            $menu_url  = I('post.menu_url');
            if (empty($menu_url)) {
                $this->error('菜单url不能为空');
            }
            if (empty($menu_name)) {
                $this->error('菜单名称不能为空');
            }
            unset($getPost['id']);
            $res = $this->MenuDAO->saveData($getPost, ['id' => $id]);
            if ($res) {
                $this->success('菜单修改成功', ['返回列表页' => make_url('index'),]);
            } else {
                $this->error('菜单修改失败:' . var_export($res, 1));
            }
        }
        $id           = I('get.id');
        $info         = $this->MenuDAO->getOne(['id' => $id]);
        $validParents = $this->MenuDAO->getValidParentsById($id);
        $selector     = $this->generateCategorySelector($validParents, $info['pid']);
        $this->assign('pidSelector', $selector);
        $this->assign('info', $info);
        $this->display();
    }

    /**
     * 关联权限
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     * @date   2017/05/27
     */
    public function relatePrivilegeAction()
    {
        if (IS_POST) {
            $data                = I('post.');
            $id                  = isset($data['id']) ? $data['id'] : '';
            $relatePrivilegeList = isset($data['relatePrivilege']) ? $data['relatePrivilege'] : '';
            if ($id || empty($relatePrivilegeList)) {
                $ret = $this->MenuDAO->relatePrivilege($id, $relatePrivilegeList);
                if ($ret) {
                    $this->success('菜单权限关联成功', ['返回列表页' => make_url('index'),]);
                } else {
                    $this->error('菜单权限关联失败：' . 'id missing or relatePrivilege missing');
                }
            } else {
                $this->error('菜单权限关联失败：' . 'id missing or relatePrivilege missing');
            }
        }
        $id               = I('id');
        $privilegeList    = $this->PrivilegeDAO->getList(['priv_type' => 'MENU']);
        $menuInfo         = $this->MenuDAO->getOne($id);
        $relatedPriviList = $this->MenuDAO->getRealatedPrivilege($id);
        array_walk(
                $privilegeList,
                function (&$item) use ($relatedPriviList) {
                    if (in_array($item['id'], $relatedPriviList)) {
                        $item['related'] = 1;
                    } else {
                        $item['related'] = 0;
                    }
                }
        );
        $this->assign('privilegeList', $privilegeList);
        $this->assign('menuInfo', $menuInfo);
        $this->display();
    }

}
