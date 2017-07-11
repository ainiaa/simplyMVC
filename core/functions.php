<?php

function C($key = null, $default = false)
{
    return SmvcConf::getInstance()->get($key, $default);
}


function SC($key = null, $value = false)
{
    return SmvcConf::getInstance()->set($key, $value);
}


/**
 * @param        $configFilePath
 * @param string $configFileExt
 * @param bool   $excludEnvFile
 */
function LCL($configFilePath, $configFileExt = 'php', $excludEnvFile = true)
{
    SmvcConf::getInstance()->loadConfigFileList($configFilePath, $configFileExt, $excludEnvFile);
}

/**
 * 字符串命名风格转换
 * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
 *
 * @param string  $name 字符串
 * @param integer $type 转换类型
 *
 * @return string
 */
function parse_name($name, $type = 0)
{
    if ($type) {
        return ucfirst(
                preg_replace_callback(
                        '/_([a-zA-Z])/',
                        function ($match) {
                            return strtoupper($match[1]);
                        },
                        $name
                )
        );
    } else {
        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }
}

/**
 * 区分大小写的文件存在判断
 *
 * @param string $filename 文件地址
 *
 * @return boolean
 */
function file_exists_case($filename)
{
    if (is_file($filename)) {
        if (IS_WIN && C('APP_FILE_CASE')) {
            if (basename(realpath($filename)) != basename($filename)) {
                return false;
            }
        }

        return true;
    }

    return false;
}

/**
 * XML编码
 *
 * @param mixed  $data     数据
 * @param string $root     根节点名
 * @param string $item     数字索引的子节点名
 * @param string $attr     根节点属性
 * @param string $id       数字索引子节点key转换的属性名
 * @param string $encoding 数据编码
 *
 * @return string
 */
function xml_encode($data, $root = 'think', $item = 'item', $attr = '', $id = 'id', $encoding = 'utf-8')
{
    if (is_array($attr)) {
        $_attr = [];
        foreach ($attr as $key => $value) {
            $_attr[] = "{$key}=\"{$value}\"";
        }
        $attr = implode(' ', $_attr);
    }
    $attr = trim($attr);
    $attr = empty($attr) ? '' : " {$attr}";
    $xml  = "<?xml version=\"1.0\" encoding=\"{$encoding}\"?>";
    $xml  .= "<{$root}{$attr}>";
    $xml  .= data_to_xml($data, $item, $id);
    $xml  .= "</{$root}>";

    return $xml;
}

/**
 * 数据XML编码
 *
 * @param mixed  $data 数据
 * @param string $item 数字索引时的节点名称
 * @param string $id   数字索引key转换为的属性名
 *
 * @return string
 */
function data_to_xml($data, $item = 'item', $id = 'id')
{
    $xml = $attr = '';
    foreach ($data as $key => $val) {
        if (is_numeric($key)) {
            $id && $attr = " {$id}=\"{$key}\"";
            $key = $item;
        }
        $xml .= "<{$key}{$attr}>";
        $xml .= (is_array($val) || is_object($val)) ? data_to_xml($val, $item, $id) : $val;
        $xml .= "</{$key}>";
    }

    return $xml;
}

// 不区分大小写的in_array实现
function in_array_case($value, $array)
{
    return in_array(strtolower($value), array_map('strtolower', $array));
}


/**
 * 从文件或数组中定义常量
 *
 * @author Jeff Liu
 *
 * @param     mixed $source
 *
 * @return    mixed
 */
function smvc_define($source)
{
    static $defined = [];
    if (is_string($source)) { //导入数组
        $source = include($source);
    }
    if (!is_array($source)) { //不是数组，无法定义
        return false;
    }

    foreach ($source as $key => $value) {
        $finalKey = strtoupper($key);
        if (!isset($defined[$finalKey])) {
            if (is_scalar($value)) {
                define($finalKey, $value);
                $defined[$finalKey] = 1;
            }
        }
    }

    return true;
}


/**
 * 获得当前的域名
 *
 * @return  string
 */
