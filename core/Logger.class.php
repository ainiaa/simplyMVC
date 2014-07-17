<?php

class Logger
{
    private $driver;

    public function __construct($config, $specifiedDriver = false)
    {
        if ($specifiedDriver) {
            $config['driver'] = $specifiedDriver;
        } else {
            $config['driver'] = '';
        }
        $driver = ucwords($config['driver']);

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