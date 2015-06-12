<?php
/**
 * 数据库相关配置项
 */
return array(
        'db' => array(
                'split'  => array(//分库相关 todo 这个可以使用其他方法实现
                        array(
                                'DB_HOST'   => 'localhost', //数据库HOST
                                'DB_NAME'   => 'smvc_test_master', //数据库名
                                'DB_PORT'   => '3306', //数据库端口
                                'DB_PREFIX' => 'smvc_',
                                'DB_TYPE'   => 'mysql',
                                'DB_USER'   => 'root',
                                'DB_PASS'   => 'root',
                        ),
                ),
                'master' => array(//主库
                        array(//第一个主库
                                'DB_HOST'   => 'localhost', //数据库HOST
                                'DB_NAME'   => 'smvc_test_master', //数据库名
                                'DB_PORT'   => '3306', //数据库端口
                                'DB_PREFIX' => 'smvc_',
                                'DB_TYPE'   => 'mysql',
                                'DB_USER'   => 'root',
                                'DB_PASS'   => 'root',
                        ),
                        array(//第二个主库
                                'DB_HOST'   => 'localhost', //数据库HOST
                                'DB_NAME'   => 'smvc_test_master', //数据库名
                                'DB_PORT'   => '3306', //数据库端口
                                'DB_PREFIX' => 'smvc_',
                                'DB_TYPE'   => 'mysql',
                                'DB_USER'   => 'root',
                                'DB_PASS'   => 'root',
                        ),
                ),
                'slave'  => array(//从库
                        0 => array(////第一个master对应的slave
                                array(
                                        'DB_HOST'   => 'localhost', //数据库HOST
                                        'DB_NAME'   => 'smvc_test_slave', //数据库名
                                        'DB_PORT'   => '3306', //数据库端口
                                        'DB_PREFIX' => 'smvc_',
                                        'DB_TYPE'   => 'mysql',
                                        'DB_USER'   => 'root',
                                        'DB_PASS'   => 'root',
                                ),
                                array(
                                        'DB_HOST'   => 'localhost', //数据库HOST
                                        'DB_NAME'   => 'smvc_test_slave', //数据库名
                                        'DB_PORT'   => '3306', //数据库端口
                                        'DB_PREFIX' => 'smvc_',
                                        'DB_TYPE'   => 'mysql',
                                        'DB_USER'   => 'root',
                                        'DB_PASS'   => 'root',
                                ),
                        ),
                        1 => array(//第二个master对应的slave
                                array(
                                        'DB_HOST'   => 'localhost', //数据库HOST
                                        'DB_NAME'   => 'smvc_test_slave', //数据库名
                                        'DB_PORT'   => '3306', //数据库端口
                                        'DB_PREFIX' => 'smvc_',
                                        'DB_TYPE'   => 'mysql',
                                        'DB_USER'   => 'root',
                                        'DB_PASS'   => 'root',
                                ),
                                array(
                                        'DB_HOST'   => 'localhost', //数据库HOST
                                        'DB_NAME'   => 'smvc_test_slave', //数据库名
                                        'DB_PORT'   => '3306', //数据库端口
                                        'DB_PREFIX' => 'smvc_',
                                        'DB_TYPE'   => 'mysql',
                                        'DB_USER'   => 'root',
                                        'DB_PASS'   => 'root',
                                ),
                        ),

                ),
        )
);