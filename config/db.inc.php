<?php
/**
 * 数据库相关配置项
 */
return array(
        'db' => array(
                'master' => array(//主库
                        array(
                                'DB_HOST'   => 'localhost', //数据库HOST
                                'DB_NAME'   => 'smvc_test_master', //数据库名
                                'DB_PORT'   => '3306', //数据库端口
                                'DB_PREFIX' => 'smvc_',
                                'DB_TYPE'   => 'mysql',
                                'DB_USER'   => 'root',
                                'DB_PASS'   => '',
                        ),
                ),
                'slave'  => array(//从库
                        array(
                                'DB_HOST'   => 'localhost', //数据库HOST
                                'DB_NAME'   => 'smvc_test_slave', //数据库名
                                'DB_PORT'   => '3306', //数据库端口
                                'DB_PREFIX' => 'smvc_',
                                'DB_TYPE'   => 'mysql',
                                'DB_USER'   => 'root',
                                'DB_PASS'   => '',
                        ),
                        array(
                                'DB_HOST'   => 'localhost', //数据库HOST
                                'DB_NAME'   => 'smvc_test_slave', //数据库名
                                'DB_PORT'   => '3306', //数据库端口
                                'DB_PREFIX' => 'smvc_',
                                'DB_TYPE'   => 'mysql',
                                'DB_USER'   => 'root',
                                'DB_PASS'   => '',
                        ),
                        array(
                                'DB_HOST'   => 'localhost', //数据库HOST
                                'DB_NAME'   => 'smvc_test_slave', //数据库名
                                'DB_PORT'   => '3306', //数据库端口
                                'DB_PREFIX' => 'smvc_',
                                'DB_TYPE'   => 'mysql',
                                'DB_USER'   => 'root',
                                'DB_PASS'   => '',
                        ),
                ),
        )
);