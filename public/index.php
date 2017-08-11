<?php
define('DS', DIRECTORY_SEPARATOR);
define('PUBLIC_DIR', __DIR__ . DS);
define('ROOT_DIR', realpath(__DIR__ . '/../') . DS);
define('APP_DIR', ROOT_DIR);
define('CORE_DIR', ROOT_DIR . 'core' . DS);//framework所在路径
define('CONF_DIR', ROOT_DIR . 'config' . DS); //config路径

include CORE_DIR . 'simpleMVC.php';