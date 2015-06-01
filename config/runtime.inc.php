<?php

return array(
    //目录分隔符
        'DS'                       => DIRECTORY_SEPARATOR,
    //是否为debug模式
        'smvcDebug'                => true,
    //1=>直接信息输出简单的调试信息,2=>将简单的调试信息写入文件,3=>直接输出复杂的调试信息,4=>将复杂的调试信息写入文件
        'debugMode'                => 1,
    //网站url
        'site_url'                 => '',
        'FRONTEND_CONTROLLER_PATH' => ROOT_PATH . '/frontend',
        'BACKEND_CONTROLLER_PATH'  => ROOT_PATH . '/backend',
    //COOKIE 域名
        'cookieDomain'             => '',
    //类自动加载目录 多个目录之间使用逗号分割
        'autoLoadPath'             => CORE_PATH . ',' . CORE_PATH . '/cache/' . ',' . CORE_PATH . '/i18n/' . ',' . CORE_PATH . '/helper/' . ',' . CORE_PATH . '/session/',
        'defaultLocal'             => 'en_us',//默认语言

    //开启 路由过滤
        'routerFilterMode'         => 'blacklist',//whiteList :开启白名单, blacklist:开启黑名单, none:不过滤
        'routerFilterWhiteList'    => array(),//需要 routerFilterMode 设置为 whiteList 可用
        'routerFilterBlackList'    => array(
                array('controller' => 'page', 'action' => '*'),
                array('controller' => '*', 'action' => 'test'),
                array('controller' => 'cc', 'action' => 'ca'),
        ),//需要 routerFilterMode 设置为 blacklist 可用

        'useAllInOneCache'         => true,//是否使用文件缓存 填写 true 或者 false
);
