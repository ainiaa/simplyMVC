<?php

/**
 * 权限管理 角色
 * @author Jeff.Liu<liuwy@imageco.com.cn>
 * @date   2017/05/27
 */
class RoleController extends AdminController
{

    /**
     * @var RoleDAO
     */
    private $RoleDAO;

    /**
     * @var PrivilegeDAO
     */
    private $PrivilegeDAO;

    /**
     * 角色列表
     * @author Jeff.Liu<liuwy@imageco.com.cn>
     * @date   2017/05/27
     */
    public function indexAction()
    {
        $perPageNum = 20;
        $where      = [];
        if (isset($_GET['role_name']) && $_GET['role_name']) {
            $where['role_name'] = ['LIKE', '%' . $_GET['role_name'] . '%'];
        }
        $count     = $this->RoleDAO->getCount($where);
        $Page      = new Page($count, $perPageNum); // 实例化分页类 传入总记录数和每页显示的记录数
        $pageShow  = $Page->show(); // 分页显示输出
        $limit     = $Page->firstRow . ',' . $Page->listRows;
        $queryList = $this->RoleDAO->getListWithLimit($where, $limit);
        $this->assign('queryList', $queryList);
        $this->assign('pageShow', $pageShow);
        $this->display();
    }

    /**
     * 删除角色
     * @author Jeff.Liu<liuwy@imageco.com.cn>
     * @date   2017/05/27
     */
    public function deleteAction()
    {
        $id  = I('id');
        $ret = $this->RoleDAO->delete(['id' => $id]);
        if ($ret) {
            $data = ['status' => 1, 'info' => '角色删除成功'];
        } else {
            $data = ['status' => 0, 'info' => '角色删除失败'];
        }
        $this->ajaxReturn($data, 'JSON');

    }

    /**
     * 添加角色
     * @author Jeff.Liu<liuwy@imageco.com.cn>
     * @date   2017/05/27
     */
    public function addAction()
    {
        if (IS_POST) {
            // 获取表单数据
            $getPost   = I('post.');
            $role_name = I('post.role_name');
            $role_desc = I('post.role_desc');
            if (empty($role_name)) {
                $this->error('角色名称不能为空');
            }

            if (empty($role_desc)) {
                $this->error('角色描述不能为空');
            }

            $res = $this->RoleDAO->add($getPost);
            if ($res) {
                $this->success('角色添加成功', ['返回列表页' => make_url('Admin/Role/index'),]);
            } else {
                $this->error('角色添加失败:' . var_export($res, 1) );
            }
        }
        $this->display();
    }

    /**
     * 编辑角色
     * @author Jeff.Liu<liuwy@imageco.com.cn>
     * @date   2017/05/27
     */
    public function editAction()
    {
        if (IS_POST) {
            // 获取表单数据
            $getPost   = I('post.');
            $id        = $getPost['id'];
            $role_name = I('post.role_name');
            $role_desc = I('post.role_desc');
            if (empty($role_name)) {
                $this->error('角色名称不能为空');
            }
            if (empty($role_desc)) {
                $this->error('角色描述不能为空');
            }
            unset($getPost['id']);
            $res = $this->RoleDAO->saveData($getPost, ['id' => $id]);
            if ($res) {
                $this->success('角色修改成功', ['返回列表页' => make_url('index'),]);
            } else {
                $this->error('角色修改失败:' . var_export($res, 1));
            }
        }
        $id   = I('get.id');
        $info = $this->RoleDAO->getOne(['id' => $id]);
        $this->assign('info', $info);
        $this->display();
    }

    /**
     * 关联权限
     * @author Jeff.Liu<liuwy@imageco.com.cn>
     * @date   2017/05/27
     */
    public function relatePrivilegeAction()
    {
        if (IS_POST) {
            $data                = I('post.');
            $id                  = isset($data['id']) ? $data['id'] : '';
            $relatePrivilegeList = isset($data['relatePrivilege']) ? $data['relatePrivilege'] : '';
            if ($id || empty($relatePrivilegeList)) {
                $ret = $this->RoleDAO->relatePrivilege($id, $relatePrivilegeList);
                if ($ret) {
                    $this->success('角色菜单权限关联成功', ['返回列表页' => make_url('index'),]);
                } else {
                    $this->error('角色菜单权限关联失败：' . 'id missing or relatePrivilege missing');
                }
            } else {
                $this->error('角色菜单权限关联失败2：' . 'id missing or relatePrivilege missing');
            }
        }
        $id = I('id');
        if (!is_numeric($id)) {
            $this->error('id missing');
        }
        $privilegeList    = $this->PrivilegeDAO->getData();
        $roleInfo         = $this->RoleDAO->getOne($id);
        $relatedPriviList = $this->RoleDAO->getRealatedPrivilege($id);
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
        $this->assign('roleInfo', $roleInfo);
        $this->display();
    }

    /**
     * 关联菜单权限
     * @author Jeff.Liu<liuwy@imageco.com.cn>
     * @date   2017/05/27
     */
    public function relateMenuPrivilegeAction()
    {
        if (IS_POST) {
            $data                    = I('post.');
            $id                      = isset($data['id']) ? $data['id'] : '';
            $relateMenuPrivilegeList = isset($data['relateMenuPrivilege']) ? $data['relateMenuPrivilege'] : '';
            if ($id || empty($relateMenuPrivilegeList)) {
                $ret = $this->RoleDAO->relateMenuPrivilege($id, $relateMenuPrivilegeList);
                if ($ret) {
                    $this->success('角色权限关联成功', ['返回列表页' => make_url('index'),]);
                } else {
                    $this->error('角色权限关联失败：' . 'id missing or relatePrivilege missing');
                }
            } else {
                $this->error('角色权限关联失败2：' . 'id missing or relatePrivilege missing');
            }
        }
        $id = I('id');
        if (!is_numeric($id)) {
            $this->error('id missing');
        }
        $menuPrivilegeList    = $this->RoleDAO->getMenuPrivilegeList();
        $roleInfo             = $this->RoleDAO->getOne($id);
        $relatedMenuPriviList = $this->RoleDAO->getRealatedMenuPrivilege($id);
        array_walk(
                $menuPrivilegeList,
                function (&$item) use ($relatedMenuPriviList) {
                    $related = 0;
                    foreach ($relatedMenuPriviList as $index => $relatedMenuPriv) {
                        if ($item['pid'] == $relatedMenuPriv['pid'] && $item['mid'] == $relatedMenuPriv['mid']) {
                            $related = 1;
                            break;
                        }
                    }
                    $item['related']   = $related;
                    $item['show_tips'] = sprintf('%s(%s)', $item['menu_name'], $item['priv_name']);
                }
        );
        $this->assign('menuPrivilegeList', $menuPrivilegeList);
        $this->assign('roleInfo', $roleInfo);
        $this->display();
    }
}
