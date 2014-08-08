<?php

class Session
{
    /**
     * loaded session driver instance
     */
    protected static $_proxy = null;

    /**
     * array of loaded instances
     */
    protected static $_instances = array();

    /**
     * array of global config defaults
     */
    protected static $_defaults = array(
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
            'post_cookie_name'       => ''
    );


    // --------------------------------------------------------------------

    /**
     * Factory
     *
     * Produces fully configured session driver instances
     *
     * @param    array|string full driver config or just driver type
     *
     * @throws SessionException
     * @throws Exception
     * @return
     */
    public static function forge($custom = array())
    {
        $config = C('session');

        // When a string was passed it's just the driver type
        if ($custom && !is_array($custom)) {
            $custom = array('driver' => $custom);
        }

        $config = array_merge(self::$_defaults, $config, $custom);

        if (empty($config['driver'])) {
            throw new SessionException('No session driver given or no default session driver set.');
        }

        // determine the driver to load
        $class = 'Smvc' . ucfirst($config['driver']) . 'Session';

        /**  @var SmvcBaseSession */
        $driver = null;

        $driver = new $class($config);
        SmvcDebugHelper::instance()->debug(
                array(
                        'info'  => $driver,
                        'label' => '$driver ' . __METHOD__,
                        'level' => 'error',
                )
        );

        SmvcDebugHelper::instance()->debug(
                array(
                        'info'  => $config,
                        'label' => '$config ' . __METHOD__,
                        'level' => 'error',
                )
        );
        // get the driver's cookie name
        $cookieName = isset($config['cookie_name']) ? $config['cookie_name'] : 'smvcid';
        SmvcDebugHelper::instance()->debug(
                array(
                        'info'  => $cookieName,
                        'label' => '$cookieName ' . __METHOD__,
                        'level' => 'error',
                )
        );
        // do we already have a driver instance for this cookie?
        if (isset(self::$_instances[$cookieName])) {
            // if so, they must be using the same driver class!
            if (self::$_instances[$cookieName] instanceof $class) {
                throw new Exception('You can not instantiate two different sessions using the same cookie name "' . $cookieName . '"');
            }
        } else {
            // register a shutdown event to update the session
            // init the session
            $driver->init();
            $driver->read();

            register_shutdown_function(array(&$driver, "write"), array(''));

            // store this instance
            self::$_instances[$cookieName] =& $driver;
        }

        return self::$_instances[$cookieName];
    }

    // --------------------------------------------------------------------

    /**
     * class constructor
     *
     * @internal  param $void
     *
     * @access    private
     * @return Session
     */
    final private function __construct()
    {
    }

    // --------------------------------------------------------------------

    /**
     * create or return the driver instance
     *
     * @param    void
     *
     * @access    public
     * @return    SmvcBaseSession object
     */
    public static function instance($instance = null)
    {
        if (isset(self::$_proxy[$instance]) && self::$_proxy[$instance]) {
            return self::$_proxy[$instance];
        } else {
            self::$_proxy[$instance] = self::forge($instance);
        }
        return static::$_proxy[$instance];
    }

    // --------------------------------------------------------------------

    /**
     * set session variables
     *
     * @param    string|array       name of the variable to set or array of values, array(name => value)
     * @param                 mixed $value
     *
     * @access    public
     * @return $this
     */
    public static function set($name, $value = null)
    {
        return self::instance()->set($name, $value);
    }

    // --------------------------------------------------------------------

    /**
     * get session variables
     *
     * @access    public
     *
     * @param    string $name    of the variable to get
     * @param    mixed  $default value to return if the variable does not exist
     *
     * @return    mixed
     */
    public static function get($name = null, $default = null)
    {
        return self::instance()->get($name, $default);
    }

    // --------------------------------------------------------------------

    /**
     * delete a session variable
     *
     * @param    string $name of the variable to delete
     *
     * @access    public
     * @return $this
     */
    public static function delete($name)
    {
        return self::instance()->delete($name);
    }

