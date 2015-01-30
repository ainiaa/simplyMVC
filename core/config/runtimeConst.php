<?php

if (version_compare(PHP_VERSION, '5.2.0', '<')) { //判断php的版本是不是为php5
    die('require PHP > 5.2.0 !');
} else if (version_compare(PHP_VERSION, '5.4.0', '<')) {
    ini_set('magic_quotes_runtime', 0);
    define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc() ? true : false);
} else {
    define('MAGIC_QUOTES_GPC', false);
}

!defined('CORE_PATH') && define('CORE_PATH', __DIR__);

//  版本信息
!defined('SMVC_VERSION') && define('SMVC_VERSION', '0.1.0');

!defined('IS_CGI') && define('IS_CGI', substr(PHP_SAPI, 0, 3) == 'cgi' ? 1 : 0);
!defined('IS_WIN') && define('IS_WIN', strstr(PHP_OS, 'WIN') ? 1 : 0);
!defined('IS_CLI') && define('IS_CLI', PHP_SAPI == 'cli' ? 1 : 0);

!defined('MEMORY_LIMIT_ON') && define('MEMORY_LIMIT_ON', function_exists('memory_get_usage'));

// 项目名称
!defined('APP_NAME') && define('APP_NAME', basename(dirname($_SERVER['SCRIPT_FILENAME'])));

!defined('SMVC_VERSION') && define('VENDOR_PATH', dirname(CORE_PATH) . '/include/vendor/');

!defined('VAR_AJAX_SUBMIT') && define('VAR_AJAX_SUBMIT', 'isAjax');