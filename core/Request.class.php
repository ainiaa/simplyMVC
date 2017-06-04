<?php

/**
 * Class Request
 */
class Request
{

    private static $instance = null;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * @return Request
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Request();
        }
        return self::$instance;
    }

    /**
     * @param      $origin
     * @param      $key
     * @param null $defaultValue
     *
     * @return array|null
     */
    private static function getParamBase($origin, $key, $defaultValue = null)
    {
        if ($key == '*') {
            return $origin;
        } else if (is_array($key)) {
            $final = [];
            foreach ($key as $k) {
                $final[$k] = isset($origin[$k]) ? $origin[$k] : $defaultValue;
            }
            return $final;
        } else {
            return isset($origin[$key]) ? $origin[$key] : $defaultValue;
        }
    }

    /**
     * @param      $type
     * @param      $key
     * @param null $defaultValue
     * @param null $callback
     *
     * @return array|null
     */
    public static function getParam($type, $key, $defaultValue = null, $callback = null)
    {
        switch (strtolower($type)) {
            case 'get':
                $return = self::getParamBase($_GET, $key, $defaultValue);
                break;
            case 'post':
                $return =  self::getParamBase($_POST, $key, $defaultValue);
                break;
            case 'request':
                $return =  self::getParamBase($_REQUEST, $key, $defaultValue);
                break;
            case 'cookie':
                $return =  self::getParamBase($_COOKIE, $key, $defaultValue);
                break;
            case 'server':
                $return =  self::getParamBase($_SERVER, $key, $defaultValue);
                break;
            case 'session':
                $return =  self::getParamBase($_SESSION, $key, $defaultValue);
                break;
            case 'env':
                $return =  self::getParamBase($_ENV, $key, $defaultValue);
                break;
            default:
                $return =  null;
        }
        if ($callback && is_callable($callback)) {
            return $callback($return);
        }
        return $return;
    }

    /**
     * @param $origin
     * @param $key
     * @param $value
     */
    private static function setParamBase(&$origin, $key, $value)
    {
        if (is_array($key) && is_null($value)) {
            array_merge($origin, $key);
        } else if (is_array($key) && !is_null($value)) {
            foreach ($key as $k) {
                $origin[$k] = $value;
            }
        } else {
            $origin[$key] = $value;
        }
    }

    /**
     * @param      $type
     * @param      $key
     * @param null $value
     */
    public static function setParam($type, $key, $value = null)
    {
        switch (strtolower($type)) {
            case 'get':
                self::setParamBase($_GET, $key, $value);
                break;
            case 'post':
                self::setParamBase($_POST, $key, $value);
                break;
            case 'request':
                self::setParamBase($_REQUEST, $key, $value);
                break;
            case 'cookie':
                self::setParamBase($_COOKIE, $key, $value);
                break;
            case 'server':
                self::setParamBase($_SERVER, $key, $value);
                break;
            case 'session':
                self::setParamBase($_SESSION, $key, $value);
                break;
            case 'env':
                self::setParamBase($_ENV, $key, $value);
                break;
            default:
                self::setParamBase($_REQUEST, $key, $value);
        }
    }

    /**
     * @param string $key
     *
     * @return null|string
     */
    public static function getGet($key = '*')
    {
        return self::getParam('get', $key);
    }

    /**
     * @param string $key
     *
     * @return null|string
     */
    public static function getPost($key = '*')
    {
        return self::getParam('post', $key);
    }

    /**
     * @param string $key
     *
     * @return null|string
     */
    public static function getRequest($key = '*')
    {
        return self::getParam('request', $key);
    }

    /**
     * @param string $key
     *
     * @return null|string
     */
    public static function getCookie($key = '*')
    {
        return self::getParam('cookie', $key);
    }

    /**
     * @param string $key
     *
     * @return null|string
     */
    public static function getServer($key = '*')
    {
        return self::getParam('server', $key);
    }

    /**
     * @param string $key
     *
     * @return null|string
     */
    public static function getEnv($key = '*')
    {
        return self::getParam('env', $key);
    }


    // ==================

    /**
     * @param string $key
     * @param null   $value
     *
     * @return null|string
     */
    public static function setGet($key, $value = null)
    {
        return self::setParam('get', $key, $value);
    }

    /**
     * @param string $key
     * @param null   $value
     *
     * @return null|string
     */
    public static function setPost($key, $value = null)
    {
        return self::setParam('post', $key, $value);
    }

    /**
     * @param string $key
     * @param null   $value
     *
     * @return null|string
     */
    public static function setRequest($key, $value = null)
    {
        return self::setParam('request', $key, $value);
    }

    /**
     * @param string $key
     * @param null   $value
     *
     * @return null|string
     */
    public static function setCookie($key, $value = null)
    {
        return self::setParam('cookie', $key, $value);
    }

    /**
     * @param string $key
     * @param null   $value
     *
     * @return null|string
     */
    public static function setServer($key, $value = null)
    {
        return self::setParam('server', $key, $value);
    }

    /**
     * @param string $key
     * @param null   $value
     *
     * @return null|string
     */
    public static function setEnv($key, $value = null)
    {
        return self::setParam('env', $key, $value);
    }
}