<?php

/**
 * Login module done
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
        $userInfo = session('userInfo');
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
                        session('userInfo', ['userName' => $userName, 'password' => md5($password)]);
                        $url = $this->getRedirectUrl('backurl');
                        if (stripos($url, 'logout') !== 0) {
                            Request::redirect($url);
                        } else {
                            Request::redirect(make_url('modules/category/category/index'));
                        }

                    } else {
                        echo 1;exit;
                    }
                }
                $this->assign('loginError', '用户名或者密码错误');
            }

            $this->display();
        }

    }

    /**
     * @return mixed|string
     */
    public function getRedirectUrl($key, $withReferer = true, $cleanCache = false)
    {
        $url = I($key);
        if (empty($url)) {
            $url = session($key);
            if ($cleanCache) {
                session($key, null);
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
}
