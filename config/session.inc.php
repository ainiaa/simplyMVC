<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 13-11-7
 * Time: 下午5:29
 */

return array(
    'session' => array(
        'auto_initialize'        => true,//获取方式  C('session.auto_initialize')
        'driver'                 => 'cookie',
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
    ),
);