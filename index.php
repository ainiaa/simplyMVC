<?php
$rootDir = dirname(__FILE__);
$rootDir = str_replace('\\', '/', $rootDir);
define('ROOT_DIR', $rootDir . '/');

define('APP_DIR', dirname($_SERVER['SCRIPT_FILENAME']) . '/');
define('CORE_DIR', ROOT_DIR . 'core/');//framework所在路径
define('CONF_DIR', ROOT_DIR . 'config/'); //config路径

include CORE_DIR . 'simpleMVC.php';