<?php

return [
    //    'APP_SUB_DOMAIN_DEPLOY' => true, //开启子域名部署
    //    'APP_SUB_DOMAIN_RULES' => array( // '子域名'=>array('项目[/分组]');
    //    ),
        'URL_PATHINFO_DEPR'    => '-', //参数分隔符
        'VAR_ACTION'           => 'a', //action在url中对应的key
        'VAR_MODULE'           => 'm', //module在url中对应的key
        'VAR_GROUP'            => 'g', //group在url中对应的key
        'VAR_CONTROLLER'       => 'c', //controller在url中对应的key
        'URL_MODULE_MAP'       => '', //？？？
        'URL_CASE_INSENSITIVE' => false, //URL是否区分大小写
        'APP_GROUP_LIST'       => 'frontend,backend,modules', //所有的分组列表
        'VAR_PATHINFO'         => 'r', //URL里面兼容模式参数
        'URL_HTML_SUFFIX'      => '.html', //rewrite模式下 html后缀
        'VAR_URL_PARAMS'       => '_URL_', //在 $_GET中保持整个url的key
        'URL_ACTION_MAP'       => '', //？？？
        'DEFAULT_ACTION'       => 'index', //默认ACTION
        'DEFAULT_GROUP'        => 'frontend', //默认分组
        'DEFAULT_MODULE'       => 'default', //默认module
        'DEFAULT_CONTROLLER'   => 'default', //默认controller
        'APP_GROUP_MODE'       => 1, //启用独立分组
        'urlMode'              => 1, //1=>兼容性的url mode,2=>path_info的url mode,3=>url rewrite的mode url
];