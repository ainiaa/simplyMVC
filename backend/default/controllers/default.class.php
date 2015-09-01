<?php

class defaultController extends BackendController
{

    /**
     * @var TestService
     */
    public $TestService;

    public function preIndexAction()
    {
    }

    public function indexAction()
    {
        $this->assign('helloWorld', 'Hello World  xxxx  xx !');

        $this->assign('helloWorld1', 'Hello World gggg !');

        $all = $this->TestService->getAll();

        $add = array(
                'name' => 'addName',
                'desc' => 'addNameaddNameaddNameaddName',
        );

        $ret = $this->TestService->add($add);

        $this->assign('all', $all);

        $tpl = isset($_GET['tpl']) ? trim($_GET['tpl']) : 'index';

        $this->display($tpl . '.tpl.html');
    }

    public function preTest()
    {
        echo __METHOD__, ' 在Test之前执行.... <br />';
        //        tag('view_end');
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
