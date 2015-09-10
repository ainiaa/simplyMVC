<?php

/**
 * Class BackendController
 */
class BackendController extends BaseController
{

    /**
     * @var AdminService
     */
    public $AdminService;

    protected $headerTpl;
    protected $topbarTpl;
    protected $leftMenuTpl;
    protected $mainTpl;
    protected $footerTpl;
    protected $layout;

    protected $defaultTplComponent = array(
            'headerTpl'   => 'header.tpl.html',
            'topbarTpl'   => 'topbar.tpl.html',
            'leftMenuTpl' => 'left_menu.tpl.html',
            'mainTpl'     => 'main.tpl.html',
            'layout'      => 'backend_layout.tpl.html',
    );


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return mixed
     */
    public function getLayout()
    {
        if (empty($this->layout) && isset($this->defaultTplComponent['layout'])) {
            $this->layout = $this->defaultTplComponent['layout'];
        }
        return $this->layout;
    }

    /**
     * @param mixed $layout
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    /**
     * @return mixed
     */
    public function getHeaderTpl()
    {
        return $this->headerTpl;
    }

    /**
     * @param mixed $headerTpl
     *
     * @return BackendController
     */
    public function setHeaderTpl($headerTpl)
    {
        $this->headerTpl = $headerTpl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTopbarTpl()
    {
        return $this->topbarTpl;
    }

    /**
     * @param mixed $topbarTpl
     *
     * @return BackendController
     */
    public function setTopbarTpl($topbarTpl)
    {
        $this->topbarTpl = $topbarTpl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLeftMenuTpl()
    {
        return $this->leftMenuTpl;
    }

    /**
     * @param mixed $leftMenuTpl
     *
     * @return BackendController
     */
    public function setLeftMenuTpl($leftMenuTpl)
    {
        $this->leftMenuTpl = $leftMenuTpl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMainTpl()
    {
        return $this->mainTpl;
    }

    /**
     * @param mixed $mainTpl
     *
     * @return BackendController
     */
    public function setMainTpl($mainTpl)
    {
        $this->mainTpl = $mainTpl;
        $this->assign('mainTpl', $this->mainTpl);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFooterTpl()
    {
        return $this->footerTpl;
    }

    /**
     * @param mixed $footerTpl
     *
     * @return BackendController
     */
    public function setFooterTpl($footerTpl)
    {
        $this->footerTpl = $footerTpl;

        return $this;
    }

    protected function initTplComponent($tplComponent = array())
    {
        if (!is_array($tplComponent) || empty($tplComponent)) {
            $tplComponent = $this->defaultTplComponent;
        } else {
            $tplComponent = array_merge($this->defaultTplComponent, $tplComponent);
        }

        foreach ($tplComponent as $componentName => $componentValue) {
            if (is_string($componentValue)) {
                $this->assign($componentName, $componentValue);
            }
        }
    }

    public function display($tpl = '')
    {
        if (empty($tpl)) {
            $tpl = $this->getLayout();
            if (empty($tpl)) {
                die('please set the template or the layout');
            }
        }

        return parent::display($tpl);
    }

    /**
     * 执行初始化操作
     * @author Jeff Liu
     */
    protected function _initialize()
    {

        $this->assign('title', 'Simply MVC backend');
        $this->initTplComponent();

        //        $this->assign('headerTpl', 'header.tpl.html');
        //        $this->assign('topbarTpl', 'topbar.tpl.html');
        //        $this->assign('leftMenuTpl', 'left_menu.tpl.html');
        //        $this->assign('mainTpl', 'main.tpl.html');
        $sessionInfo = Session::instance()->get('userInfo');
        if (empty($sessionInfo) && $_GET['a'] != 'login') {
            $this->loginAction();
            exit;
        }
    }

    /**
     * 登录
     * @author Jeff Liu
     */
    public function loginAction()
    {
        if (IS_POST) {
            $userName  = $_POST['userName'];
            $password  = $_POST['password'];
            $adminInfo = $this->AdminService->getAdminInfoByUserName($userName);

            if ($adminInfo) {
                $md5Password = md5($password);
                if ($md5Password == $adminInfo['password']) { //登录成功
                    Session::instance()->set('userInfo', array('userName' => $userName, 'password' => md5($password)));
                    header('Location:/?debug=1&b=2&m=default&c=default&g=backend&a=index');

                    return;
                }
            }
            $this->assign('loginError', '用户名或者密码错误');
        }

        $this->display('login.tpl.html');
    }

    /**
     *
     */
    public function loginoutAction()
    {
        Session::instance()->delete('userInfo');
        $this->display('login.tpl.html');
    }

}