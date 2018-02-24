<?php

class ApiBaseService extends BaseService
{

    private $api;
    private $service;
    private $conf;

    /**
     * ApiBaseService constructor.
     *
     * @param $api
     * @param $params
     */
    public function __construct($api)
    {
        $this->api     = $api;
        $this->conf    = $this->getConf($api);
        $this->service = $this->getService($api);
    }

    /**
     * @param $api
     *
     * @return mixed
     */
    private function getService($api)
    {
        static $services;
        if (!isset($services[$api])) {
            if (empty($this->conf)) {
                $this->conf = $this->getConf($api);
            }
            if (empty($this->conf)) {
                trigger_error('api configure not found api:' . $api . '   at file:' . __FILE__ . ' line:' . __LINE__);
            }
            $file    = $this->conf['file'];
            $service = $this->conf['service'];
            if (file_exists_case($file)) {
                $services[$api] = Factory::getInstance($service);
            } else {
                trigger_error('file not found: ' . $file . '   at file:' . __FILE__ . ' line:' . __LINE__);
                return null;
            }
        }

        return $services[$api];
    }

    /**
     * @param $api
     *
     * @return mixed
     */
    public function getConf($api)
    {
        $key = 'API_SERVICE.' . $api;
        $conf = C($key);
        return $conf;
    }

    public function invoke($params)
    {
        $method = $this->conf['method'];
        return $this->service->$method($params);
    }

    /**
     * @param $api
     * @param $params
     *
     * @return mixed
     */
    public function getData($params)
    {
        return $this->invoke($params);
    }

    public function postData($params)
    {
        return $this->invoke($params);
    }
}
