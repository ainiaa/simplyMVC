<?php

return array(
        'extName'        => 'php',
        'pathMode'       => '',
        'appGroupPath'   => '',
        'defaultGroup'   => 'frontend',
        'defaultModule'  => 'default',
        'defaultController'  => 'default',
        'defaultAction'  => 'index',
        'viewEngine'     => 'Smarty', //视图engine
        'viewEnginePath' => ROOT_PATH . '/include/vendor/Smarty/Smarty.class.php', //视图engine 所在位置
        'viewEngineConf' => array( //视图engine相关配置
                'caching'         => false, //是否使用缓存，项目在调试期间，不建议启用缓存
                'template_dir'    => '@/templates', //设置模板目录
                'compile_dir'     => '@/templates_c', //设置编译目录
                'cache_dir'       => '@/smarty_cache', //缓存文件夹
                'cache_lifetime'  => 3600, // 缓存更新时间, 默认 3600 秒
                'force_compile'   => false,
                'left_delimiter'  => '<{', // smarty左限定符
                'right_delimiter' => '}>', // smarty右限定符
                'auto_literal'    => true, // Smarty3新特性
        ),
);