function get_domain()
{
    /* 协议 */
    $protocol = (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) ? 'https://' : 'http://';

    $host = '';
    /* 域名或IP地址 */
    if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
    } elseif (isset($_SERVER['HTTP_HOST'])) {
        $host = $_SERVER['HTTP_HOST'];
    } else {
        /* 端口 */
        if (isset($_SERVER['SERVER_PORT'])) {
            $port = ':' . $_SERVER['SERVER_PORT'];

            if ((':80' == $port && 'http://' == $protocol) || (':443' == $port && 'https://' == $protocol)) {
                $port = '';
            }
        } else {
            $port = '';
        }

        if (isset($_SERVER['SERVER_NAME'])) {
            $host = $_SERVER['SERVER_NAME'] . $port;
        } elseif (isset($_SERVER['SERVER_ADDR'])) {
            $host = $_SERVER['SERVER_ADDR'] . $port;
        }
    }

    return $protocol . $host;
}

/**
 * 获得网站的URL地址
 *
 * @return  string
 */
function site_url()
{
    return get_domain() . substr(PHP_SELF, 0, strrpos(PHP_SELF, '/'));
}


/**
 * 添加和获取页面Trace记录
 *
 * @param string  $value  变量
 * @param string  $label  标签
 * @param string  $level  日志级别
 * @param boolean $record 是否记录日志
 *
 * @return mixed
 */
function trace($value = '[think]', $label = '', $level = 'DEBUG', $record = false)
{
    static $_trace = [];
    if ('[think]' === $value) { // 获取trace信息
        return $_trace;
    } else {
        $info = ($label ? $label . ':' : '') . print_r($value, true);
        if ('ERR' == $level && C('TRACE_EXCEPTION')) {// 抛出异常
            new Exception($info);
        }
        $level = strtoupper($level);
        if (!isset($_trace[$level])) {
            $_trace[$level] = [];
        }
        $_trace[$level][] = $info;
        if ((defined('IS_AJAX') && IS_AJAX) || !C('SHOW_PAGE_TRACE') || $record) {
            Log::record($info, $level, $record);
        }
    }

    return true;
}

/**
 * @param      $uri_path
 * @param      $uri_params
 * @param bool $with_domain
 *
 * @return string
 */
function make_url($uri_path = '', $uri_params = [], $with_domain = true)
{
    $final_url    = '';
    if (empty($uri_path)) {
        if ($with_domain) {
            return Request::getCurrentUrl();
        } else {
            return Request::getServer('REQUEST_URI');
        }
    } else {
        $uri_path     = explode('/', $uri_path);
        $uri_path     = array_reverse($uri_path);
    }

    $default_path = [
            'action'     => Request::getActionName(false),
            'controller' => Request::getControllerName(false),
            'module'     => Request::getModule(),
            'group'      => Request::getGroup(),
    ];
    $keys = array_keys($default_path);
    for ($i = count($uri_path);$i<count($default_path);$i++) {
        $uri_path[$i] = $default_path[$keys[$i]];
    }
    $uri_path     = array_combine(['action', 'controller', 'module', 'group'], $uri_path);
    $final_url    .= sprintf(
            'index.php?g=%s&m=%s&c=%s&a=%s',
            $uri_path['group'],
            $uri_path['module'],
            $uri_path['controller'],
            $uri_path['action']
    );
    if ($uri_params) {
        $final_url .= '&' . http_build_query($uri_params);
    }

    if ($with_domain) {
        $final_url = generateUrlByDomainAndRequestUri(get_domain(),$final_url);
    }
    return $final_url;
}

/**
 * PS 在配置项 urlMapping 中配置使用
 * @param $domain
 * @param $url
 *
 * @return string
 */
function generateUrlByDomainAndRequestUri($domain, $url)
{
    if($domain) {
        $domain = (is_ssl()?'https://':'http://') . str_replace(['http://','https://'], '', $domain);
        $domainLatestLetter = substr($domain, -1);
        $urlFirstLetter     = substr($url, 0, 1);
        if ($domainLatestLetter == '/' && $urlFirstLetter == '/') {
            $url = $domain . substr($url, 1);
        } else if ($domainLatestLetter == '/' || $urlFirstLetter == '/') {
            $url = $domain . $url;
        } else {
            $url = $domain .'/'. $url;
        }
    }

    return $url;
}

/**
 * 判断是否SSL协议
 * @return boolean
 */
function is_ssl() {
    if(isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))){
        return true;
    }elseif(isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'] )) {
        return true;
    }
    return false;
}

/**
 * @param      $name
 * @param null $defaultValue
 * @param null $callback
 *
 * @return mixed
 */
