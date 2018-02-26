<?php

/**
 * Class Api
 */
class Api
{
    const API_SECRET = 'fjzlT9HcyumCwV4VGxKtGkwdyQC9P07p';
    const TIMEOUT = 10;
    private static $instances = [];

    /**
     * @param string $type
     *
     * @return ApiCallBase
     */
    public static function getInstance($type = '')
    {
        if (empty($type)) {
            $type = C('API_CALL_TYPE');
        }
        $id = md5($type);
        if (!isset(self::$instances[$id])) {
            switch ($type) {
                case 'curl':
                    self::$instances[$id] = new ApiCallViaCurl();
                    break;
                case 'grpc':
                    self::$instances[$id] = new ApiCallViaGRPC();
                    break;
                default:
                    self::$instances[$id] = new ApiCallViaDB();
            }
        }

        return self::$instances[$id];
    }

    /**
     * post方式提交
     *
     * @param      $api
     * @param      $data
     * @param int  $second
     *
     * @param null $cookie
     *
     * @return mixed
     */
    public static function post($api, $data, $second = self::TIMEOUT, $cookie = null)
    {
        $instance = self::getInstance();
        return $instance->post($api, $data, $second, $cookie);
    }

    /**
     * get方式提交
     *
     * @param       $api
     * @param       $data
     * @param int   $second
     * @param array $cookie
     *
     * @return mixed
     */
    public static function get($api, $data = [], $second = self::TIMEOUT, $cookie = [])
    {
        $instance = self::getInstance();
        return $instance->get($api, $data, $second, $cookie);
    }
}