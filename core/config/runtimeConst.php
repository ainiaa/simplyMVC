<?php

!defined('MIN_PHP_VERSION') && define('MIN_PHP_VERSION', '5.4.0');//定义PHP最小版本

if (version_compare(PHP_VERSION, MIN_PHP_VERSION, '<')) { //判断php的版本是不是为php5  php5.3.0之后才有匿名函数
    die('require PHP VERSION >= ' . MIN_PHP_VERSION);
} else if (version_compare(PHP_VERSION, MIN_PHP_VERSION, '<')) {
    ini_set('magic_quotes_runtime', 0);
    define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc() ? true : false);
} else {
    define('MAGIC_QUOTES_GPC', false);
}

!defined('CORE_DIR') && define('CORE_DIR', dirname(__DIR__) . '/');//framework所在路径
!defined('CONF_DIR') && define('CONF_DIR', dirname(CORE_DIR) . '/config/'); //config路径
!defined('INCLUDE_DIR') && define('INCLUDE_DIR', dirname(CORE_DIR) . '/include/'); //include目录的地址
!defined('PEAR_DIR') && define('PEAR_DIR', INCLUDE_DIR . '/lib/pear/'); //include目录的地址

//  版本信息
!defined('SMVC_VERSION') && define('SMVC_VERSION', '0.4.0');

!defined('IS_CGI') && define('IS_CGI', substr(PHP_SAPI, 0, 3) == 'cgi' ? 1 : 0);
!defined('IS_WIN') && define('IS_WIN', strstr(PHP_OS, 'WIN') ? 1 : 0);
!defined('IS_CLI') && define('IS_CLI', PHP_SAPI == 'cli' ? 1 : 0);

!defined('MEMORY_LIMIT_ON') && define('MEMORY_LIMIT_ON', function_exists('memory_get_usage'));

// 项目名称
!defined('APP_NAME') && define('APP_NAME', basename(dirname($_SERVER['SCRIPT_FILENAME'])));

!defined('VENDOR_DIR') && define('VENDOR_DIR', dirname(CORE_DIR) . '/include/vendor/');

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

define('PHP_SELF', htmlentities(isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']));
define('SHARE_TEMP_PATH', ROOT_DIR . 'temp/');
define('SHARE_DATA_PATH', ROOT_DIR . 'data/');