function I($name, $defaultValue = null, $callback = null)
{
    $name = explode('.', $name);
    if (count($name) > 1) {
        $type = array_shift($name);
        $key  = implode('.', $name);
    } else {
        $type = 'request';
        $key  = $name[0];
    }
    return Request::getParam($type, $key, $defaultValue, $callback);
}

/**
 * URL重定向
 *
 * @param string  $url  重定向的URL地址
 * @param integer $time 重定向的等待时间（秒）
 * @param string  $msg  重定向前的提示信息
 *
 * @return void
 */
function redirect($url, $time = 0, $msg = '')
{
    //多行URL地址支持
    $url = str_replace(array("\n", "\r"), '', $url);
    if (empty($msg)) {
        $msg = "系统将在{$time}秒之后自动跳转到{$url}！";
    }
    if (!headers_sent()) {
        // redirect
        if (0 === $time) {
            header('Location: ' . $url);
        } else {
            header("refresh:{$time};url={$url}");
            echo($msg);
        }
        exit();
    } else {
        $str = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
        if ($time != 0) {
            $str .= $msg;
        }
        exit($str);
    }
}

/**
 * 获取和设置语言定义(不区分大小写)
 *
 * @param string|array $name  语言变量
 * @param string       $value 语言值
 *
 * @return mixed
 */
function L($name = null, $value = null)
{
    static $_lang = array();
    // 空参数返回所有定义
    if (empty($name)) {
        return $_lang;
    }
    // 判断语言获取(或设置)
    // 若不存在,直接返回全大写$name
    if (is_string($name)) {
        $name = strtoupper($name);
        if (is_null($value)) {
            return isset($_lang[$name]) ? $_lang[$name] : $name;
        }
        $_lang[$name] = $value; // 语言定义
        return;
    }
    // 批量定义
    if (is_array($name)) {
        $_lang = array_merge($_lang, array_change_key_case($name, CASE_UPPER));
    }
    return;
}

/**
 * 错误输出
 *
 * @param mixed $error 错误
 *
 * @return void
 */
function halt($error)
{
    $e = array();
    if (defined('APP_DEBUG') && APP_DEBUG) {
        //调试模式下输出错误信息
        if (!is_array($error)) {
            $trace        = debug_backtrace();
            $e['message'] = $error;
            $e['file']    = $trace[0]['file'];
            $e['line']    = $trace[0]['line'];
            ob_start();
            debug_print_backtrace();
            $e['trace'] = ob_get_clean();
        } else {
            $e = $error;
        }
    } else {
        //否则定向到错误页面
        $error_page = C('ERROR_PAGE');
        if (!empty($error_page)) {
            redirect($error_page);
        } else {
            if (C('SHOW_ERROR_MSG')) {
                $e['message'] = is_array($error) ? $error['message'] : $error;
            } else {
                $e['message'] = C('ERROR_MESSAGE');
            }
        }
    }
    // 包含异常页面模板
    var_export($error);
    exit;
    include C('TMPL_EXCEPTION_FILE');
    exit;
}

/**
 * 自定义异常处理
 *
 * @param string  $msg  异常消息
 * @param string  $type 异常类型 默认为ThinkException
 * @param integer $code 异常代码 默认为0
 *
 * @return void
 */
function throw_exception($msg, $type = 'Exception', $code = 0)
{
    if (class_exists($type, false)) {
        throw new $type($msg, $code);
    } else {
        halt($msg);// 异常类型不存在则输出错误信息字串
    }
}

/**
 *
 */
function get_current_url()
{
    $REQUEST_SCHEME = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http';
    $HTTP_HOST      = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    $SERVER_PORT    = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : '';
    $REQUEST_URI    = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    if ($SERVER_PORT == 80) {
        $url = sprintf('%s://%s%s', $REQUEST_SCHEME, $HTTP_HOST, $REQUEST_URI);
    } else {
        $url = sprintf('%s://%s:%s%s', $REQUEST_SCHEME, $HTTP_HOST, $SERVER_PORT, $REQUEST_URI);
    }
    return $url;
}

/**
 * @param        $params
 * @param string $label
 * @param string $fileName
 * @param string $filePath
 */
