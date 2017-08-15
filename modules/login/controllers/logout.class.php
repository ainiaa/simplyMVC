<?php

/**
 * Login module done
 * @author Jeff.Liu<jeff.liu.guo@gmail.com>
 * @date   2017/05/27
 */
class LogoutController extends ModulesController
{

    public function logoutAction()
    {
        session('userInfo', null);
        $this->display('logout.tpl.html');
    }
}
