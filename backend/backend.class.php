<?php

class BackendController extends BaseController
{

    function __construct()
    {
        parent::__construct();

        $sessionInfo = Session::instance()->get('userInfo');
        if (empty($sessionInfo) && $_GET['action'] != 'login') {
            echo '后台必须先登录!';
        }
    }

    public function run()
    {

    }

    public function login()
    {

    }

} 