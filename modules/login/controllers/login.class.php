<?php

/**
 * Admin
 * @author Jeff.Liu<jeff.liu.guo@gmail.com>
 * @date   2017/05/27
 */
class LoginController extends ModulesController
{

    /**
     * 登录
     * @author Jeff Liu
     */
    public function loginAction()
    {
        $userInfo = Session::instance()->get('userInfo');
        if ($userInfo) {
            $url = $this->getRedirectUrl('backurl');
            $this->redirect($url);
        } else {
            if (IS_POST) {
                $userName  = $_POST['userName'];
                $password  = $_POST['password'];
                $adminInfo = $this->AdminService->getAdminInfoByUserName($userName);
                if ($adminInfo) {
                    $md5Password = md5($password);
                    if ($md5Password == $adminInfo['password']) { //登录成功
                        Session::instance()->set('userInfo', ['userName' => $userName, 'password' => md5($password)]);
                        $url = $this->getRedirectUrl('backurl');
                        header('Location:' . $url);
                        return;
                    } else {
                        echo 1;exit;
                    }
                }
                $this->assign('loginError', '用户名或者密码错误');
            }

            $this->display('login.tpl.html');
        }

    }

    /**
     * @return mixed|string
     */
    public function getRedirectUrl($key, $withReferer = true, $cleanCache = false)
    {
        $url = I($key);
        if (empty($url)) {
            $url = Session::instance()->get($key);
            if ($cleanCache) {
                Session::instance()->set($key, null);
            }
        }
        if (empty($url) && $withReferer) {
            $url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        }

        if (empty($url)) {
            $url = C('DEFAULT_URL');
        }

        return $url;
    }

    /**
     *
     */
    public function logoutAction()
    {
        Session::instance()->delete('userInfo');
        $this->display('login.tpl.html');
    }
}
