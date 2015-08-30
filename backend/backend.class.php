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

        $this->AdminService = new AdminService();

        $sessionInfo = Session::instance()->get('userInfo');
        if (empty($sessionInfo) && $_GET['a'] != 'login') {
            $this->loginAction();
            exit;
        }

    }

    public function run()
    {

    }

    public function loginAction()
    {
        if (IS_POST) {
            $userName  = $_POST['username'];
            $password  = $_POST['password'];
            $adminInfo = $this->AdminService->getAdminInfoByUserName($userName);

            if ($adminInfo) {
                $md5Password = md5($password);
                if ($md5Password == $adminInfo['password']) { //登录成功
                    Session::instance()->set('userInfo', array('userName' => $userName, 'password' => md5($password)));
                    header('Location:/?debug=1&b=2&m=default&c=default&g=backend&a=index');
                }
            }
            $this->assign('loginError', '用户名或者密码错误');
        }

        $this->display('login.tpl.html');
    }

} 