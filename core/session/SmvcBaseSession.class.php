<?php

/**
 * based on fueld
 * Class SmvcBaseSession
 */
abstract class SmvcBaseSession implements SmvcSessionInterface
{

    /*
     * @var	session class configuration
     */
    protected $config = array();

    /*
     * @var	session indentification keys
     */
    protected $keys = array();

    /*
     * @var	session variable data
     */
    protected $data = array();

    /*
     * @var	session flash data
     */
    protected $flash = array();

    /*
     * @var	session time object
     */
    protected $time = null;


    public function __construct($config = array())
    {
        session_set_save_handler(
                array($this, 'create'),
                array($this, 'destroy'),
                array($this, 'read'),
                array($this, 'write'),
                array($this, 'destroy'),
                array($this, 'gc')
        );
    }

    /**
     * create a new session
     *
     * @access    public
     * @return $this
     */
    public function create()
    {
        // create a new session
        $this->keys['session_id']  = $this->newSessionId();
        $this->keys['previous_id'] = $this->keys['session_id']; // prevents errors if previous_id has a unique index
        $this->keys['ip_hash']     = md5(Router::getRemoteIp() . Router::clientIp());
        $this->keys['user_agent']  = Router::getUserAgent();
        $this->keys['created']     = SmvcUtilHelper::getTime();
        $this->keys['updated']     = $this->keys['created'];

        return $this;
    }


    // --------------------------------------------------------------------
    // generic driver methods
    // --------------------------------------------------------------------

    /**
     * destroy the current session
     *
     * @access    public
     */
    public function destroy($id = '')
    {
        // delete the session cookie
        Cookie::delete($this->config['cookie_name']);
        //        unset($_COOKIE[$this->config['cookie_name']]);

        // reset the stored session data
        $this->keys = $this->flash = $this->data = array();

        return $this;
    }

