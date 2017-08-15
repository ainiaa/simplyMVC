<?php

/**
 * 权限管理 公共资源
 * @author Jeff.Liu<jeff.liu.guo@gmail.com>
 * @date   2017/05/27
 */
class PublicResourceController extends AdminController
{

    /**
     * @var PublicResourceDAO
     */
    private $PublicResourceDAO;


    /**
     * 公共资源列表
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     * @date   2017/05/27
     */
    public function indexAction()
    {
        $perPageNum = 20;
        $where      = [];
        if (isset($_GET['resource_name']) && $_GET['resource_name']) {
            $where['resource_name'] = ['LIKE', '%' . $_GET['resource_name'] . '%'];
        }
        if (isset($_GET['resource_type']) && $_GET['resource_type']) {
            $where['resource_type'] = ['LIKE', '%' . $_GET['resource_type'] . '%'];
        }
        $count     = $this->PublicResourceDAO->getCount($where);
        $Page      = new Page($count, $perPageNum); // 实例化分页类 传入总记录数和每页显示的记录数
        $pageShow  = $Page->show(); // 分页显示输出
        $limit     = $Page->firstRow . ',' . $Page->listRows;
        $queryList = $this->PublicResourceDAO->getListWithLimit($where, $limit);
        $this->assign('queryList', $queryList);
        $this->assign('pageShow', $pageShow);
        $this->display();
    }

    /**
     * 删除公共资源
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     * @date   2017/05/27
     */
    public function deleteAction()
    {
        $id  = I('id');
        $ret = $this->PublicResourceDAO->delete(['id' => $id]);
        if ($ret) {
            $data = ['status' => 1, 'info' => '公共资源删除成功'];
        } else {
            $data = ['status' => 0, 'info' => '公共资源删除失败'];
        }

        $this->ajaxReturn($data, 'JSON');

    }

    /**
     * 添加公共资源
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     * @date   2017/05/27
     */
    public function addAction()
    {
        if (IS_POST) {
            // 获取表单数据
            $getPost       = I('post.');
            $resource_name = I('post.resource_name');
            $resource_url  = I('post.resource_url');
            $resource_type = I('post.resource_type');
            if (empty($resource_name)) {
                $this->error('公共资源名称不能为空');
            }
            if (empty($resource_url)) {
                $this->error('公共资源url不能为空');
            }
            if (empty($resource_type)) {
                $this->error('公共资源类型不能为空');
            }

            $res = $this->PublicResourceDAO->add($getPost);
            if ($res) {
                $this->success('公共资源添加成功', ['返回列表页' => make_url('Admin/Role/index'),]);
            } else {
                $this->error('公共资源添加失败:' . var_export($res, 1) );
            }
        }
        $this->display();
    }

    /**
     * 编辑公共资源
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     * @date   2017/05/27
     */
    public function editAction()
    {
        if (IS_POST) {
            // 获取表单数据
            $getPost       = I('post.');
            $id            = $getPost['id'];
            $resource_name = I('post.resource_name');
            $resource_url  = I('post.resource_url');
            $resource_type = I('post.resource_type');
            if (empty($resource_name)) {
                $this->error('公共资源名称不能为空');
            }
            if (empty($resource_url)) {
                $this->error('公共资源url不能为空');
            }
            if (empty($resource_type)) {
                $this->error('公共资源类型不能为空');
            }

            unset($getPost['id']);
            $res = $this->PublicResourceDAO->saveData($getPost, ['id' => $id]);
            if ($res) {
                $this->success('公共资源修改成功', ['返回列表页' => make_url('index'),]);
            } else {
                $this->error('公共资源修改失败:' . var_export($res, 1));
            }
        }
        $id   = I('get.id');
        $info = $this->PublicResourceDAO->getOne(['id' => $id]);
        $this->assign('info', $info);
        $this->display();
    }
}
