<?php

define('DS', DIRECTORY_SEPARATOR);
define('PUBLIC_DIR', __DIR__ . DS);
define('ROOT_DIR', dirname(PUBLIC_DIR) . DS);
define('API_DIR', ROOT_DIR . 'api' . DS);
define('INCLUDE_DIR', ROOT_DIR . 'include' . DS);
define('CORE_DIR', ROOT_DIR . 'core' . DS);//framework path
define('CONF_DIR', ROOT_DIR . 'config' . DS); //config path

include CORE_DIR . 'simpleMVC.php';