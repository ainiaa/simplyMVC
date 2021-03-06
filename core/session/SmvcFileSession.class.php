<?php

class SmvcFileSession extends SmvcBaseSession
{

    /**
     * array of driver config defaults
     */
    protected static $_defaults = [
            'cookie_name'    => 'smvcid', // name of the session cookie for file based sessions
            'path'           => '/tmp', // path where the session files should be stored
            'gc_probability' => 5 // probability % (between 0 and 100) for garbage collection
    ];

    // --------------------------------------------------------------------

    public function __construct($config = [])
    {
        parent::__construct($config);
        // merge the driver config with the global config
        if (is_array($config)) {
            $fileConfig = isset($config['file']) && is_array($config['file']) ? $config['file'] : [];
            $config     = array_merge(self::$_defaults, $config, $fileConfig);
        }

        $this->config = $config;

        $this->config = $this->validateConfig($this->config);
    }

    /**
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
    public function read($id = '')
    {
        // initialize the session
        $this->data  = [];
        $this->keys  = [];
        $this->flash = [];

        // get the session cookie
        $cookie = $this->getCookie();

        // if a cookie was present, find the session record
        if ($cookie && isset($cookie[0])) {
            // read the session file
            $payload = $this->readFile($cookie[0]);

            if ($payload === false) {
                // cookie present, but session record missing. force creation of a new session
                return parent::read($id);
            }

            // unpack the payload
            $payload = $this->unserialize($payload);

            // session referral?
            if (isset($payload['rotated_session_id'])) {
                $payload = $this->readFile($payload['rotated_session_id']);
                if ($payload === false) {
                    // cookie present, but session record missing. force creation of a new session
                    return parent::read($id);
                } else {
                    // unpack the payload
                    $payload = $this->unserialize($payload);
                }
            }

            if (!isset($payload[0]) or !is_array($payload[0])) {
                // not a valid cookie payload
            } elseif ($payload[0]['updated'] + $this->config['expiration_time'] <= SmvcUtilHelper::getTime()) {
                // session has expired
            } elseif ($this->config['match_ip'] and $payload[0]['ip_hash'] !== md5(
                            Request::getRemoteIp() . Request::getClientIp()
                    )
            ) {
                // IP address doesn't match
            } elseif ($this->config['match_ua'] and $payload[0]['user_agent'] !== Request::getUserAgent()) {
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
        }

        return parent::read($id);
    }

    // --------------------------------------------------------------------

    /**
     * write the session
     *
     * @access    public
     * @return    $this
     */
    public function write($id = '')
    {
        // do we have something to write?
        if (!empty($this->keys) or !empty($this->data) or !empty($this->flash)) {
            parent::write($id);

            // rotate the session id if needed
            $this->rotate(false);

            // record the last update time of the session
            $this->keys['updated'] = SmvcUtilHelper::getTime();

            // session payload
            $payload = $this->serialize([$this->keys, $this->data, $this->flash]);

            // create the session file
            $this->writeFile($this->keys['session_id'], $payload);
            file_debug(['id' => $this->keys['session_id'], '$payload' => $payload]);

            // was the session id rotated?
            if (isset($this->keys['previous_id']) and $this->keys['previous_id'] != $this->keys['session_id']) {
                // point the old session file to the new one, we don't want to lose the session
                $payload = $this->serialize(['rotated_session_id' => $this->keys['session_id']]);
                $this->writeFile($this->keys['previous_id'], $payload);
            }

            // then update the cookie
            $this->setCookie(array($this->keys['session_id']));

            // do some garbage collection
            if (mt_rand(0, 100) < $this->config['gc_probability']) {
                if ($handle = opendir($this->config['path'])) {
                    $expire = SmvcUtilHelper::getTime() - $this->config['expiration_time'];

                    while (($file = readdir($handle)) !== false) {
                        if (filetype($this->config['path'] . $file) == 'file' and strpos(
                                        $file,
                                        $this->config['cookie_name'] . '_'
                                ) === 0 and filemtime($this->config['path'] . $file) < $expire
                        ) {
                            @unlink($this->config['path'] . $file);
                        }
                    }
                    closedir($handle);
                }
            }
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * destroy the current session
     *
     * @access    public
     *
     * @param string $id
     *
     * @return    $this
     */
    public function destroy($id = '')
    {
        // do we have something to destroy?
        if (!empty($this->keys)) {
            // delete the session file
            $file = $this->config['path'] . $this->config['cookie_name'] . '_' . $this->keys['session_id'];
            if (is_file($file)) {
                unlink($file);
            }
        }

        parent::destroy();

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Writes the session file
     *
     * @access    private
     *
     * @param $session_id
     * @param $payload
     *
     * @throws Exception
     * @return  boolean, true if it was an existing session, false if not
     */
    protected function writeFile($session_id, $payload)
    {
        // create the session file
        $file   = $this->config['path'] . $this->config['cookie_name'] . '_' . $session_id;
        $exists = is_file($file);
        $handle = fopen($file, 'c');
        if ($handle) {
            // wait for a lock
            while (!flock($handle, LOCK_EX)) {
                ;
            }

            // erase existing contents
            ftruncate($handle, 0);

            // write the session data
            fwrite($handle, $payload);

            //release the lock
            flock($handle, LOCK_UN);

            // close the file
            fclose($handle);
        } else {
            throw new Exception('Could not open the session file in "' . $this->config['path'] . " for write access");
        }

        return $exists;
    }

    // --------------------------------------------------------------------

    /**
     * Reads the session file
     *
     * @access    private
     *
     * @param $session_id
     *
     * @return  mixed, the payload if the file exists, or false if not
     */
    protected function readFile($session_id)
    {
        $payload = false;

        $file = $this->config['path'] . $this->config['cookie_name'] . '_' . $session_id;
        if (is_file($file)) {
            $handle = fopen($file, 'r');
            if ($handle) {
                // wait for a lock
                while (!flock($handle, LOCK_SH)) {
                    ;
                }

                // read the session data
                if ($size = filesize($file)) {
                    $payload = fread($handle, $size);
                }

                //release the lock
                flock($handle, LOCK_UN);

                // close the file
                fclose($handle);

            }
        }

        return $payload;
    }


    /**
     * validate a driver config value
     *
     * @param array $config
     *
     * @throws Exception
     * @internal  param array $array with configuration values
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
                        if (empty($item) OR !is_string($item)) {
                            $item = 'smvcid';
                        }
                        break;

                    case 'path':
                        // do we have a path?
                        if (empty($item) OR !is_dir($item)) {
                            throw new Exception('You have specify a valid path to store the session data files.');
                        }
                        // and can we write to it?
                        if (!is_writable($item)) {
                            throw new Exception(
                                    'The webserver doesn\'t have write access to the path to store the session data files.'
                            );
                        }
                        // update the path, and add the trailing slash
                        $item = realpath($item) . '/';
                        break;

                    case 'gc_probability':
                        // do we have a path?
                        if (!is_numeric($item) OR $item < 0 OR $item > 100) {
                            // default value: 5%
                            $item = 5;
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
     * @return boolean
     */
    public function gc($maxLifeTime)
    {
    }
}


