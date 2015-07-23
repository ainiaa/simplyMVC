<?php

class BackendController extends BaseController
{

    function __construct()
    {
        parent::__construct();

        $sessionInfo = Session::instance()->get('userInfo');
        var_export($sessionInfo);
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
//        echo '$_SERVER:',var_export($_SERVER,1);
        if (IS_POST) {
            $userName = $_POST['username'];
            $password = $_POST['password'];
            if ($userName == 'jeff' && $password == '111111') {
                Session::instance()->set('userInfo', array('userName' => $userName, 'password' => md5($password)));
                $sessionInfo = Session::instance()->get('userInfo');
                echo '$sessionInfo:';
                var_dump($sessionInfo);exit;
                header('Location:/?debug=1&b=2&m=default&c=default&g=backend&a=index');
            } else {//重新登录
                $this->display('login.tpl.html');
            }
        } else {
            $this->display('login.tpl.html');
        }
    }

} 