<?php

/**
 * Class CategoryBaseController
 */
class CategoryBaseController extends BaseController
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
    protected $breadCrumbTpl;
    protected $defaultTplComponent = [
            'headerTpl'     => 'header.tpl.html',
            'topbarTpl'     => 'topbar.tpl.html',
            'leftMenuTpl'   => 'left_menu.tpl.html',
            'mainTpl'       => 'main.tpl.html',
            'breadCrumbTpl' => 'bread_crumb.tpl.html',
            'layout'        => 'layout.tpl.html',
    ];

    /**
     * @return mixed
     */
    public function getBreadCrumbTpl()
    {
        return $this->breadCrumbTpl;
    }

    /**
     * @param mixed $breadCrumbTpl
     */
    public function setBreadCrumbTpl($breadCrumbTpl)
    {
        $this->breadCrumbTpl = $breadCrumbTpl;
        $this->assign('breadCrumbTpl', $breadCrumbTpl);

        return $this;
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
        $this->assign('layout', $layout);

        return $this;
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
        $this->assign('headerTpl', $headerTpl);

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
        $this->assign('topbarTpl', $topbarTpl);

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
        $this->assign('leftMenuTpl', $leftMenuTpl);

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
        $this->assign('mainTpl', $mainTpl);

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
        $this->assign('footerTpl', $footerTpl);

        return $this;
    }

    /**
     * 初始化模板组件
     *
     * @param array $tplComponent
     */
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

    /**
     * @param string $tpl
     *
     * @return mixed
     */
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

        $sessionInfo = Session::instance()->get('userInfo');
        if (empty($sessionInfo) && (!isset($_GET['a']) || $_GET['a'] != 'login')) { //这个需要修改
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
    public function logoutAction()
    {
        Session::instance()->delete('userInfo');
        $this->display('login.tpl.html');
    }

    /**
     * 根据编号获得对一个tipsInfo.
     *
     * @author Jeff Liu
     *
     * @param $errno
     *
     * @return mixed
     */
    protected function getMessageInfoByErrno($errno)
    {
        return TipsInfoService::getMessageInfoByErrno($errno);
    }


    /**
     * @author Jeff Liu<liuwy@iamgeco.com.cn>
     *
     * @param $errno
     *
     * @return array
     */
    protected function buildAjaxErrorReturnData($errno)
    {
        $messageInfo = $this->getMessageInfoByErrno($errno);
        $msg         = isset($messageInfo['errorSoftTxt']) ? $messageInfo['errorSoftTxt'] : $errno;
        $data        = array(
                'data'   => 'error',
                'info'   => $msg,
                'status' => $errno,
        );

        return $data;
    }

    /**
     * Ajax方式返回数据到客户端.
     *
     * @param mixed  $data 要返回的数据
     * @param string $type AJAX返回数据格式
     */
    protected function ajaxReturn($data, $type = '', $status = null)
    {
        if (method_exists($this, 'beforeAjaxReturn')) {
            $this->beforeAjaxReturn($data, $type, $status);
        }

        if (func_num_args() == 1 && is_numeric($data) && $data < 0) {
            $data = $this->buildAjaxErrorReturnData($data);
            parent::ajaxReturn($data, $type);
        } elseif (func_num_args() == 3) {
            // 3.0的方式
            $args   = func_get_args();
            $data   = $args[0];
            $info   = $args[1];
            $status = $args[2];
            parent::ajaxReturn($data, $info, $status);
        } else {
            parent::ajaxReturn($data, $type);
        }
    }

}