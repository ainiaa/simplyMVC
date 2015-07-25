<?php

class BackendController extends BaseController
{

    public function __construct()
    {
        parent::__construct();

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
            $userName = $_POST['username'];
            $password = $_POST['password'];
            if ($userName == 'jeff' && $password == '111111') {
                Session::instance()->set('userInfo', array('userName' => $userName, 'password' => md5($password)));
                header('Location:/?debug=1&b=2&m=default&c=default&g=backend&a=index');
            } else {//重新登录
                $this->display('login.tpl.html');
            }
        } else {
            $this->display('login.tpl.html');
        }
    }

} 