function file_debug($params, $label = '', $fileName = '', $filePath = '')
{
    if (empty($filePath)) {
        if (defined('PHP_OS') && strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $filePath = '/tmp/';
        } else {
            $filePath = 'd:/';
        }
    }

    if (empty($fileName)) {
        if (empty($label)) {
            $fileName = 'file_debug.log';
        } else {
            $fileName = $label;
        }
    }

    $file = $filePath . $fileName;
    if (!is_scalar($params)) {
        $params = var_export($params, 1);
    }
    error_log('[date:]' . date('Y-m-d H:i:s') . '|' . $label . ':' . $params . PHP_EOL, 3, $file);
}

function triggerBehaviorWithResult($name, &$params = null)
{
    if (strpos($name, '/')) {
        list($name, $method) = explode('/', $name);
    } else {
        $method = 'run';
    }
    $class    = $name . 'Behavior';
    $behavior = new $class();
    $return   = $behavior->$method($params);
    return $return;
}

function BR($name, &$params = null)
{
    return triggerBehaviorWithResult($name, $params);
}

/**
 * @param      $tag
 * @param null $params
 *
 * @return array|bool
 */
function tagWithReturn($tag, &$params = null)
{
    // 系统标签扩展
    $extends = C('extends.' . $tag);
    // 应用标签扩展
    $tags = C('tags.' . $tag);
    if (!empty($tags)) {
        if (empty($tags['_overlay']) && !empty($extends)) { // 合并扩展
            $tags = array_unique(array_merge($extends, $tags));
        } elseif (isset($tags['_overlay'])) { // 通过设置 '_overlay'=>1 覆盖系统标签
            unset($tags['_overlay']);
        }
    } elseif (!empty($extends)) {
        $tags = $extends;
    }
    if ($tags) {
        $return = [];
        // 执行扩展
        foreach ($tags as $key => $name) {
            if (!is_int($key)) { // 指定行为类的完整路径 用于模式扩展
                $name = $key;
            }
            $return[$name] = BR($name, $params);
        }
        return $return;
    } else { // 未执行任何行为 返回false
        return false;
    }
}

/**
 * 执行某个行为
 *
 * @param string $name   行为名称
 * @param Mixed  $params 传入的参数
 *
 * @return void
 */
function B($name, &$params = null)
{
    if (strpos($name, '/')) {
        list($name, $method) = explode('/', $name);
    } else {
        $method = 'run';
    }
    $class    = $name . 'Behavior';
    $behavior = new $class();
    $behavior->$method($params);
}

/**
 * 处理标签扩展
 *
 * @param string $tag    标签名称
 * @param mixed  $params 传入参数
 *
 * @return mixed
 */
function tag($tag, &$params = null)
{
    // 系统标签扩展
    $extends = C('extends.' . $tag);
    // 应用标签扩展
    $tags = C('tags.' . $tag);
    if (!empty($tags)) {
        if (empty($tags['_overlay']) && !empty($extends)) { // 合并扩展
            $tags = array_unique(array_merge($extends, $tags));
        } elseif (isset($tags['_overlay'])) { // 通过设置 '_overlay'=>1 覆盖系统标签
            unset($tags['_overlay']);
        }
    } elseif (!empty($extends)) {
        $tags = $extends;
    }
    if ($tags) {
        // 执行扩展
        foreach ($tags as $key => $name) {
            if (!is_int($key)) { // 指定行为类的完整路径 用于模式扩展
                $name = $key;
            }
            B($name, $params);
        }
    } else { // 未执行任何行为 返回false
        return false;
    }
}

/**
 * session 操作
 *
 * @param        $name
 * @param string $value
 *
 * @return mixed|SmvcSessionInterface
 */
function session($name = '', $value = '')
{
    if ($name === '') { //获取所有的session
        return Session::getInstance()->get();
    } else if (is_null($name)) {//清空所有session
        return Session::getInstance()->deleteAll();
    } else if ($value === '') {//获取单个session
        return Session::getInstance()->get($name);
    } else if (is_null($value)) { //删除单个session
        return Session::getInstance()->delete($name);
    } else if ($name === '[start]'){
        return Session::getInstance()->start();
    } else if ($name === '[pause]'){
        return Session::getInstance()->pause();
    }else if ($name === '[stop]'){
        return Session::getInstance()->stop();
    }else{ //设置session
        return Session::getInstance()->set($name, $value);
    }
}


if (!function_exists('com_create_guid')) {
    function com_create_guid()
    {
        return sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff)
        );
    }
}