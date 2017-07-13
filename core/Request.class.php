<?php

/**
 * Class Request
 */
class Request
{

    private static $instance = null;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * @return Request
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Request();
        }
        return self::$instance;
    }

    /**
     * @param      $origin
     * @param      $key
     * @param null $defaultValue
     *
     * @return array|null
     */
    private static function getParamBase($origin, $key, $defaultValue = null)
    {
        if ($key == '*') {
            return $origin;
        } else if (is_array($key)) {
            $final = [];
            foreach ($key as $k) {
                $final[$k] = isset($origin[$k]) ? $origin[$k] : $defaultValue;
            }
            return $final;
        } else {
            return isset($origin[$key]) ? $origin[$key] : $defaultValue;
        }
    }

    /**
     * @param      $type
     * @param      $key
     * @param null $defaultValue
     * @param null $callback
     *
     * @return mixed
     */
    public static function getParam($type, $key, $defaultValue = null, $callback = null)
    {
        switch (strtolower($type)) {
            case 'get':
                $return = self::getParamBase($_GET, $key, $defaultValue);
                break;
            case 'post':
                $return = self::getParamBase($_POST, $key, $defaultValue);
                break;
            case 'request':
                $return = self::getParamBase($_REQUEST, $key, $defaultValue);
                break;
            case 'cookie':
                $return = self::getParamBase($_COOKIE, $key, $defaultValue);
                break;
            case 'server':
                $return = self::getParamBase($_SERVER, $key, $defaultValue);
                break;
            case 'session':
                $return = self::getParamBase($_SESSION, $key, $defaultValue);
                break;
            case 'env':
                $return = self::getParamBase($_ENV, $key, $defaultValue);
                break;
            default:
                $return = null;
        }
        if ($callback && is_callable($callback)) {
            return $callback($return);
        }
        return $return;
    }

    /**
     * @param $origin
     * @param $key
     * @param $value
     */
    private static function setParamBase(&$origin, $key, $value)
    {
        if (is_array($key) && is_null($value)) {
            array_merge($origin, $key);
        } else if (is_array($key) && !is_null($value)) {
            foreach ($key as $k) {
                $origin[$k] = $value;
            }
        } else {
            $origin[$key] = $value;
        }
    }

    /**
     * @param      $type
     * @param      $key
     * @param null $value
     */
    public static function setParam($type, $key, $value = null)
    {
        switch (strtolower($type)) {
            case 'get':
                self::setParamBase($_GET, $key, $value);
                break;
            case 'post':
                self::setParamBase($_POST, $key, $value);
                break;
            case 'request':
                self::setParamBase($_REQUEST, $key, $value);
                break;
            case 'cookie':
                self::setParamBase($_COOKIE, $key, $value);
                break;
            case 'server':
                self::setParamBase($_SERVER, $key, $value);
                break;
            case 'session':
                self::setParamBase($_SESSION, $key, $value);
                break;
            case 'env':
                self::setParamBase($_ENV, $key, $value);
                break;
            default:
                self::setParamBase($_REQUEST, $key, $value);
        }
    }

    /**
     * @param string $key
     * @param null   $default
     * @param null   $callback
     *
     * @return mixed
     */
    public static function getGet($key = '*', $default = null, $callback = null)
    {
        return self::getParam('get', $key, $default, $callback);
    }

    /**
     * @param string $key
     * @param null   $default
     * @param null   $callback
     *
     * @return mixed
     */
    public static function getPost($key = '*', $default = null, $callback = null)
    {
        return self::getParam('post', $key, $default, $callback);
    }

    /**
     * @param string $key
     * @param null   $default
     * @param null   $callback
     *
     * @return mixed
     */
    public static function getRequest($key = '*', $default = null, $callback = null)
    {
        return self::getParam('request', $key, $default, $callback);
    }

    /**
     * @param string $key
     * @param null   $default
     * @param null   $callback
     *
     * @return mixed
     */
    public static function getCookie($key = '*', $default = null, $callback = null)
    {
        return self::getParam('cookie', $key, $default, $callback);
    }

    /**
     * @param string $key
     * @param null   $default
     * @param null   $callback
     *
     * @return mixed
     */
    public static function getServer($key = '*', $default = null, $callback = null)
    {
        return self::getParam('server', $key, $default, $callback);
    }

    /**
     * @param string $key
     * @param null   $default
     * @param null   $callback
     *
     * @return mixed
     */
    public static function getEnv($key = '*', $default = null, $callback = null)
    {
        return self::getParam('env', $key, $default, $callback);
    }


    /**
     * @param string $key
     * @param null   $value
     *
     */
    public static function setGet($key, $value = null)
    {
        self::setParam('get', $key, $value);
    }

    /**
     * @param string $key
     * @param null   $value
     *
     */
    public static function setPost($key, $value = null)
    {
        self::setParam('post', $key, $value);
    }

    /**
     * @param string $key
     * @param null   $value
     *
     */
    public static function setRequest($key, $value = null)
    {
        self::setParam('request', $key, $value);
    }

    /**
     * @param string $key
     * @param null   $value
     *
     */
    public static function setCookie($key, $value = null)
    {
        self::setParam('cookie', $key, $value);
    }

    /**
     * @param string $key
     * @param null   $value
     */
    public static function setServer($key, $value = null)
    {
        self::setParam('server', $key, $value);
    }

    /**
     * @param string $key
     * @param null   $value
     */
    public static function setEnv($key, $value = null)
    {
        self::setParam('env', $key, $value);
    }



    public static function value($value)
    {
        if ($value instanceof Closure) {
            return $value();
        } else {
            return $value;
        }
    }

    /**
     * @return array
     */
    public static function parseRequest()
    {
        $group      = self::parseGroup();
        $module     = self::parseModule();
        $controller = self::parseController();
        $action     = self::parseAction();
        return ['group' => $group, 'module' => $module, 'controller' => $controller, 'action' => $action];
    }

    public static function parseGroup()
    {
        if (empty(self::$group)) {
            $groupParamName = C('groupParamName', 'g');
            if (isset($_REQUEST[$groupParamName])) {
                self::$group = $_REQUEST[$groupParamName];
            } else {
                self::$group = C('defaultGroup', 'frontend');
            }
            self::$originGroup = self::$group;
        }
        return self::$group;
    }

    /**
     * 初始化module
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     */
    public static function parseModule()
    {
        if (empty(self::$module)) {
            $moduleParamName = C('moduleParamName', 'm');
            if (isset($_REQUEST[$moduleParamName])) {
                self::$module = $_REQUEST[$moduleParamName];
            } else {
                self::$module = C('defaultModule', 'default');
            }
            self::$originModule = self::$module;
        }

        return self::$module;
    }

    /**
     * 初始化action
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     */
    public static function parseController()
    {
        if (empty(self::$controller)) {
            $controllerParamName = C('controllerParamName', 'c');
            if (isset($_REQUEST[$controllerParamName])) {
                self::$controller = $_REQUEST[$controllerParamName];
            } else {
                self::$controller = C('defaultController', 'default');
            }
            self::$originController = self::$controller;
        }

        return self::$controller;
    }

    /**
     * @return mixed|string
     */
    public static function parseAction()
    {
        if (empty(self::$action)) {
            $actionParamName = C('actionParamName', 'a');

            if (isset($_REQUEST[$actionParamName])) {
                self::$action = $_REQUEST[$actionParamName];
            } else {
                self::$action = C('defaultAction', 'index');
            }
            self::$originAction = self::$action;
        }

        return self::$action;
    }

    /**
     * 获得实际的分组名称
     * @return string
     */
    public static function getGroup($default = null)
    {
        if (self::$group) {
            $groupName = self::$group;
        } else {
            $groupName = $default;
        }
        return $groupName;
    }


    /**
     * 获得实际的模块名称
     * @access private
     *
     * @param null $default
     *
     * @return string
     */
    public static function getModule($default = null)
    {
        if (self::$module) {
            $moduleName = self::$module;
        } else {
            $moduleName = $default;
        }
        return $moduleName;
    }


    /**
     * 获得实际的控制器名称
     *
     * @param bool $appendSuffer
     * @param null $default
     *
     * @return string
     */
    public static function getControllerName($appendSuffer = true, $default = null)
    {
        if (self::$controller) {
            $controllerName = self::$controller;
        } else {
            $controllerName = $default;
        }
        if ($appendSuffer) {
            $controllerName .= C('controllerSuffix');
        }
        return $controllerName;
    }

    /**
     * 获得实际的操作名称
     * @access public
     *
     * @param bool $appendSuffer
     * @param null $defalut
     *
     * @return string
     */
    public static function getActionName($appendSuffer = true, $defalut = null)
    {
        if (self::$action) {
            $actionName = self::$action;
        } else {
            $actionName = $defalut;
        }

        if ($appendSuffer) {
            $actionName .= C('actionSuffix');
        }

        return $actionName;
    }


    /**
     * @param array $info
     *
     * @return mixed
     */
    public static function getParams($info = [])
    {
        return SmvcArrayHelper::get($info, 'uri_params', []);
    }


    /**
     * 根据服务器环境不同，取得RequestUri信息
     *
     * @return array
     */
    public static function getQueryString()
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

                $value = self::getServer('Content_Type', self::getServer('Content-Type')
                ) and $headers['Content-Type'] = $value;
                $value = self::getServer('Content_Length', self::getServer('Content-Length')
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
    public static function getClientIp($default = '0.0.0.0', $exclude_reserved = false)
    {
        static $server_keys = null;

        if (empty($server_keys)) {
            $server_keys = ['HTTP_CLIENT_IP', 'REMOTE_ADDR'];
            if (C('security.allow_x_headers', false)) {
                $server_keys = array_merge(
                        ['HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR'],
                        $server_keys
                );
            }
        }
        $clientIp = '';

        foreach ($server_keys as $key) {
            $clientIp = self::getEnv($key);
            if (empty($clientIp)) {
                continue;
            } else {
                break;
            }
        }

        if (empty($clientIp)) {
            foreach ($server_keys as $key) {
                $clientIp = self::getServer($key);
                if (empty($clientIp)) {
                    continue;
                } else {
                    break;
                }
            }
        }

        $clientIp = trim($clientIp);
        if ($clientIp) {
            return filter_var(
                    $clientIp,
                    FILTER_VALIDATE_IP,
                    $exclude_reserved ? FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE : null
            );
        } else {
            return $default;
        }
    }

    /**
     * Return's the protocol that the request was made with
     *
     * @return  string
     */
    public static function getProtocol()
    {
        $isHttpsOn              = self::getServer('HTTPS') == 'on';
        $isHttps                = self::getServer('HTTPS') == 1;
        $is443                  = self::getServer('SERVER_PORT') == 443;
        $allow_x_headers        = C('security.allow_x_headers', false);
        $HTTP_X_FORWARDED_PORT  = self::getServer('HTTP_X_FORWARDED_PORT') == 443;
        $HTTP_X_FORWARDED_PROTO = self::getServer('HTTP_X_FORWARDED_PROTO') == 'https';
        if ($isHttpsOn || $isHttps || $is443 || ($allow_x_headers && $HTTP_X_FORWARDED_PROTO) or ($allow_x_headers && $HTTP_X_FORWARDED_PORT)) {
            return 'https';
        }

        return 'http';
    }

    public static function mappingUri($finalUri)
    {
        list($finalGroup, $finalModule, $finalController, $finalAction) = each(explode('/', $finalUri));
        self::mappingGroup($finalGroup);
        self::mappingModule($finalModule);
        self::mappingController($finalController);
        self::mappingAction($finalAction);
    }

    /**
     *
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     *
     * @param string $finalGroup
     */
    public static function mappingGroup($finalGroup = '')
    {
        if ($finalGroup) {
            $groupParamName = C('groupParamName', 'g');
            if (isset($_REQUEST[$groupParamName])) {
                $_REQUEST[$groupParamName] = $finalGroup;
            }
            if (isset($_GET[$groupParamName])) {
                $_GET[$groupParamName] = $finalGroup;
            }
            if (isset($_POST[$groupParamName])) {
                $_POST[$groupParamName] = $finalGroup;
            }
            if (isset($_COOKIE[$groupParamName])) {
                $_COOKIE[$groupParamName] = $finalGroup;
            }
            self::$group = $finalGroup;
        }
    }

    /**
     *
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     *
     * @param string $finalModule
     */
    public static function mappingModule($finalModule = '')
    {
        if ($finalModule) {
            $moduleParamName = C('moduleParamName', 'm');
            if (isset($_REQUEST[$moduleParamName])) {
                $_REQUEST[$moduleParamName] = $finalModule;
            }
            if (isset($_GET[$moduleParamName])) {
                $_GET[$moduleParamName] = $finalModule;
            }
            if (isset($_POST[$moduleParamName])) {
                $_POST[$moduleParamName] = $finalModule;
            }
            if (isset($_COOKIE[$moduleParamName])) {
                $_COOKIE[$moduleParamName] = $finalModule;
            }
            self::$module = $finalModule;
        }
    }

    /**
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     *
     * @param string $finalController
     */
    public static function mappingController($finalController = '')
    {
        if ($finalController) {
            $controllerParamName = C('controllerParamName', 'c');
            if (isset($_REQUEST[$controllerParamName])) {
                $_REQUEST[$controllerParamName] = $finalController;
            }
            if (isset($_GET[$controllerParamName])) {
                $_GET[$controllerParamName] = $finalController;
            }
            if (isset($_POST[$controllerParamName])) {
                $_POST[$controllerParamName] = $finalController;
            }
            if (isset($_COOKIE[$controllerParamName])) {
                $_COOKIE[$controllerParamName] = $finalController;
            }
            self::$controller = $finalController;
        }
    }

    /**
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     *
     * @param string $finalAction
     */
    public static function mappingAction($finalAction = '')
    {
        if ($finalAction) {
            $actionParamName = C('actionParamName', 'a');
            if (isset($_REQUEST[$actionParamName])) {
                $_REQUEST[$actionParamName] = $finalAction;
            }
            if (isset($_GET[$actionParamName])) {
                $_GET[$actionParamName] = $finalAction;
            }
            if (isset($_POST[$actionParamName])) {
                $_POST[$actionParamName] = $finalAction;
            }
            if (isset($_COOKIE[$actionParamName])) {
                $_COOKIE[$actionParamName] = $finalAction;
            }
            self::$action = $finalAction;
        }
    }

    /**
     * @return string
     */
    public static function getOriginGroup()
    {
        return self::$originGroup;
    }

    /**
     * @param string $originGroup
     */
    public static function setOriginGroup($originGroup)
    {
        self::$originGroup = $originGroup;
    }

    /**
     * @return string
     */
    public static function getOriginModule()
    {
        return self::$originModule;
    }

    /**
     * @param string $originModule
     */
    public static function setOriginModule($originModule)
    {
        self::$originModule = $originModule;
    }

    /**
     * @return string
     */
    public static function getOriginController()
    {
        return self::$originController;
    }

    /**
     * @param string $originController
     */
    public static function setOriginController($originController)
    {
        self::$originController = $originController;
    }

    /**
     * @return string
     */
    public static function getOriginAction()
    {
        return self::$originAction;
    }

    /**
     * @param string $originAction
     */
    public static function setOriginAction($originAction)
    {
        self::$originAction = $originAction;
    }

    /**
     * 站内跳转 (不会重新发起一次新的请求.而是根据url 重新解析url，然后再次dispatch)
     *
     * @param $url
     */
    public static function redirect($url)
    {
        header('Location:' . $url);
        exit;
    }

    /**
     * 站内跳转 (不会重新发起一次新的请求.而是根据url 重新解析url，然后再次dispatch)
     *
     * @param $url
     */
    public static function forward($url)
    {
        parse_str($url, $info);
        $_GET     = array_merge($_GET, $info);
        $_REQUEST = array_merge($_REQUEST, $info);

        //解析url
        Router::route();

        Dispatcher::dispatch();
    }

    /**
     *
     */
    public static function getCurrentUrl()
    {
        $urlWithPortFormat    = '%s://%s%s';
        $urlWithoutPortFormat = '%s://%s:%s%s';
        $serverPort           = self::getServer('SERVER_PORT', '80');
        $serverName           = self::getServer('SERVER_NAME');
        $requestUri           = self::getServer('REQUEST_URI');
        $protocol             = self::getProtocol();
        if ($serverPort == '80' || $serverPort == '443') {
            $url = sprintf($urlWithPortFormat, $protocol, $serverName, $requestUri);
        } else {
            $url = sprintf($urlWithoutPortFormat, $protocol, $serverName, $serverPort, $requestUri);
        }
        return $url;
    }

    public function getMethod()
    {
        return self::getServer('REQUEST_METHOD', 'GET');
    }


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
     * @var string 原始moudle
     */
    private static $originModule;

    /**
     * @var string 控制器名称
     */
    private static $controller;

    /**
     * @var string 原始控制器
     */
    private static $originController;

    /**
     * @var string action名称
     */
    private static $action;

    /**
     * @var string 原始action
     */
    private static $originAction;

}