<?php
if (!empty($_GET['_debug_']) && $_GET['_debug_'] == 'show_debug') {
    define('APP_DEBUG', true);
} else { //生产环境 APP_DEBUG 设置为 false
    define('APP_DEBUG', false);
}

define('CURRENT_HOST', 'http://local.smvc.me/');
define('BASE_DOMAIN', 'smvc.me');