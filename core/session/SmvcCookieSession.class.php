<?php

class SmvcCookieSession extends SmvcBaseSession
{

    /**
     * array of driver config defaults
     */
    protected static $_defaults = [
            'cookie_name' => 'smvcid',
    ];

    // --------------------------------------------------------------------

    public function __construct($config = [])
    {

        parent::__construct($config);
        // merge the driver config with the global config
        $this->config = array_merge(
                $config,
                (isset($config['cookie']) and is_array($config['cookie'])) ? $config['cookie'] : self::$_defaults
        );

        $this->config = $this->validateConfig($this->config);
    }

    /**
     * todo
     * read the session
     *
     * @access    public
     *
     * @param string $id
     *
     * @internal  param $boolean , set to true if we want to force a new session to be created
     *
     * @return    $this
     */
    public function read($id)
    {
        // initialize the session
        $this->data  = [];
        $this->keys  = [];
        $this->flash = [];

        // get the session cookie
        $payload = $this->getCookie();

        // validate it
        if ($payload === false) {
            // not a valid cookie, or a forced session reset
        } elseif (!isset($payload[0]) or !is_array($payload[0])) {
            // not a valid cookie payload
        } elseif ($payload[0]['updated'] + $this->config['expiration_time'] <= SmvcUtilHelper::getTime()) {
            // session has expired
        } elseif ($this->config['match_ip'] and $payload[0]['ip_hash'] !== md5(Router::getRemoteIp() . Router::clientIp())) {
            // IP address doesn't match
        } elseif ($this->config['match_ua'] and $payload[0]['user_agent'] !== Router::getUserAgent()) {
            // user agent doesn't match
        } else {
            // session is valid, retrieve the payload
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

        return parent::read($id);
    }

    // --------------------------------------------------------------------

    /**
     * write the current session
     *
     * @access    public
     *
     * @param string $id
     *
     * @return $this
     * @throws Exception
     */
    public function write($id)
    {
        // do we have something to write?
        if (!empty($this->keys) or !empty($this->data) or !empty($this->flash)) {
            parent::write($id);

            // rotate the session id if needed
            $this->rotate(false);

            // record the last update time of the session
            $this->keys['updated'] = SmvcUtilHelper::getTime();

            // then update the cookie
            $this->setCookie(array($this->keys, $this->data, $this->flash));
        }

        return $this;
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
        $validated = [];

        foreach ($config as $name => $item) {
            // filter out any driver config
            if (!is_array($item)) {
                switch ($name) {
                    case 'cookie_name':
                        if (empty($item) or !is_string($item)) {
                            $item = 'smvcid';
                        }
                        break;

                    default:
                        // no config item for this driver
                        break;
                }

                // global config, was validated in the driver
                $validated[$name] = $item;
            }
        }

        // validate all global settings as well
        return parent::validateConfig($validated);
    }

    /**
     * Garbage collection. Remove all expired entries atomically.
     *
     * @param int $maxLifeTime
     *
     * @return bool
     */
    public function gc($maxLifeTime)
    {
        // TODO: Implement gc() method.
    }
}


