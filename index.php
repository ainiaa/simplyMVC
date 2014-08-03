<?php
define('ROOT_PATH', dirname(__FILE__));

!defined('APP_PATH') or define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']) . '/');

define('CONF_PATH', ROOT_PATH . '/config'); //config路径
define('CORE_PATH', ROOT_PATH . '/core'); //core路径
define('INCLUDE_PATH', ROOT_PATH . '/include'); //include目录的地址
define('PEAR_PATH', INCLUDE_PATH . '/lib/pear'); //include目录的地址
include './core/simpleMVC.php';