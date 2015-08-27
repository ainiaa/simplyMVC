<?php

!defined('MIN_PHP_VERSION') && define('MIN_PHP_VERSION', '5.3.0');//定义PHP最小版本

if (version_compare(PHP_VERSION, MIN_PHP_VERSION, '<')) { //判断php的版本是不是为php5  php5.3.0之后才有匿名函数
    die('require PHP VERSION >= ' . MIN_PHP_VERSION);
} else if (version_compare(PHP_VERSION, '5.4.0', '<')) {
    ini_set('magic_quotes_runtime', 0);
    define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc() ? true : false);
} else {
    define('MAGIC_QUOTES_GPC', false);
}

!defined('CORE_PATH') && define('CORE_PATH', dirname(__DIR__));

!defined('CONF_PATH') && define('CONF_PATH', dirname(CORE_PATH) . '/config'); //config路径
!defined('INCLUDE_PATH') && define('INCLUDE_PATH', dirname(CORE_PATH) . '/include'); //include目录的地址
!defined('PEAR_PATH') && define('PEAR_PATH', INCLUDE_PATH . '/lib/pear'); //include目录的地址

//  版本信息
!defined('SMVC_VERSION') && define('SMVC_VERSION', '0.3.0');

!defined('IS_CGI') && define('IS_CGI', substr(PHP_SAPI, 0, 3) == 'cgi' ? 1 : 0);
!defined('IS_WIN') && define('IS_WIN', strstr(PHP_OS, 'WIN') ? 1 : 0);
!defined('IS_CLI') && define('IS_CLI', PHP_SAPI == 'cli' ? 1 : 0);

!defined('MEMORY_LIMIT_ON') && define('MEMORY_LIMIT_ON', function_exists('memory_get_usage'));

// 项目名称
!defined('APP_NAME') && define('APP_NAME', basename(dirname($_SERVER['SCRIPT_FILENAME'])));

!defined('VENDOR_PATH') && define('VENDOR_PATH', dirname(CORE_PATH) . '/include/vendor/');

!defined('VAR_AJAX_SUBMIT') && define('VAR_AJAX_SUBMIT', 'isAjax');

// 定义当前请求的系统常量
!defined('NOW_TIME') && define('NOW_TIME', $_SERVER['REQUEST_TIME']);
!defined('REQUEST_METHOD') && define('REQUEST_METHOD', $_SERVER['REQUEST_METHOD']);
!defined('IS_GET') && define('IS_GET', REQUEST_METHOD == 'GET' ? true : false);
!defined('IS_POST') && define('IS_POST', REQUEST_METHOD == 'POST' ? true : false);
!defined('IS_PUT') && define('IS_PUT', REQUEST_METHOD == 'PUT' ? true : false);
!defined('IS_DELETE') && define('IS_DELETE', REQUEST_METHOD == 'DELETE' ? true : false);
!defined('IS_AJAX') && define('IS_AJAX', SimpleMVC::isAjax());

!defined('USE_ALLINONE_CACHE') && define('USE_ALLINONE_CACHE', false);//是否使用文件缓存
