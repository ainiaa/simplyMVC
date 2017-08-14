<?php
/**
 * 数据库相关配置项
 */
return [
        'db'        => [
                'DB_HOST'   => 'localhost', //数据库HOST
                'DB_NAME'   => 'smvc_test_master', //数据库名
                'DB_PORT'   => '3306', //数据库端口
                'DB_PREFIX' => 'smvc_',
                'DB_TYPE'   => 'mysql',
                'DB_USER'   => 'root',
                'DB_PASS'   => 'root',
                'CHARSET'   => 'utf8',
                'LOGGING'   => true,
        ],
        'redis'     => [
                'host'     => '127.0.0.1',
                'port'     => '3306',
                'pconnect' => false,
        ],
        'memcached' => [
                'host' => '127.0.0.1',
                'port' => '11211',
        ],
];