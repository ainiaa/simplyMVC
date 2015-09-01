<?php

class BackendController extends BaseController
{

    /**
     * @var AdminService
     */
    public $AdminService;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 执行初始化操作
     * @author Jeff Liu
     */
    protected function _initialize()
    {
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