    // --------------------------------------------------------------------

    /**
     * get session key variables
     *
     * @access    public
     *
     * @param    string $name of the variable to get, default is 'session_id'
     *
     * @return    mixed
     */
    public static function key($name = 'session_id')
    {
        return self::instance()->key($name);
    }

    // --------------------------------------------------------------------

    /**
     * set session flash variables
     *
     * @param    string $name of the variable to set
     * @param    mixed  $value
     *
     * @access    public
     * @return $this
     */
    public static function setFlash($name, $value = null)
    {
        return self::instance()->setFlash($name, $value);
    }

    // --------------------------------------------------------------------

    /**
     * get session flash variables
     *
     * @access    public
     *
     * @param    string $name     of the variable to get
     * @param    mixed  $default  value to return if the variable does not exist
     * @param           bool      true if the flash variable needs to expire immediately
     *
     * @return    mixed
     */
    public static function getFlash($name = null, $default = null, $expire = null)
    {
        return self::instance()->getFlash($name, $default, $expire);
    }

    // --------------------------------------------------------------------

    /**
     * keep session flash variables
     *
     * @access    public
     *
     * @param    string $name of the variable to keep
     *
     * @return $this
     */
    public static function keepFlash($name = null)
    {
        return self::instance()->keepFlash($name);
    }

    // --------------------------------------------------------------------

    /**
     * delete session flash variables
     *
     * @param    string $name of the variable to delete
     *
     * @access    public
     * @return $this
     */
    public static function deleteFlash($name = null)
    {
        return self::instance()->deleteFlash($name);
    }

    // --------------------------------------------------------------------

    /**
     * create a new session
     *
     * @access    public
     * @return $this
     */
    public static function create()
    {
        return self::instance()->create();
    }

    // --------------------------------------------------------------------

    /**
     * read the session
     *
     * @access    public
     *
     * @param $id
     *
     * @return SmvcSessionInterface
     */
    public static function read($id)
    {
        return self::instance()->read($id);
    }

    // --------------------------------------------------------------------

    /**
     * write the session
     *
     * @access    public
     *
     * @param $id
     *
     * @return SmvcSessionInterface
     */
    public static function write($id)
    {
        return self::instance()->write($id);
    }

    // --------------------------------------------------------------------

    /**
     * rotate the session id
     *
     * @access    public
     * @return $this
     */
    public static function rotate()
    {
        return self::instance()->rotate();
    }

    // --------------------------------------------------------------------

    /**
     * destroy the current session
     *
     * @access    public
     *
     * @param string $id
     *
     * @return $this
     */
    public static function destroy($id = '')
    {
        return self::instance()->destroy($id);
    }
}



//class Session
//{
//    private $driver;
//
//    public function __construct($config, $specifiedDriver = false)
//    {
//        if ($specifiedDriver) {
//            $config['driver'] = $specifiedDriver;
//        } else {
//            $config['driver'] = '';
//        }
//        $driver = ucwords($config['driver']);
//
//        $this->driver = new $driver($config);
//    }
//
//    /**
//     * Use magic method 'call' to pass user method
//     * into driver method
//     *
//     * @param string @name
//     * @param array  @arguments
//     *
//     * @return mixed
//     */
//    public function __call($name, $arguments)
//    {
//        return call_user_func_array(array($this->driver, $name), $arguments);
//    }
//
//    /**
//     * PHP Magic method for calling a class property dinamicly
//     *
//     * @param string $name
//     *
//     * @return mixed
//     */
//    public function __get($name)
//    {
//        return $this->driver->$name;
//    }
//
//    /**
//     * PHP Magic method for set a class property dinamicly
//     *
//     * @param string $name
//     * @param mixed  $value
//     *
//     * @return void
//     */
//    public function __set($name, $value)
//    {
//        $this->driver->$name = $value;
//    }
//
//}