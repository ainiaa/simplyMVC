<?php

/**
 * 管理员
 * Class AdminController
 */
class AdminController extends BackendController
{

    /**
     * @var AdminService
     */
    public $AdminService;

    public function preIndexAction()
    {
    }

    /**
     * 管理员列表
     * @author Jeff Liu
     */
    public function indexAction()
    {

        $adminList = $this->AdminService->getAdminList();



        $this->assign('title', 'Simply MVC backend - table list');

        $this->assign('adminList', $adminList);

        $this->display('backend_layout.tpl.html');
    }

    /**
     * 访问方式例如：
     * http://local.cmvc.com/index.php/default-default-test-id-2-name-namx
     * http://local.cmvc.com/index.php/default-default-test-id-2-name-namx.html
     * 2个url参数是一样的。
     */
    public function testAction($id = 0, $name = '')
    {
        $this->assign('helloWorld', 'Hello World  test  xx !');

        $this->assign('helloWorld1', 'Hello World test id=' . $_REQUEST['id'] . ';name=' . $_REQUEST['name']);

        $this->assign('id', $id);
        $this->assign('name', $name);

        $this->display('hello_world.tpl.html');
    }

    public function postTest()
    {
        echo '在Test之后执行.... <br />';
        //        tag('view_end');
    }

    public function postIndexAction()
    {
    }
}
