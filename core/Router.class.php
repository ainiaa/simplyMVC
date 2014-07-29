<?php

/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 13-1-31
 * Time: 下午11:48
 * To change this template use File | Settings | File Templates.
 */
class Router
{

    public static $group;

    public static $module;

    public static $controller;

    public static $action;

    /**
     * 除去魔术
     */
    public static function removeMagicQuotes()
    {
        if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
            $_POST    = self::stripSlashesDeep($_POST);
            $_GET     = self::stripSlashesDeep($_GET);
            $_COOKIE  = self::stripSlashesDeep($_COOKIE);
            $_REQUEST = self::stripSlashesDeep($_REQUEST);
        }
    }

    public static function stripSlashesDeep($value)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                if (is_array($v)) {
                    $value[$k] = self::stripSlashesDeep($v);
                } else {
                    $value[$k] = stripslashes($v);
                }
            }
        } else {
            $value = stripslashes($value);
        }
        return $value;
    }

    /**
     * URL组装 支持不同URL模式
     *
     * @param array $info
     *
     * @return string
     */
    public static function buildUrl($info)
    {
        return '';
    }

    /**
     * 解析url
     * TODO
     * 目前还是使用的tp的代码 还需要整理
     */
    public static function parseUrl()
    {
        self::removeMagicQuotes();
    }

    /**
     * 路由检测
     * TODO
     * @access public
     * @return boolean
     */
    static public function routerCheck()
    {
        $return = false;
        return $return;
    }

    /**
     * 获得实际的分组名称
     * @access private
     *
     * @param array $info
     *
     * @return string
     */
    public static function getGroup($info = array())
    {
        if ($info) {
            self::$group = isset($info['group']) ? $info['group'] : C('defaultGroup', 'frontend');
        } else {
            if (empty(self::$group)) {
                self::$group = C('defaultGroup', 'frontend');
            }
        }
        return self::$group;
    }

    /**
     * 获得实际的模块名称
     * @access private
     *
     * @param array $info
     *
     * @return string
     */
    public static function getModule($info = array())
    {
        if ($info) {
            self::$module = isset($info['module']) ? $info['module'] : C('defaultModule', 'default');
        } else {
            if (empty(self::$module)) {
                self::$module = C('defaultModule', 'default');
            }
        }
        return self::$module;
    }


    /**
     * 获得实际的控制器名称
     * @access public
     *
     * @param array $info
     *
     * @return string
     */
    public static function getController($info = array())
    {
        if ($info) {
            self::$controller = isset($info['controller']) ? $info['controller'] : C('defaultController', 'default');
        } else {
            if (empty(self::$controller)) {
                self::$controller = C('defaultController', 'default');
            }
        }
        return self::$controller . C('controllerSuffix');
    }

    /**
     * 获得实际的操作名称
     * @access public
     *
     * @param array $info
     *
     * @return string
     */
    public static function getAction($info = array())
    {
        if ($info) {
            self::$action = isset($info['action']) ? $info['action'] : C('defaultAction', 'index');
        } else {
            if (empty(self::$action)) {
                self::$action = C('defaultAction', 'index');
            }
        }
        return self::$action . C('actionSuffix');
    }


    /**
     * TODO
     *
     * @param $var
     */
    public static function getParams($info = array())
    {
    }


    /**
     * 根据服务器环境不同，取得RequestUri信息
     *
     * @return array
     */
    public static function  getRequestUri()
    {
        if (isset($_SERVER['HTTP_X_REWRITE_URL'])) { // check this first so IIS will catch
            $requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
        } else {
            if (isset($_SERVER['REQUEST_URI'])) {
                $requestUri = $_SERVER['REQUEST_URI'];
            } else {
                if (isset($_SERVER['ORIG_PATH_INFO'])) { // IIS 5.0, PHP as CGI
                    $requestUri = $_SERVER['ORIG_PATH_INFO'];
                    if (!empty($_SERVER['QUERY_STRING'])) {
                        $requestUri .= '?' . $_SERVER['QUERY_STRING'];
                    }
                } else {
                    $requestUri = null;
                }
            }
        }
        return $requestUri;
    }
}