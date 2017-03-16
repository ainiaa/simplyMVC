<?php
/**
 * 数据库相关配置项
 */
return [
        'db'        => [
                'split'  => [//分库相关 todo 这个可以使用其他方法实现
                        [
                                'DB_HOST'   => 'localhost', //数据库HOST
                                'DB_NAME'   => 'smvc_test_master', //数据库名
                                'DB_PORT'   => '3306', //数据库端口
                                'DB_PREFIX' => 'smvc_',
                                'DB_TYPE'   => 'mysql',
                                'DB_USER'   => 'root',
                                'DB_PASS'   => 'root',
                        ],
                ],
                'master' => [//主库
                        [//第一个主库
                                'DB_HOST'   => 'localhost', //数据库HOST
                                'DB_NAME'   => 'smvc_test_master', //数据库名
                                'DB_PORT'   => '3306', //数据库端口
                                'DB_PREFIX' => 'smvc_',
                                'DB_TYPE'   => 'mysql',
                                'DB_USER'   => 'root',
                                'DB_PASS'   => 'root',
                        ],
                        [//第二个主库
                                'DB_HOST'   => 'localhost', //数据库HOST
                                'DB_NAME'   => 'smvc_test_master', //数据库名
                                'DB_PORT'   => '3306', //数据库端口
                                'DB_PREFIX' => 'smvc_',
                                'DB_TYPE'   => 'mysql',
                                'DB_USER'   => 'root',
                                'DB_PASS'   => 'root',
                        ],
                ],
                'slave'  => [//从库
                        0 => [//第一个master对应的slave
                                [
                                        'DB_HOST'   => 'localhost', //数据库HOST
                                        'DB_NAME'   => 'smvc_test_slave', //数据库名
                                        'DB_PORT'   => '3306', //数据库端口
                                        'DB_PREFIX' => 'smvc_',
                                        'DB_TYPE'   => 'mysql',
                                        'DB_USER'   => 'root',
                                        'DB_PASS'   => 'root',
                                ],
                                [
                                        'DB_HOST'   => 'localhost', //数据库HOST
                                        'DB_NAME'   => 'smvc_test_slave', //数据库名
                                        'DB_PORT'   => '3306', //数据库端口
                                        'DB_PREFIX' => 'smvc_',
                                        'DB_TYPE'   => 'mysql',
                                        'DB_USER'   => 'root',
                                        'DB_PASS'   => 'root',
                                ],
                        ],
                        1 => [//第二个master对应的slave
                                [
                                        'DB_HOST'   => 'localhost', //数据库HOST
                                        'DB_NAME'   => 'smvc_test_slave', //数据库名
                                        'DB_PORT'   => '3306', //数据库端口
                                        'DB_PREFIX' => 'smvc_',
                                        'DB_TYPE'   => 'mysql',
                                        'DB_USER'   => 'root',
                                        'DB_PASS'   => 'root',
                                ],
                                [
                                        'DB_HOST'   => 'localhost', //数据库HOST
                                        'DB_NAME'   => 'smvc_test_slave', //数据库名
                                        'DB_PORT'   => '3306', //数据库端口
                                        'DB_PREFIX' => 'smvc_',
                                        'DB_TYPE'   => 'mysql',
                                        'DB_USER'   => 'root',
                                        'DB_PASS'   => 'root',
                                ],
                        ],

                ],
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