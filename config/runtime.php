<?php
$currentTime      = time();
$currentMicroTime = microtime(true);
return [
        'DS'                       => DIRECTORY_SEPARATOR,//目录分隔符
        'smvcDebug'                => true,//是否为debug模式
        'debugMode'                => 1,//1:直接信息输出简单的调试信息,2:将简单的调试信息写入文件,3:直接输出复杂的调试信息,4:将复杂的调试信息写入文件
        'site_url'                 => '',//网站url
        'FRONTEND_CONTROLLER_PATH' => ROOT_DIR . 'frontend',
        'BACKEND_CONTROLLER_PATH'  => ROOT_DIR . 'backend',
        'cookieDomain'             => '',//COOKIE 域名
        'autoLoadPath'             => [//类自动加载目录 多个目录之间使用逗号分割
                CORE_DIR,
                CORE_DIR . 'cache',
                CORE_DIR . 'i18n',
                CORE_DIR . 'helper',
                CORE_DIR . 'session',
                INCLUDE_DIR . 'vendor'
        ],
        'defaultLocal'             => 'en_us',//默认语言
        'routerFilterMode'         => 'blacklist',//开启 路由过滤 whiteList:开启白名单, blacklist:开启黑名单, none:不过滤
        'routerFilterWhiteList'    => [],//需要 routerFilterMode 设置为 whiteList 可用
        'routerFilterBlackList'    => [
                ['controller' => 'page', 'action' => '*'],
                ['controller' => '*', 'action' => 'test'],
                ['controller' => 'cc', 'action' => 'ca'],
        ],//需要 routerFilterMode 设置为 blacklist 可用

        'DEFAULT_CACHE_TIME'  => 86400,//默认缓存时间
        'SECOND_30'           => 30,//30秒
        'MINUTE_1'            => 60,//1分钟
        'MINUTE_5'            => 300,//5分钟
        'MINUTE_30'           => 1800,//30分钟
        'HOUR_1'              => 3600,//1小时
        'HOUR_10'             => 50400,//10小时
        'DAY_1'               => 86400,//1天
        'WORKEND_1'           => 604800,//1周
        'CURRENT_TIMESTAMP'   => $currentTime,//当前时间戳
        'CURRENT_TIME_YMD'    => date('Y-m-d', $currentTime),//Y-m-d方式的时间
        'CURRENT_TIME_YMDHIS' => date('Y-m-d H:i:s', $currentTime),//Y-m-d H:i:s方式的时间
        'DEFAULT_URL'         => 'index.php',//默认访问页面
];