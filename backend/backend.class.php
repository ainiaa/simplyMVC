<?php

class BackendController extends BaseController
{

    function __construct()
    {
        parent::__construct();

        $sessionInfo = Session::instance()->get('userInfo');
        if (empty($sessionInfo) && $_GET['action'] != 'login') {
            echo '��̨�����ȵ�¼!';
        }
    }

    public function run()
    {

    }

    public function login()
    {

    }

} 