    /**
     * read the session
     *
     * @access    public
     *
     * @param string $id
     *
     * @return    SmvcSessionInterface
     */
    public function read($id)
    {
        // do we need to create a new session?
        empty($this->keys) and $this->create();

        // mark the loaded flash data, auto-expire if configured
        foreach ($this->flash as $key => $value) {
            if ($this->config['flash_auto_expire'] === true) {
                $this->flash[$key]['state'] = 'expire';
            } else {
                $this->flash[$key]['state'] = 'loaded';
            }
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * write the session
     *
     * @access    public
     *
     * @param string $id
     *
     * @return    SmvcSessionInterface
     */
    public function write($id)
    {
        // create the session if it doesn't exist
        empty($this->keys) and $this->create();

        $this->cleanupFlash();

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * generic driver initialisation
     *
     * @access    public
     * @return    void
     */
    public function init()
    {
        // get a time object
        $this->time = SmvcUtilHelper::getTime();
    }

    // --------------------------------------------------------------------

    /**
     * set session variables
     *
     * @param array|string $name
     * @param null         $value
     *
     * @internal  param array|string $name of the variable to set or array of values, array(name => value)
     * @internal  param \value $mixed
     *
     * @access    public
     * @return $this
     */
    public function set($name, $value = null)
    {
        is_null($name) or SmvcArrayHelper::set($this->data, $name, $value);

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * get session variables
     *
     * @access    public
     *
     * @param    string $name    name of the variable to get
     * @param    mixed  $default default value to return if the variable does not exist
     *
     * @return    mixed
     */
    public function get($name, $default = null)
    {
        if (is_null($name)) {
            return $this->data;
        }
        return SmvcArrayHelper::get($this->data, $name, $default);
    }

    // --------------------------------------------------------------------

    /**
     * get session key variables
     *
     * @access    public
     *
     * @param    string $name name of the variable to get, default is 'session_id'
     *
     * @return    mixed    contents of the requested variable, or false if not found
     */
    public function key($name = 'session_id')
    {
        return isset($this->keys[$name]) ? $this->keys[$name] : false;
    }

    // --------------------------------------------------------------------

    /**
     * delete session variables
     *
     * @param    string $name name of the variable to delete
     *
     * @access    public
     * @return $this
     */
    public function delete($name)
    {
        SmvcArrayHelper::delete($this->data, $name);

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * force a session_id rotation
     *
     * @access    public
     *
     * @param    boolean , if true, force a session id rotation
     *
     * @return  $this
     */
    public function rotate($force = true)
    {
        // do we have a session?
        if (!empty($this->keys)) {
            // existing session. need to rotate the session id?
            if ($force or ($this->config['rotation_time'] and $this->keys['created'] + $this->config['rotation_time'] <= SmvcUtilHelper::getTime(
                            ))
            ) {
                // generate a new session id, and update the create timestamp
                $this->keys['previous_id'] = $this->keys['session_id'];
                $this->keys['session_id']  = $this->newSessionId();
                $this->keys['created']     = SmvcUtilHelper::getTime();
                $this->keys['updated']     = $this->keys['created'];
            }
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * set session flash variables
     *
     * @param    string $name  name of the variable to set
     * @param    mixed  $value value
     *
     * @access    public
     * @return   $this
     */
    public function setFlash($name, $value)
    {
        if (strpos($name, '.') !== false) {
            $keys = explode('.', $name, 2);
            $name = array_shift($keys);
        } else {
            $keys = false;
        }

        if ($keys) {
            isset($this->flash[$this->config['flash_id'] . '::' . $name]['value']) or $this->flash[$this->config['flash_id'] . '::' . $name] = array(
                    'state' => 'new',
                    'value' => array()
            );
            SmvcArrayHelper::set($this->flash[$this->config['flash_id'] . '::' . $name]['value'], $keys[0], $value);
        } else {
            $this->flash[$this->config['flash_id'] . '::' . $name] = array('state' => 'new', 'value' => $value);
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * get session flash variables
     *
     * @access    public
     *
     * @param    string $name    name of the variable to get
     * @param    mixed  $default default value to return if the variable does not exist
     * @param    bool   $expire  true if the flash variable needs to expire immediately, false to use "flash_auto_expire"
     *
     * @return    mixed
     */
    public function getFlash($name, $default = null, $expire = null)
    {
        // if no expiration is given, use the config default
        is_bool($expire) or $expire = $this->config['flash_expire_after_get'];

        if (is_null($name)) {
            $default = array();
            foreach ($this->flash as $key => $value) {
                $key           = substr($key, strpos($key, '::') + 2);
                $default[$key] = $value;
            }
        } else {
            // check if we need to run an Arr:get()
            if (strpos($name, '.') !== false) {
                $keys = explode('.', $name, 2);
                $name = array_shift($keys);
            } else {
                $keys = false;
            }

            if (isset($this->flash[$this->config['flash_id'] . '::' . $name])) {
                // if it's not a var set in this request, mark it for expiration
                if ($this->flash[$this->config['flash_id'] . '::' . $name]['state'] !== 'new' or $expire) {
                    $this->flash[$this->config['flash_id'] . '::' . $name]['state'] = 'expire';
                }

                if ($keys) {
                    $default = SmvcArrayHelper::get(
                            $this->flash[$this->config['flash_id'] . '::' . $name]['value'],
                            $keys[0],
                            $default
                    );
                } else {
                    $default = $this->flash[$this->config['flash_id'] . '::' . $name]['value'];
                }
            }
        }

        return SimpleMVC::value($default);
    }

    // --------------------------------------------------------------------

    /**
     * keep session flash variables
     *
     * @access    public
     *
     * @param    string $name name of the variable to keep
     *
     * @return    $this
     */
    public function keepFlash($name)
    {
        if (is_null($name)) {
            foreach ($this->flash as $key => $value) {
                $this->flash[$key]['state'] = 'new';
            }
        } elseif (isset($this->flash[$this->config['flash_id'] . '::' . $name])) {
            $this->flash[$this->config['flash_id'] . '::' . $name]['state'] = 'new';
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * delete session flash variables
     *
     * @param    string $name name of the variable to delete
     *
     * @access    public
     * @return    $this
     */
    public function deleteFlash($name)
    {
        if (is_null($name)) {
            $this->flash = array();
        } elseif (isset($this->flash[$this->config['flash_id'] . '::' . $name])) {
            unset($this->flash[$this->config['flash_id'] . '::' . $name]);
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * set the session flash id
     *
     * @param    string $name name of the id to set
     *
     * @access    public
     * @return    $this
     */
    public function setFlashId($name)
    {
        $this->config['flash_id'] = (string)$name;

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * get the current session flash id
     *
     * @access    public
     * @return    string    name of the flash id
     */
    public function getFlashId()
    {
        return $this->config['flash_id'];
    }

    // --------------------------------------------------------------------

    /**
     * get a runtime config value
     *
     * @param    string $name name of the config variable to get
     *
     * @access    public
     * @return  mixed
     */
    public function getConfig($name)
    {
        return isset($this->config[$name]) ? $this->config[$name] : null;
    }

    // --------------------------------------------------------------------

    /**
     * set a runtime config value
     *
     * @param    string $name name of the config variable to set
     *
     * @param null      $value
     *
     * @access    public
     * @return  $this
     */
    public function setConfig($name, $value = null)
    {
        if (isset($this->config[$name])) {
            $this->config[$name] = $value;
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * removes flash variables marked as old
     *
     * @access    public
     * @return  void
     */
    public function cleanupFlash()
    {
        foreach ($this->flash as $key => $value) {
            if ($value['state'] === 'expire') {
                unset($this->flash[$key]);
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * generate a new session id
     *
     * @access    public
     * @return string
     */
    public function newSessionId()
    {
        $session_id = '';
        while (strlen($session_id) < 32) {
            $session_id .= mt_rand(0, mt_getrandmax());
        }
        return md5(uniqid($session_id, true));
    }

    // --------------------------------------------------------------------

    /**
     * write a cookie
     *
     * @access    public
     *
     * @param array $payload , cookie payload
     *
     * @throws Exception
     * @return boolean
     */
    public function setCookie($payload = array())
    {
        $enable_cookie = isset($this->config['enable_cookie']) ? $this->config['enable_cookie'] : true;
        if ($enable_cookie) {
            $payload = $this->serialize($payload);

            SmvcDebugHelper::getInstance()->debug(
                    array(
                            'info'  => $payload,
                            'label' => '$payload ori ' . __METHOD__,
                            'level' => 'error',
                    )
            );
            // encrypt the payload if needed
            $this->config['encrypt_cookie'] and $payload = Crypt::encode($payload);
            SmvcDebugHelper::getInstance()->debug(
                    array(
                            'info'  => $payload,
                            'label' => '$payload encode' . __METHOD__,
                            'level' => 'error',
                    )
            );

            SmvcDebugHelper::getInstance()->debug(
                    array(
                            'info'  => Crypt::decode($payload),
                            'label' => '$payload decode' . __METHOD__,
                            'level' => 'error',
                    )
            );

            // make sure it doesn't exceed the cookie size specification
            if (strlen($payload) > 4000) {
                throw new Exception('The session data stored by the application in the cookie exceeds 4Kb. Select a different session storage driver.');
            }

            // write the session cookie
            if ($this->config['expire_on_close']) {
                return Cookie::set(
                        $this->config['cookie_name'],
                        $payload,
                        0,
                        $this->config['cookie_path'],
                        $this->config['cookie_domain'],
                        null,
                        $this->config['cookie_http_only']
                );
            } else {
                return Cookie::set(
                        $this->config['cookie_name'],
                        $payload,
                        $this->config['expiration_time'],
                        $this->config['cookie_path'],
                        $this->config['cookie_domain'],
                        null,
                        $this->config['cookie_http_only']
                );
            }
        }

        return false;
    }

    // --------------------------------------------------------------------

    /**
     * read a cookie
     *
     * @access    public
     * @return bool|string
     */
    public function getCookie()
    {
        // was the cookie value posted?
        $cookie = Router::getPost($this->config['post_cookie_name'], false);

        SmvcDebugHelper::getInstance()->debug(array(
                        'info' => $cookie,
                        'label' => '$cookie ' . __METHOD__,
                        'level' => 'warn',
                ));
        SmvcDebugHelper::getInstance()->debug(array(
                        'info' => $_COOKIE,
                        'label' => '$_COOKIE ' . __METHOD__,
                        'level' => 'warn',
                ));
        // if not found, fetch the regular cookie
        if ($cookie === false) {
            $cookie_name = isset($this->config['cookie_name']) ? $this->config['cookie_name'] : 'smvcid';
            SmvcDebugHelper::getInstance()->debug(
                    array(
                            'info'  => $this->config,
                            'label' => '$this->config ' . __METHOD__,
                            'level' => 'info',
                    )
            );
            $cookie = Cookie::get(
                    $cookie_name
            ); //isset($_COOKIE[$this->config['cookie_name']]) ? $_COOKIE[$this->config['cookie_name']] : false;
            SmvcDebugHelper::getInstance()->debug(
                    array(
                            'info'  => $cookie,
                            'label' => '$cookie ' . __METHOD__,
                            'level' => 'error',
                    )
            );
        }

        // if not found, check the URL for a cookie
        if ($cookie === false) {
            $cookie = Router::getGet($this->config['cookie_name'], false);
        }

        // if not found, was a session-id present in the HTTP header?
        if ($cookie === false) {
            $header_header_name = isset($this->config['header_header_name']) ? $this->config['header_header_name'] : '';
            $cookie             = Router::getHeader($header_header_name, false);
        }

        if ($cookie !== false) {
            // fetch the payload
            $this->config['encrypt_cookie'] and $cookie = Crypt::decode($cookie);
            SmvcDebugHelper::getInstance()->debug(
                    array(
                            'info'  => $cookie,
                            'label' => '$cookie ' . __METHOD__,
                            'level' => 'error',
                    )
            );
            $cookie = $this->unserialize($cookie);

            // validate the cookie format: must be an array
            if (is_array($cookie)) {
                // cookies use nested arrays, other drivers have a string value
                if (($this->config['driver'] === 'cookie' and !is_array(
                                        $cookie[0]
                                )) or ($this->config['driver'] !== 'cookie' and !is_string($cookie[0]))
                ) {
                    // invalid specific format
                    $cookie = false;
                }
            } // or a string containing the session id
            elseif (is_string($cookie) and strlen($cookie) == 32) {
                $cookie = array($cookie);
            } // invalid general format
            else {
                $cookie = false;
            }
        }

        // and the result
        return $cookie;
    }

    // --------------------------------------------------------------------

    /**
     * Serialize an array
     *
     * This function first converts any slashes found in the array to a temporary
     * marker, so when it gets unserialized the slashes will be preserved
     *
     * @access    public
     *
     * @param    array
     *
     * @return    string
     */
    public function serialize($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                if (is_string($val)) {
                    $data[$key] = str_replace('\\', '{{slash}}', $val);
                }
            }
        } else {
            if (is_string($data)) {
                $data = str_replace('\\', '{{slash}}', $data);
            }
        }

        return serialize($data);
    }

    // --------------------------------------------------------------------

    /**
     * Unserialize
     *
     * This function unserializes a data string, then converts any
     * temporary slash markers back to actual slashes
     *
     * @access    public
     *
     * @param    array
     *
     * @return    string
     */
    public function unserialize($input)
    {
        $data = @unserialize($input);

        if (is_array($data)) {
            foreach ($data as $key => $val) {
                if (is_string($val)) {
                    $data[$key] = str_replace('{{slash}}', '\\', $val);
                }
            }

            return $data;
        } else if ($data === false) {
            is_string($input) and $data = array($input);
        }

        return (is_string($data)) ? str_replace('{{slash}}', '\\', $data) : $data;
    }

    // --------------------------------------------------------------------

    /**
     * validate__config
     *
     * This function validates all global (driver independent) configuration values
     *
     * @access    public
     *
     * @param    array
     *
     * @return    array
     */
    public function validateConfig($config)
    {
        $validated = array();

        foreach ($config as $name => $item) {
            switch ($name) {
                case 'driver':
                    // if we get here, this one was ok... ;-)
                    break;

                case 'match_ip':
                case 'match_ua':
                case 'enable_cookie':
                case 'cookie_http_only':
                case 'encrypt_cookie':
                case 'expire_on_close':
                case 'flash_expire_after_get':
                case 'flash_auto_expire':
                    // make sure it's a boolean
                    $item = (bool)$item;
                    break;

                case 'post_cookie_name':
                case 'http_header_name':
                case 'cookie_domain':
                    // make sure it's a string
                    $item = (string)$item;
                    break;

                case 'cookie_path':
                    // make sure it's a string
                    $item = (string)$item;
                    empty($item) and $item = '/';
                    break;

                case 'expiration_time':
                    // make sure it's an integer
                    $item = (int)$item;
                    // invalid? set it to two years from now
                    $item <= 0 and $item = 86400 * 365 * 2;
                    break;

                case 'rotation_time':
                    // make sure it's an integer
                    $item = (int)$item;
                    // invalid? set it to 5 minutes
                    $item <= 0 and $item = 300;
                    break;

                case 'flash_id':
                    // make sure it's a string
                    $item = (string)$item;
                    empty($item) and $item = 'flash';
                    break;

                default:
                    // ignore this setting
                    break;

            }

            // store the validated result
            $validated[$name] = $item;
        }

        return $validated;
    }
}


