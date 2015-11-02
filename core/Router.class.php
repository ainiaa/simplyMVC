<?php

/**
 * Class Router
 */
class Router
{

    /**
     * @var string 分组名称
     */
    private static $group;

    /**
     * @var string 原始分组
     */
    private static $originGroup;

    /**
     * @var string module 名称
     */
    private static $module;

    /**
     * @var string 控制器名称
     */
    private static $controller;

    /**
     * @var string action名称
     */
    private static $action;

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

    /**
     * @author Jeff Liu
     */
    public static function doUrlMapping()
    {
        $urlMappingConf = C('URL_MAPPING', array());
        if ($urlMappingConf) {
            foreach ($urlMappingConf as $pattern => $mapping) {
                list($group, $module, $controller, $action) = each(explode('/', $pattern));
                list($mappingGroup, $mappingModule, $mappingController, $mappingAction) = each(explode('/', $pattern));
                $originGroup = self::initGroup();
                if ($group == '*' || $group == $originGroup) {
                    self::mappingGroup($mappingGroup);
                    $originModule = self::initModule();
                    if ($module == '*' || $module == $originModule) {
                        self::mappingModule($mappingModule);
                        $originController = self::initController();
                        if ($controller == '*' || $controller == $originController) {
                            self::mappingController($mappingController);
                            $originAction = self::initAction();
                            if ($action == '*' || $action == $originAction) {
                                self::mappingAction($mappingAction);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $value
     *
     * @return array|string
     */
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
     *
     * 目前还是使用的tp的代码 还需要整理
     */
    public static function parseUrl()
    {
        self::removeMagicQuotes();
        self::initEnv();
    }


    /**
     * 路由检测
     * @author Jeff Liu
     * @access public
     * @return boolean
     */
    public static function routerCheck()
    {
        $controller = self::getControllerName(null, false);
        $action     = self::getActionName(null, false);
        if (C('routerFilterMode', 'none') === 'whiteList') {//白名单
            $return = self::routerCheckByWhiteList($controller, $action);
        } else if (C('routerFilterMode', 'none') === 'blacklist') {//黑名单
            $return = self::routerCheckByBlackList($controller, $action);
        } else {
            $return = true;
        }

        return $return;
    }


    /**
     * router检验白名单
     *
     * @param $controller
     * @param $action
     *
     * @return bool
     */
    public static function routerCheckByWhiteList($controller, $action)
    {
        //只有 请求的route 在白名单中才可以执行
        $whiteList = C('routerFilterWhiteList', array());
        $return    = false;
        if ($whiteList) {
            if ('*.*' === $whiteList) {
                $return = true;
            } else {
                foreach ($whiteList as $currentRoute) {
                    $currentRouteController = isset($currentRoute['controller']) ? $currentRoute['controller'] : '*';
                    $currentRouteAction     = isset($currentRoute['action']) ? $currentRoute['action'] : '*';
                    if ($currentRouteController === '*' && $currentRouteAction === '*') {
                        $return = true;
                        break;
                    } else if ($currentRouteController === '*' && $action === $currentRouteAction) {
                        $return = true;
                        break;
                    } else if ($currentRouteAction === '*' && $controller === $currentRouteController) {
                        $return = true;
                        break;
                    } else if ($currentRouteController === $controller && $action === $currentRouteAction) {
                        $return = true;
                        break;
                    }
                }
            }
        }

        return $return;
    }


    /**
     * router检验黑名单
     *
     * @param $controller
     * @param $action
     *
     * @return bool
     */
    public static function routerCheckByBlackList($controller, $action)
    {
        //只有 请求的route 不在黑名单中才可以执行
        $blacklist = C('routerFilterBlackList', array());
        $return    = true;
        if ($blacklist) {
            if ('*.*' === $blacklist) {
                $return = false;
            } else {
                foreach ($blacklist as $currentRoute) {
                    $currentRouteController = isset($currentRoute['controller']) ? $currentRoute['controller'] : '*';
                    $currentRouteAction     = isset($currentRoute['action']) ? $currentRoute['action'] : '*';
                    if ($currentRouteController === '*' && $currentRouteAction === '*') {
                        $return = false;
                        break;
                    } else if ($currentRouteController === '*' && $action === $currentRouteAction) {
                        $return = false;
                        break;
                    } else if ($currentRouteAction === '*' && $controller === $currentRouteController) {
                        $return = false;
                        break;
                    } else if ($currentRouteController === $controller && $action === $currentRouteAction) {
                        $return = false;
                        break;
                    }
                }
            }
        }

        return $return;
    }

    /**
     * 初始化环境变量
     * @author Jeff Liu
     */
    public static function initEnv()
    {
        self::doUrlMapping();

        self::initGroup();
        self::initModule();
        self::initController();
        self::initAction();
    }

    /**
     * 初始化分组
     * @author Jeff Liu
     */
    private static function initGroup()
    {
        if (empty(self::$group)) {
            $groupParamName = C('groupParamName', 'g');
            if (isset($_REQUEST[$groupParamName])) {
                self::$group = $_REQUEST[$groupParamName];
            } else {
                self::$group = C('defaultGroup', 'frontend');
            }
        }

        return self::$group;
    }

    /**
     *
     * @author Jeff Liu
     *
     * @param string $module
     */
    private static function mappingModule($module = '')
    {
        if ($module) {
            $moduleParamName = C('moduleParamName', 'm');
            if (isset($_REQUEST[$moduleParamName])) {
                $_REQUEST[$moduleParamName] = $module;
            }
            if (isset($_GET[$moduleParamName])) {
                $_GET[$moduleParamName] = $module;
            }
            if (isset($_POST[$moduleParamName])) {
                $_POST[$moduleParamName] = $module;
            }
            if (isset($_COOKIE[$moduleParamName])) {
                $_COOKIE[$moduleParamName] = $module;
            }
            self::$module = $module;
        }
    }

    /**
     *
     * @author Jeff Liu
     *
     * @param string $group
     */
    private static function mappingGroup($group = '')
    {
        if ($group) {
            $groupParamName = C('groupParamName', 'g');
            if (isset($_REQUEST[$groupParamName])) {
                $_REQUEST[$groupParamName] = $group;
            }
            if (isset($_GET[$groupParamName])) {
                $_GET[$groupParamName] = $group;
            }
            if (isset($_POST[$groupParamName])) {
                $_POST[$groupParamName] = $group;
            }
            if (isset($_COOKIE[$groupParamName])) {
                $_COOKIE[$groupParamName] = $group;
            }
            self::$group = $group;
        }
    }

    /**
     * @author Jeff Liu
     *
     * @param string $controller
     */
    private static function mappingController($controller = '')
    {
        if ($controller) {
            $controllerParamName = C('controllerParamName', 'c');
            if (isset($_REQUEST[$controllerParamName])) {
                $_REQUEST[$controllerParamName] = $controller;
            }
            if (isset($_GET[$controllerParamName])) {
                $_GET[$controllerParamName] = $controller;
            }
            if (isset($_POST[$controllerParamName])) {
                $_POST[$controllerParamName] = $controller;
            }
            if (isset($_COOKIE[$controllerParamName])) {
                $_COOKIE[$controllerParamName] = $controller;
            }
            self::$controller = $controller;
        }
    }

    /**
     * @author Jeff Liu
     *
     * @param string $action
     */
    private static function mappingAction($action = '')
    {
        if ($action) {
            $actionParamName = C('actionParamName', 'a');
            if (isset($_REQUEST[$actionParamName])) {
                $_REQUEST[$actionParamName] = $action;
            }
            if (isset($_GET[$actionParamName])) {
                $_GET[$actionParamName] = $action;
            }
            if (isset($_POST[$actionParamName])) {
                $_POST[$actionParamName] = $action;
            }
            if (isset($_COOKIE[$actionParamName])) {
                $_COOKIE[$actionParamName] = $action;
            }
            self::$action = $action;
        }
    }

    /**
     * 初始化module
     * @author Jeff Liu
     */
    private static function initModule()
    {
        if (empty(self::$module)) {
            $moduleParamName = C('moduleParamName', 'm');
            if (isset($_REQUEST[$moduleParamName])) {
                self::$module = $_REQUEST[$moduleParamName];
            } else {
                self::$module = C('defaultModule', 'default');
            }
        }

        return self::$module;
    }

    /**
     * 初始化action
     * @author Jeff Liu
     */
    private static function initController()
    {
        if (empty(self::$controller)) {
            $controllerParamName = C('controllerParamName', 'c');

            if (isset($_REQUEST[$controllerParamName])) {
                self::$controller = $_REQUEST[$controllerParamName];
            } else {
                self::$controller = C('defaultController', 'default');
            }
        }

        return self::$controller;
    }

    private static function initAction()
    {
        if (empty(self::$action)) {
            $actionParamName = C('actionParamName', 'a');

            if (isset($_REQUEST[$actionParamName])) {
                self::$action = $_REQUEST[$actionParamName];
            } else {
                self::$action = C('defaultAction', 'index');
            }
        }

        return self::$action;
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
        /*
        $groupParamName = C('groupParamName', 'g');
        if ($info) {
            self::$group = isset($info[$groupParamName]) ? $info[$groupParamName] : C('defaultGroup', 'frontend');
        } else {
            if (empty(self::$group)) {
                self::$group = C('defaultGroup', 'frontend');
            }
        }
        */
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
        /*
        $moduleParamName = C('moduleParamName', 'm');
        if ($info) {
            self::$module = isset($info[$moduleParamName]) ? $info[$moduleParamName] : C('defaultModule', 'default');
        } else {
            if (empty(self::$module)) {
                self::$module = C('defaultModule', 'default');
            }
        }
        */
        return self::$module;
    }


    /**
     * 获得实际的控制器名称
     * @access public
     *
     * @param array $info
     *
     * @param bool  $appendSuffer
     *
     * @return string
     */
    public static function getControllerName($info = array(), $appendSuffer = true)
    {
        /*
        if (empty(self::$controller)) {
            $controllerParamName = C('controllerParamName', 'c');
            if ($info) {
                self::$controller = isset($info[$controllerParamName]) ? $info[$controllerParamName] : C('defaultController',
                        'default');
            } else {
                if (isset($_GET[$controllerParamName])) {
                    $controllerName = $_GET[$controllerParamName];
                } else if (isset($_POST[$controllerParamName])) {
                    $controllerName = $_POST[$controllerParamName];
                } else if (isset($_COOKIE[$controllerParamName])) {
                    $controllerName = $_COOKIE[$controllerParamName];
                } else {
                    $controllerName = C('defaultController', 'default');
                }
                self::$controller = $controllerName;
            }
        }
        */
        if ($appendSuffer) {
            $controllerName = self::$controller . C('controllerSuffix');
        } else {
            $controllerName = self::$controller;
        }

        return $controllerName;
    }

    /**
     * 获得实际的操作名称
     * @access public
     *
     * @param array $info
     *
     * @return string
     */
    public static function getActionName($info = array(), $appendSuffer = true)
    {
        /*
        if (empty(self::$action)) {
            $actionParamName = C('actionParamName', 'a');
            if ($info) {
                self::$action = isset($info[$actionParamName]) ? $info[$actionParamName] : C('defaultAction', 'index');
            } else {
                if (isset($_GET[$actionParamName])) {
                    $actionName = $_GET[$actionParamName];
                } else if (isset($_POST[$actionParamName])) {
                    $actionName = $_POST[$actionParamName];
                } else if (isset($_COOKIE[$actionParamName])) {
                    $actionName = $_COOKIE[$actionParamName];
                } else {
                    $actionName = C('defaultAction', 'default');
                }
                self::$action = $actionName;
            }
        }
        */
        if ($appendSuffer) {
            $actionName = self::$action . C('actionSuffix');
        } else {
            $actionName = self::$action;
        }

        return $actionName;
    }


    /**
     * TODO
     *
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
        if ($headers === null) {
            if (!function_exists('getallheaders')) {
                $server = SmvcArrayHelper::filterPrefixed(self::getServer(), 'HTTP_', true);

                foreach ($server as $k => $value) {
                    $k = join('-', array_map('ucfirst', explode('_', strtolower($k))));

                    $headers[$k] = $value;
                }

                $value = self::getServer('Content_Type',
                        self::getServer('Content-Type')) and $headers['Content-Type'] = $value;
                $value = self::getServer('Content_Length',
                        self::getServer('Content-Length')) and $headers['Content-Length'] = $value;
            } else {
                $headers = getallheaders();
            }
        }

        return empty($headers) ? $default : ((func_num_args() === 0) ? $headers : SmvcArrayHelper::get($headers, $key,
                $default));
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
    public static function getRemoteIp($default = '0.0.0.0')
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
    public static function clientIp($default = '0.0.0.0', $exclude_reserved = false)
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
            array_walk($ips, function (&$ip) {
                $ip = trim($ip);
            });

            $ips = array_filter($ips, function ($ip) use ($exclude_reserved) {
                return filter_var($ip, FILTER_VALIDATE_IP,
                        $exclude_reserved ? FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE : null);
            });

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
    public static function getProtocol()
    {
        if (self::getServer('HTTPS') == 'on' or self::getServer('HTTPS') == 1 or self::getServer('SERVER_PORT') == 443 or (C('security.allow_x_headers',
                                false) and self::getServer('HTTP_X_FORWARDED_PROTO') == 'https') or (C('security.allow_x_headers',
                                false) and self::getServer('HTTP_X_FORWARDED_PORT') == 443)
        ) {
            return 'https';
        }

        return 'http';
    }
}