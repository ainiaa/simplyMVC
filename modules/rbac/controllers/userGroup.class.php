<?php

/**
 * 权限管理 用户分组
 * @author Jeff.Liu<jeff.liu.guo@gmail.com>
 * @date   2017/05/27
 */
class UserGroupController extends AdminController
{

    /**
     * @var UserGroupDAO
     */
    private $UserGroupDAO;

    /**
     * @var RoleDAO
     */
    private $RoleDAO;

    /**
     * 列表
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     * @date   2017/05/27
     */
    public function indexAction()
    {
        $perPageNum = 20;
        $where      = [];
        if (isset($_GET['menu_name']) && $_GET['menu_name']) {
            $where['menu_name'] = ['LIKE', '%' . $_GET['menu_name'] . '%'];
        }
        $count     = $this->UserGroupDAO->getCount($where);
        $Page      = new Page($count, $perPageNum); // 实例化分页类 传入总记录数和每页显示的记录数
        $pageShow  = $Page->show(); // 分页显示输出
        $limit     = $Page->firstRow . ',' . $Page->listRows;
        $queryList = $this->UserGroupDAO->getListWithLimit($where, $limit);
        $this->assign('queryList', $queryList);
        $this->assign('pageShow', $pageShow);
        $this->display();
    }

    /**
     * 删除用户分组
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     * @date   2017/05/27
     */
    public function deleteAction()
    {
        $id  = I('id');
        $ret = $this->UserGroupDAO->delete(['id' => $id]);
        if ($ret) {
            $data = ['status' => 1, 'info' => '用户分组删除成功'];
        } else {
            $data = ['status' => 0, 'info' => '用户分组删除失败'];
        }
        $this->ajaxReturn($data);

    }

    /**
     * 添加用户分组
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     * @date   2017/05/27
     */
    public function addAction()
    {
        if (IS_POST) {
            // 获取表单数据
            $getPost    = I('post.');
            $group_name = I('post.group_name');
            if (empty($group_name)) {
                $this->error('用户分组名称不能为空');
            }

            $res = $this->UserGroupDAO->add($getPost);
            if ($res) {
                $this->success('用户分组添加成功', ['返回列表页' => make_url('index'),]);
            } else {
                $this->error('用户分组添加失败:' . var_export($res, 1));
            }
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
            $getPost    = I('post.');
            $id         = $getPost['id'];
            $group_name = I('post.group_name');
            if (empty($group_name)) {
                $this->error('用户分组名称不能为空');
            }
            unset($getPost['id']);
            $res = $this->UserGroupDAO->saveData($getPost, ['id' => $id]);
            if ($res) {
                $this->success('用户分组修改成功', ['返回列表页' => make_url('index'),]);
            } else {
                $this->error('用户分组修改失败:' . var_export($res, 1));
            }
        }
        $id   = I('get.id');
        $info = $this->UserGroupDAO->getOne(['id' => $id]);
        $this->assign('info', $info);
        $this->display();
    }

    /**
     * 关联角色
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     * @date   2017/05/27
     */
    public function relateRoleAction()
    {
        if (IS_POST) {
            $data           = I('post.');
            $id             = isset($data['id']) ? $data['id'] : '';
            $relateRoleList = isset($data['relateRole']) ? $data['relateRole'] : '';
            if ($id || empty($relateRoleList)) {
                $ret = $this->UserGroupDAO->relateRole($id, $relateRoleList);
                if ($ret) {
                    $this->success('角色权限关联成功', ['返回列表页' => make_url('index'),]);
                } else {
                    $this->error('角色权限关联失败：' . 'id missing or relateRoleList missing');
                }
            } else {
                $this->error('角色权限关联失败2：' . 'id missing or relateRoleList missing');
            }
        }
        $id = I('id');
        if (!is_numeric($id)) {
            $this->error('id missing');
        }
        $roleList        = $this->RoleDAO->getData();
        $userGroupInfo   = $this->UserGroupDAO->getOne($id);
        $relatedRoleList = $this->UserGroupDAO->getRealatedRole($id);
        array_walk(
                $roleList,
                function (&$item) use ($relatedRoleList) {
                    if (in_array($item['id'], $relatedRoleList)) {
                        $item['related'] = 1;
                    } else {
                        $item['related'] = 0;
                    }
                }
        );
        $this->assign('roleList', $roleList);
        $this->assign('userGroupInfo', $userGroupInfo);
        $this->display();
    }
}
