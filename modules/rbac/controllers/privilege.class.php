<?php

/**
 * 权限管理 权限
 * @author Jeff.Liu<jeff.liu.guo@gmail.com>
 * @date   2017/05/27
 */
class PrivilegeController extends AdminController
{

    /**
     * @var PrivilegeDAO
     */
    private $PrivilegeDAO;

    /**
     *
     */
    public function indexAction()
    {
        $perPageNum = 20;
        $where      = [];
        if (isset($_GET['priv_name']) && $_GET['priv_name']) {
            $where['priv_name'] = ['LIKE', '%' . $_GET['priv_name'] . '%'];
        }
        if (isset($_GET['priv_type']) && $_GET['priv_type']) {
            $where['priv_type'] = ['LIKE', '%' . $_GET['priv_type'] . '%'];
        }
        $count     = $this->PrivilegeDAO->getCount($where);
        $Page      = new Page($count, $perPageNum); // 实例化分页类 传入总记录数和每页显示的记录数
        $pageShow  = $Page->show(); // 分页显示输出
        $limit     = $Page->firstRow . ',' . $Page->listRows;
        $queryList = $this->PrivilegeDAO->getListWithLimit($where, $limit);
        $this->assign('queryList', $queryList);
        $this->assign('pageShow', $pageShow);
        $this->display();
    }

    /**
     * 删除权限
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     * @date   2017/05/27
     */
    public function delPrivAction()
    {
        $id  = I('id');
        $ret = $this->PrivilegeDAO->delete(['id' => $id]);
        if ($ret) {
            $this->ajaxReturn(['status' => 1, 'info' => '权限删除成功']);
        } else {
            $this->ajaxReturn(['status' => 0, 'info' => '权限删除失败']);
        }

    }

    /**
     * 添加权限
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     * @date   2017/05/27
     */
    public function addAction()
    {
        if (IS_POST) {
            // 获取表单数据
            $getPost   = I('post.');
            $priv_type = I('post.priv_type');
            $priv_name = I('post.priv_name');
            if (empty($priv_name)) {
                $this->error('权限名称不能为空');
            }

            if (empty($priv_type)) {
                $this->error('权限类型不能为空');
            }

            $res = $this->PrivilegeDAO->add($getPost);
            if ($res) {
                $this->success('权限添加成功', ['返回列表页' => make_url('index'),]);
            } else {
                $this->error('权限添加失败:' . var_export($res, 1));
            }
        }
        $this->display();
    }

    /**
     * 编辑
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     * @date   2017/05/27
     */
    public function editAction()
    {
        if (IS_POST) {
            // 获取表单数据
            $getPost = I('post.');
            $id      = $getPost['id'];
            unset($getPost['id']);
            $res = $this->PrivilegeDAO->saveData($getPost, ['id' => $id]);
            if ($res) {
                $this->success('权限修改成功', ['返回列表页' => make_url('index'),]);
            } else {
                $this->error('权限修改失败:' . var_export($res, 1));
            }
        }
        $id   = I('get.id');
        $info = $this->PrivilegeDAO->getOne(['id' => $id]);
        $this->assign('info', $info);
        $this->display();
    }
}
