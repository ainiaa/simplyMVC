<?php
if (!function_exists('C')) {
    function C($key = null, $default = false)
    {
        return SmvcConf::getInstance()->get($key, $default);
    }
}

if (!function_exists('SC')) {
    function SC($key = null, $value = false)
    {
        return SmvcConf::getInstance()->set($key, $value);
    }
}


if (!function_exists('LCL')) {
    /**
     * @param        $configFilePath
     * @param string $configFileExt
     * @param bool   $excludEnvFile
     */
    function LCL($configFilePath, $configFileExt = 'php', $excludEnvFile = true)
    {
        SmvcConf::getInstance()->loadConfigFileList($configFilePath, $configFileExt, $excludEnvFile);
    }
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
 * @param bool $absolute
 *
 * @return string
 */
function make_url($uri_path, $uri_params, $absolute = false)
{
    $final_url    = '';
    $uri_path_arr = explode('/', $uri_path);
    $final_url    .= sprintf(
            'index.php?g=%s&m=%s&c=%s&a=%s',
            $uri_path_arr[0],
            $uri_path_arr[1],
            $uri_path_arr[2],
            $uri_path_arr[3]
    );
    $final_url    .= '&' . http_build_query($uri_params);
    if ($absolute) {
        $final_url = get_domain() . $final_url;
    }
    return $final_url;
}

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