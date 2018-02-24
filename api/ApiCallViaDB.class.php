<?php

/**
 * Class ApiCallViaDB
 */
class ApiCallViaDB extends ApiCallBase
{
    const API_SECRET = 'fjzlT9HcyumCwV4VGxKtGkwdyQC9P07p';
    const TIMEOUT = 10;

    /**
     * post方式提交
     *
     * @param      $api
     * @param      $data
     * @param int  $timeout
     * @param null $cookie
     *
     * @return mixed
     */
    public function post($api, $data, $timeout = self::TIMEOUT, $cookie = null)
    {
        $handler = $this->getHanderByApi($api, $data);
        if ($handler) {
            return $handler->invoke($data);
        }
        return null;
    }

    /**
     * get方式提交
     *
     * @param       $api
     * @param       $data
     * @param int   $timeout
     * @param array $cookie
     *
     * @return mixed
     */
    public function get($api, $data = [], $timeout = self::TIMEOUT, $cookie = [])
    {
        $handler = $this->getHanderByApi($api, $data);
        if ($handler) {
            return $handler->invoke($data);
        }
        return null;
    }

    /**
     * @param        $api
     * @param string $data
     *
     * @return ApiBaseService
     */
    public function getHanderByApi($api, $data = '')
    {
        return new ApiBaseService($api);
    }
}