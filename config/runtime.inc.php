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
);
