<?php

class SmvcRedisSession extends SmvcBaseSession
{

    /**
     * array of driver config defaults
     */
    protected static $_defaults = array(
            'cookie_name' => 'fuelrid', // name of the session cookie for redis based sessions
            'database'    => 'default' // name of the redis database to use (as configured in config/db.php)
    );

    /*
     * @var	storage for the redis object
     */
    protected $redis = false;

    // --------------------------------------------------------------------

    public function __construct($config = array())
    {
        // merge the driver config with the global config
        $this->config = array_merge($config, is_array($config['redis']) ? $config['redis'] : static::$_defaults);

        $this->config = $this->validateConfig($this->config);
    }

    // --------------------------------------------------------------------

    /**
     * driver initialisation
     *
     * @access    public
     * @return    void
     */
    public function init()
    {
        // generic driver initialisation
        parent::init();

        if ($this->redis === false) {
            // get the redis database instance
            $this->redis = Redis_Db::instance($this->config['database']);
        }
    }

    /**
     * read the session
     *
     * @access    public
     *
     * @param    boolean , set to true if we want to force a new session to be created
     *
     * @return    $this
     */
    public function read($force = false)
    {
        // initialize the session
        $this->data  = array();
        $this->keys  = array();
        $this->flash = array();

        // get the session cookie
        $cookie = $this->getCookie();

        // if a cookie was present, find the session record
        if ($cookie and !$force and isset($cookie[0])) {
            // read the session file
            $payload = $this->readRedis($cookie[0]);

            if ($payload === false) {
                // cookie present, but session record missing. force creation of a new session
                return $this->read(true);
            }

            // unpack the payload
            $payload = $this->unserialize($payload);

            // session referral?
            if (isset($payload['rotated_session_id'])) {
                $payload = $this->readRedis($payload['rotated_session_id']);
                if ($payload === false) {
                    // cookie present, but session record missing. force creation of a new session
                    return $this->read(true);
                }

                // unpack the payload
                $payload = $this->unserialize($payload);
            }

            if (!isset($payload[0]) or !is_array($payload[0])) {
                // not a valid cookie payload
            } elseif ($payload[0]['updated'] + $this->config['expiration_time'] <= SmvcUtilHelper::getTime()) {
                // session has expired
            } elseif ($this->config['match_ip'] and $payload[0]['ip_hash'] !== md5(Router::ip() . Router::realIp())) {
                // IP address doesn't match
            } elseif ($this->config['match_ua'] and $payload[0]['user_agent'] !== Router::getUserAgent()) {
                // user agent doesn't match
            } else {
                // session is valid, retrieve the rest of the payload
                if (isset($payload[0]) and is_array($payload[0])) {
                    $this->keys = $payload[0];
                }
                if (isset($payload[1]) and is_array($payload[1])) {
                    $this->data = $payload[1];
                }
                if (isset($payload[2]) and is_array($payload[2])) {
                    $this->flash = $payload[2];
                }
            }
        }

        return parent::read();
    }

    // --------------------------------------------------------------------

    /**
     * write the session
     *
     * @access    public
     * @return    $this
     */
    public function write()
    {
        // do we have something to write?
        if (!empty($this->keys) or !empty($this->data) or !empty($this->flash)) {
            parent::write();

            // rotate the session id if needed
            $this->rotate(false);

            // record the last update time of the session
            $this->keys['updated'] = SmvcUtilHelper::getTime();

            // session payload
            $payload = $this->serialize(array($this->keys, $this->data, $this->flash));

            // create the session file
            $this->_write_redis($this->keys['session_id'], $payload);

            // was the session id rotated?
            if (isset($this->keys['previous_id']) and $this->keys['previous_id'] != $this->keys['session_id']) {
                // point the old session file to the new one, we don't want to lose the session
                $payload = $this->serialize(array('rotated_session_id' => $this->keys['session_id']));
                $this->_write_redis($this->keys['previous_id'], $payload);
            }

            $this->setCookie(array($this->keys['session_id']));
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * destroy the current session
     *
     * @access    public
     * @return    $this
     */
    public function destroy()
    {
        // do we have something to destroy?
        if (!empty($this->keys)) {
            // delete the key from the redis server
            $this->redis->del($this->keys['session_id']);
        }

        parent::destroy();

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Writes the redis entry
     *
     * @access    private
     * @return  boolean, true if it was an existing session, false if not
     */
    protected function _write_redis($session_id, $payload)
    {
        // write it to the redis server
        $this->redis->set($session_id, $payload);
        $this->redis->expire($session_id, $this->config['expiration_time']);
    }

    // --------------------------------------------------------------------

    /**
     * Reads the redis entry
     *
     * @access    private
     * @return  mixed, the payload if the file exists, or false if not
     */
    protected function readRedis($session_id)
    {
        // fetch the session data from the redis server
        return $this->redis->get($session_id);
    }

    // --------------------------------------------------------------------

    /**
     * validate a driver config value
     *
     * @param    array    array with configuration values
     *
     * @access    public
     * @return  array    validated and consolidated config
     */
    public function validateConfig($config)
    {
        $validated = array();

        foreach ($config as $name => $item) {
            // filter out any driver config
            if (!is_array($item)) {
                switch ($item) {
                    case 'cookie_name':
                        if (empty($item) or !is_string($item)) {
                            $item = 'fuelrid';
                        }
                        break;

                    case 'database':
                        // do we have a servers config
                        if (empty($item) or !is_array($item)) {
                            $item = 'default';
                        }
                        break;

                    default:
                        break;
                }

                // global config, was validated in the driver
                $validated[$name] = $item;
            }
        }

        // validate all global settings as well
        return parent::validateConfig($validated);
    }

}


