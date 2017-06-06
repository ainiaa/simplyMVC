<?php

/**
 * 权限管理 用户
 * @author Jeff.Liu<liuwy@imageco.com.cn>
 * @date   2017/05/27
 */
class UserController extends AdminController
{

    /**
     * @var UserInfoDAO
     */
    private $UserInfoDAO;

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
     * @author Jeff.Liu<liuwy@imageco.com.cn>
     * @date   2017/05/27
     */
    public function indexAction()
    {
        $perPageNum = 20;
        $where      = [];
        if (isset($_GET['user_name']) && $_GET['user_name']) {
            $where['user_name'] = ['LIKE', '%' . $_GET['user_name'] . '%'];
        }
        if (isset($_GET['node_id']) && $_GET['node_id']) {
            $where['node_id'] = $_GET['node_id'];
        }
        $count     = $this->UserInfoDAO->getCount($where);
        $Page      = new Page($count, $perPageNum); // 实例化分页类 传入总记录数和每页显示的记录数
        $pageShow  = $Page->show(); // 分页显示输出
        $limit     = $Page->firstRow . ',' . $Page->listRows;
        $queryList = $this->UserInfoDAO->getListWithLimit($where, $limit);
        $this->assign('queryList', $queryList);
        $this->assign('pageShow', $pageShow);
        $this->display();
    }


    /**
     * 关联用户分组
     * @author Jeff.Liu<liuwy@imageco.com.cn>
     * @date   2017/05/27
     */
    public function relateUserGroupAction()
    {
        if (IS_POST) {
            $data                = I('post.');
            $id                  = isset($data['id']) ? $data['id'] : '';
            $relateUserGroupList = isset($data['relateUserGroup']) ? $data['relateUserGroup'] : '';
            if ($id || empty($relateRoleList)) {
                $ret = $this->UserInfoDAO->relateUserGroup($id, $relateUserGroupList);
                if ($ret) {
                    $this->success('用户关联用户分组成功', ['返回列表页' => make_url('index'),]);
                } else {
                    $this->error('用户关联用户分组失败：' . 'id missing or relateUserGroup missing');
                }
            } else {
                $this->error('角色权限关联失败2：' . 'id missing or relateUserGroup missing');
            }
        }
        $id = I('id');
        if (!is_numeric($id)) {
            $this->error('id missing');
        }
        $userGroupList        = $this->UserGroupDAO->getData();
        $userInfo             = $this->UserInfoDAO->getOne(['id' => $id]);
        $relatedUserGroupList = $this->UserInfoDAO->getRealatedUserGroup($id);
        array_walk(
                $userGroupList,
                function (&$item) use ($relatedUserGroupList) {
                    if (in_array($item['id'], $relatedUserGroupList)) {
                        $item['related'] = 1;
                    } else {
                        $item['related'] = 0;
                    }
                }
        );
        $this->assign('userGroupList', $userGroupList);
        $this->assign('userInfo', $userInfo);
        $this->display();
    }

    /**
     * 关联角色
     * @author Jeff.Liu<liuwy@imageco.com.cn>
     * @date   2017/05/27
     */
    public function relateRoleAction()
    {
        if (IS_POST) {
            $data           = I('post.');
            $id             = isset($data['id']) ? $data['id'] : '';
            $relateRoleList = isset($data['relateRole']) ? $data['relateRole'] : '';
            if ($id || empty($relateRoleList)) {
                $ret = $this->UserInfoDAO->relateRole($id, $relateRoleList);
                if ($ret) {
                    $this->success('用户关联角色成功', ['返回列表页' => make_url('index'),]);
                } else {
                    $this->error('用户关联角色失败：' . 'id missing or relateRole missing');
                }
            } else {
                $this->error('角色权限关联失败2：' . 'id missing or relateRole missing');
            }
        }
        $id = I('id');
        if (!is_numeric($id)) {
            $this->error('id missing');
        }
        $roleList        = $this->RoleDAO->getData();
        $userInfo        = $this->UserInfoDAO->getOne(['id' => $id]);
        $relatedRoleList = $this->UserInfoDAO->getRealatedRole($id);
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
        $this->assign('userInfo', $userInfo);
        $this->display();
    }
}
