<?php
return [
        'session' => [
                'auto_initialize'        => true, //获取方式  C('session.auto_initialize')
                'driver'                 => 'cookie', //cookie database redis memcache
                'enable_cookie'          => true,
                'match_ip'               => false,
                'match_ua'               => true,
                'cookie_domain'          => '',
                'cookie_path'            => '/',
                'cookie_http_only'       => null,
                'encrypt_cookie'         => true,
                'expire_on_close'        => false,
                'expiration_time'        => 7200,
                'rotation_time'          => 300,
                'flash_id'               => 'flash',
                'flash_auto_expire'      => true,
                'flash_expire_after_get' => true,
                'post_cookie_name'       => '',
                'redis'                  => array(
                        'host'     => '127.0.0.1',
                        'port'     => '3306',
                        'pconnect' => false,
                ),
                'file'                   => array(
                        'path' => 'e:/tmp/',
                ),
                'memcached'              => [
                        'host' => '127.0.0.1',
                        'port' => '11211',
                ],
                'database'               => [
                        'database_type'  => 'mysql',
                        'server'         => 'localhost',
                        'database_name'  => 'test',
                        'db_port'        => '3306',
                        'username'       => 'root',
                        'password'       => '',
                        'table'          => 'smvc_sessions',
                        'gc_probability' => 5,
                ],
        ],
];