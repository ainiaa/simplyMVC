<?php

class defaultController extends FrontendController
{

    /**
     * @var TestService
     */
    public $TestService;

    public function preIndexAction()
    {
        //        trace('本调试信息仅页面Trace中可见');
        //        SmvcDebugHelper::instance()->debug(
        //                array(
        //                        'info'  => $_REQUEST,
        //                        'label' => '$_REQUEST ',
        //                        'level' => 'info',
        //                )
        //        );
        echo __METHOD__, ' 在index之前执行 ... <br />';
        //        echo IniI18N::t('first.hello', 'en_us'), IniI18N::t('first.world', 'en_us'), '<br />';
        //        echo IniI18N::t('second.hello', 'en_us', 'other'), IniI18N::t('second.world', 'en_us', 'other'), '<br />';
        //        echo IniI18N::t('first.hello', 'zh_tw'), IniI18N::t('first.world', 'zh_tw'), '<br />';
        //
        //        echo ArrayI18N::t('first.hello', 'en_us'), ArrayI18N::t('first.world', 'en_us'), '<br />';
        //        echo ArrayI18N::t('second.hello', 'en_us', 'other'), ArrayI18N::t('second.world', 'en_us', 'other'), '<br />';
        //
        //
        //        echo JsonI18N::t('first.hello', 'en_us'), JsonI18N::t('first.world', 'en_us'), '<br />';
        //
        //
        //        echo XmlI18N::t('first.hello', 'en_us'), XmlI18N::t('first.world', 'en_us'), '<br />';

        //        tag('ShowPageTrace');
    }

    public function indexAction()
    {
        echo __METHOD__, '$_REQUEST:', var_export($_REQUEST, 1), '<br />';
        $this->assign('helloWorld', 'Hello World  xxxx  xx !');

        $this->assign('helloWorld1', 'Hello World gggg !');

        $all = $this->TestService->getAll();

        $add = array(
                'name' => 'addName',
                'desc' => 'addNameaddNameaddNameaddName',
        );

        $ret = $this->TestService->add($add);

        SmvcDebugHelper::getInstance()->debug(
                array(
                        'info'  => $ret,
                        'label' => '$ret:' . __METHOD__,
                        'level' => 'info',
                )
        );

        $this->assign('all', $all);

        SmvcDebugHelper::getInstance()->debug(
                array(
                        'info'  => $all,
                        'label' => '$all',
                        'level' => 'error',
                )
        );

        //        echo '<pre>$all:', var_export($all, 1), '</pre>';

        $this->display('hello_world.tpl.html');
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
        echo '在index之后执行.... <br />';
        //        tag('view_end');
    }
}
