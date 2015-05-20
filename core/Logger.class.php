<?php

class Logger
{
    private $driver;

    private static $loggerInstance = null;

    /**
     * @param null $config
     * @param bool $specifiedDriver
     *
     * @return SmvcLoggerInterface
     */
    public static function getInstance($config = null, $specifiedDriver = false)
    {
        if (is_null($config)) {
            $config = C('logger');
        }
        if ($specifiedDriver) {
            $config['driver'] = $specifiedDriver;
        }
        $driver = ucwords($config['driver']);

        if (!isset(self::$loggerInstance[$driver])) {
            self::$loggerInstance[$driver] = new self($config);
        }

        return self::$loggerInstance[$driver];
    }

    private function __clone()
    {
    }


    /**
     * @param $config
     */
    private function __construct($config)
    {
        $driver       = ucwords($config['driver']);
        $this->driver = new $driver($config);
    }

    /**
     * Use magic method 'call' to pass user method
     * into driver method
     *
     * @param string @name
     * @param array  @arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->driver, $name), $arguments);
    }

    /**
     * PHP Magic method for calling a class property dinamicly
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->driver->$name;
    }

    /**
     * PHP Magic method for set a class property dinamicly
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    public function __set($name, $value)
    {
        $this->driver->$name = $value;
    }

}