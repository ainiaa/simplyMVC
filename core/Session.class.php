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
    protected static $_instances = [];

    /**
     * array of global config defaults
     */
    protected static $_defaults = [
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
    ];



    /**
     * Factory
     *
     * Produces fully configured session driver instances
     *
     * @param    array|string full driver config or just driver type
     *
     * @throws SessionException
     * @throws Exception
     * @return SmvcSessionInterface
     */
    public static function forge($custom = [])
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

        /**
         * @var SmvcBaseSession
         */
        $driver = null;

        $driver = new $class($config);

        // get the driver's cookie name
        $cookieName = isset($config['cookie_name']) ? $config['cookie_name'] : 'smvcid';
        // do we already have a driver instance for this cookie?
        if (isset(self::$_instances[$cookieName])) {
            // if so, they must be using the same driver class!
            if (self::$_instances[$cookieName] instanceof $class) {
                throw new Exception(
                        'You can not instantiate two different sessions using the same cookie name "' . $cookieName . '"'
                );
            }
        } else {
            // register a shutdown event to update the session
            // init the session
            $driver->init();
//            $driver->read();

//            register_shutdown_function([&$driver, 'write'], ['']);

            // store this instance
            self::$_instances[$cookieName] =& $driver;
        }

        return self::$_instances[$cookieName];
    }


    /**
     * class constructor
     * @access    private
     */
    final private function __construct()
    {
    }


    /**
     * create or return the driver instance
     *
     * @param    void
     *
     * @access    public
     * @return    SmvcBaseSession object
     */
    public static function getInstance($instance = null)
    {
        if (is_null($instance) && C('session.driver')) {
            $instance = C('session.driver');
        }

        if (isset(self::$_proxy[$instance]) && self::$_proxy[$instance]) {
            return self::$_proxy[$instance];
        } else {
            self::$_proxy[$instance] = self::forge($instance);
        }
        return static::$_proxy[$instance];
    }


    /**
     * set session variables
     *
     * @param    string|array       name of the variable to set or array of values, array(name => value)
     * @param                 mixed $value
     *
     * @access    public
     * @return SmvcSessionInterface
     */
    public static function set($name, $value = null)
    {
        return self::getInstance()->set($name, $value);
    }


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
        return self::getInstance()->get($name, $default);
    }


    /**
     * delete a session variable
     *
     * @param    string $name of the variable to delete
     *
     * @access    public
     * @return SmvcSessionInterface
     */
    public static function delete($name)
    {
        return self::getInstance()->delete($name);
    }


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
        return self::getInstance()->key($name);
    }


    /**
     * set session flash variables
     *
     * @param    string $name of the variable to set
     * @param    mixed  $value
     *
     * @access    public
     * @return SmvcSessionInterface
     */
    public static function setFlash($name, $value = null)
    {
        return self::getInstance()->setFlash($name, $value);
    }


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
        return self::getInstance()->getFlash($name, $default, $expire);
    }


    /**
     * keep session flash variables
     *
     * @access    public
     *
     * @param    string $name of the variable to keep
     *
     * @return SmvcSessionInterface
     */
    public static function keepFlash($name = null)
    {
        return self::getInstance()->keepFlash($name);
    }


    /**
     * delete session flash variables
     *
     * @param    string $name of the variable to delete
     *
     * @access    public
     * @return SmvcSessionInterface
     */
    public static function deleteFlash($name = null)
    {
        return self::getInstance()->deleteFlash($name);
    }


    /**
     * create a new session
     *
     * @access    public
     * @return SmvcSessionInterface
     */
    public static function create()
    {
        return self::getInstance()->create();
    }


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
        return self::getInstance()->read($id);
    }


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
        return self::getInstance()->write($id);
    }


    /**
     * rotate the session id
     *
     * @access    public
     * @return SmvcSessionInterface
     */
    public static function rotate()
    {
        return self::getInstance()->rotate();
    }


    /**
     * destroy the current session
     *
     * @access    public
     *
     * @param string $id
     *
     * @return SmvcSessionInterface
     */
    public static function destroy($id = '')
    {
        return self::getInstance()->destroy($id);
    }
}