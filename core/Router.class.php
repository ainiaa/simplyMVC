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

    /**
     * @param mixed  $key
     * @param string $default
     *
     * @return string
     */
    public static function getPost($key = '', $default = '')
    {
        if (func_num_args() === 0) {
            return $_POST;
        }
        return SmvcArrayHelper::get($_POST, $key, $default);
    }


    /**
     * @param mixed  $key
     * @param string $default
     *
     * @return string
     */
    public static function getGet($key = '', $default = '')
    {
        if (func_num_args() === 0) {
            return $_GET;
        }
        return SmvcArrayHelper::get($_GET, $key, $default);
    }

    /**
     * @param mixed  $key
     * @param string $default
     *
     * @return string
     */
    public static function getRequest($key = '', $default = '')
    {
        if (func_num_args() === 0) {
            return $_REQUEST;
        }
        return SmvcArrayHelper::get($_REQUEST, $key, $default);
    }


    /**
     *
     * @param mixed  $key
     * @param string $default
     *
     * @return string
     */
    public static function getHeader($key = '', $default = '')
    {
        static $headers = null;

        // do we need to fetch the headers?
        if ($headers === null) {
            // deal with fcgi or nginx installs
            if (!function_exists('getallheaders')) {
                $server = SmvcArrayHelper::filterPrefixed(self::getServer(), 'HTTP_', true);

                foreach ($server as $k => $value) {
                    $k = join('-', array_map('ucfirst', explode('_', strtolower($k))));

                    $headers[$k] = $value;
                }

                $value = self::getServer(
                        'Content_Type',
                        self::getServer('Content-Type')
                ) and $headers['Content-Type'] = $value;
                $value = self::getServer(
                        'Content_Length',
                        self::getServer('Content-Length')
                ) and $headers['Content-Length'] = $value;
            } else {
                $headers = getallheaders();
            }
        }

        return empty($headers) ? $default : ((func_num_args() === 0) ? $headers : SmvcArrayHelper::get(
                $headers,
                $key,
                $default
        ));
    }

    /**
     * @param        $key
     * @param string $default
     *
     * @return string
     */
    public static function getServer($key = '', $default = '')
    {
        if (func_num_args() === 0) {
            return $_SERVER;
        }
        return SmvcArrayHelper::get($_SERVER, $key, $default);
    }


    /**
     * Get the public ip address of the user.
     *
     * @param string $default
     *
     * @return  string
     */
    public static function ip($default = '0.0.0.0')
    {
        return self::getServer('REMOTE_ADDR', $default);
    }


    public static function getCookie($key = '', $default = '')
    {
        if (func_num_args() === 0) {
            return $_COOKIE;
        }
        return SmvcArrayHelper::get($_COOKIE, $key, $default);
    }

    /**
     * @param $default
     *
     * @return string
     */
    public static function getUserAgent($default = '')
    {
        return self::getServer('HTTP_USER_AGENT', $default);
    }


    /**
     * Get the real ip address of the user.  Even if they are using a proxy.
     *
     * @param    string $default          the default to return on failure
     * @param    bool   $exclude_reserved exclude private and reserved IPs
     *
     * @return  string  the real ip address of the user
     */
    public static function realIp($default = '0.0.0.0', $exclude_reserved = false)
    {
        static $server_keys = null;

        if (empty($server_keys)) {
            $server_keys = array('HTTP_CLIENT_IP', 'REMOTE_ADDR');
            if (C('security.allow_x_headers', false)) {
                $server_keys = array_merge(array('HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_X_FORWARDED_FOR'), $server_keys);
            }
        }

        foreach ($server_keys as $key) {
            if (!self::getServer($key)) {
                continue;
            }

            $ips = explode(',', self::getServer($key));
            array_walk(
                    $ips,
                    function (&$ip) {
                        $ip = trim($ip);
                    }
            );

            $ips = array_filter(
                    $ips,
                    function ($ip) use ($exclude_reserved) {
                        return filter_var(
                                $ip,
                                FILTER_VALIDATE_IP,
                                $exclude_reserved ? FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE : null
                        );
                    }
            );

            if ($ips) {
                return reset($ips);
            }
        }

        return SimpleMVC::value($default);
    }

    /**
     * Return's the protocol that the request was made with
     *
     * @return  string
     */
    public static function protocol()
    {
        if (self::getServer('HTTPS') == 'on' or self::getServer('HTTPS') == 1 or self::getServer(
                        'SERVER_PORT'
                ) == 443 or (C('security.allow_x_headers', false) and self::getServer(
                                'HTTP_X_FORWARDED_PROTO'
                        ) == 'https') or (C('security.allow_x_headers', false) and self::getServer(
                                'HTTP_X_FORWARDED_PORT'
                        ) == 443)
        ) {
            return 'https';
        }

        return 'http';
    }

}