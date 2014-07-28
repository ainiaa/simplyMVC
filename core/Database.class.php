<?php

/**
 * Class Database
 */
class Database
{
    public static $sql = array();
    private static $connections = array();
    public static $instance = null;
    public static $debug = false;
    private $driver = null;

    const NUM   = 0;
    const ASSOC = 1;
    const BOTH  = 2;

    const VERSION = '20140728';

    function __get($name)
    {
        return $this->driver->$name;
    }

    function __set($name, $value)
    {
        $this->driver->$name = $value;
    }

    function __call($fun, $params = array())
    {
        return call_user_func_array(array($this->driver, $fun), $params);
    }

    function __construct()
    {
        $params = func_get_args();

        if (count($params) == 1) {
            $params = $params[0];
        }

        list(, $sp) = self::getParamHash($params);

        $this->driver = self::getDriver($params, $sp);
    }

    private static function getDriver($params, $sp)
    {
        if (is_array($params)) {
            $driver = array_shift($params);
        } elseif (strpos($params, '://')) { // dsn
            if (!$dsn = parse_url($params)) {
                throw new DatabaseException("cant detect the dsn: {$params}");
            }
            if (!isset($dsn['scheme'])) {
                throw new DatabaseException("cant detect the driver: {$params}");
            }
            $driver = $dsn['scheme'];
            $params = array();

            $params[0] = isset($dsn['host']) ? $dsn['host'] : '';
            $params[1] = isset($dsn['user']) ? $dsn['user'] : '';
            $params[2] = isset($dsn['pass']) ? $dsn['pass'] : '';
            $params[3] = isset($dsn['path']) ? ltrim($dsn['path'], '/') : '';

            if ($driver == 'mysql') {
                isset($dsn['port']) && $params[0] .= ":{$dsn['port']}";
            } elseif ($driver == 'mysqli') {
                isset($dsn['port']) && $params[4] = $dsn['port'];
            } else {
                throw new DatabaseException("not support dsn driver: {$driver}");
            }

        } elseif (preg_match('/type \((\w+)|object\((\w+)\)/', $sp, $driver)) {
            $driver = strtolower(array_pop($driver));
            if ($driver == 'sqlitedatabase') {
                $driver = 'sqlite';
            }
        } else {
            throw new DatabaseException("cant auto detect the database driver");
        }

        require_once dirname(__FILE__) . '/Driver/' . $driver . '.php';
        $class = $driver . 'Wrapper';

        return new $class($params);
    }

    private static function getParamHash($params)
    {
        // mabe the param is object, so use var_dump
        ob_start();
        var_dump($params);
        $sp  = ob_get_clean();
        $key = sha1($sp);
        // $key = md5(serialize($params));

        return array($key, $sp);
    }

    public static function connect()
    {
        $params = func_get_args();

        if (count($params) == 1) {
            $params = $params[0];
        }

        list($key, $sp) = self::getParamHash($params);

        if (!isset(self::$connections[$key])) {
            self::$connections[$key] = self::getDriver($params, $sp);
        }

        return self::$connections[$key];
    }
}