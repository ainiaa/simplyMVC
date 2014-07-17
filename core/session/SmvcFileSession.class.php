<?php
class SmvcFileSession extends SmvcBaseSession
{

    /**
     * array of driver config defaults
     */
    protected static $_defaults = array(
            'cookie_name'    => 'fuelfid', // name of the session cookie for file based sessions
            'path'           => '/tmp', // path where the session files should be stored
            'gc_probability' => 5 // probability % (between 0 and 100) for garbage collection
    );

    // --------------------------------------------------------------------

    public function __construct($config = array())
    {
        // merge the driver config with the global config
        $this->config = array_merge($config, is_array($config['file']) ? $config['file'] : self::$_defaults);

        $this->config = $this->validateConfig($this->config);
    }

    // --------------------------------------------------------------------

    /**
     * create a new session
     *
     * @access    public
     * @return    $this
     */
    public function create()
    {
        // create a new session
        $this->keys['session_id']  = $this->newSessionId();
        $this->keys['previous_id'] = $this->keys['session_id']; // prevents errors if previous_id has a unique index
        $this->keys['ip_hash']     = md5(Input::ip() . Input::real_ip());
        $this->keys['user_agent']  = Input::user_agent();
        $this->keys['created']     = $this->time->get_timestamp();
        $this->keys['updated']     = $this->keys['created'];

        return $this;
    }

    // --------------------------------------------------------------------

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
            $payload = $this->readFile($cookie[0]);

            if ($payload === false) {
                // cookie present, but session record missing. force creation of a new session
                return $this->read(true);
            }

            // unpack the payload
            $payload = $this->unserialize($payload);

            // session referral?
            if (isset($payload['rotated_session_id'])) {
                $payload = $this->readFile($payload['rotated_session_id']);
                if ($payload === false) {
                    // cookie present, but session record missing. force creation of a new session
                    return $this->read(true);
                } else {
                    // unpack the payload
                    $payload = $this->unserialize($payload);
                }
            }

            if (!isset($payload[0]) or !is_array($payload[0])) {
                // not a valid cookie payload
            } elseif ($payload[0]['updated'] + $this->config['expiration_time'] <= $this->time->get_timestamp()) {
                // session has expired
            } elseif ($this->config['match_ip'] and $payload[0]['ip_hash'] !== md5(Input::ip() . Input::real_ip())) {
                // IP address doesn't match
            } elseif ($this->config['match_ua'] and $payload[0]['user_agent'] !== Input::user_agent()) {
                // user agent doesn't match
            } else {
                // session is valid, retrieve the payload
                if (isset($payload[0]) and is_array($payload[0])) {
                    $this->keys = $payload[0];
                }
                if (isset($payload[1]) and is_array($payload[1])) $this->data = $payload[1];
                if (isset($payload[2]) and is_array($payload[2])) $this->flash = $payload[2];
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
            $this->keys['updated'] = $this->time->get_timestamp();

            // session payload
            $payload = $this->serialize(array($this->keys, $this->data, $this->flash));

            // create the session file
            $this->writeFile($this->keys['session_id'], $payload);

            // was the session id rotated?
            if (isset($this->keys['previous_id']) and $this->keys['previous_id'] != $this->keys['session_id']) {
                // point the old session file to the new one, we don't want to lose the session
                $payload = $this->serialize(array('rotated_session_id' => $this->keys['session_id']));
                $this->writeFile($this->keys['previous_id'], $payload);
            }

            // then update the cookie
            $this->setCookie(array($this->keys['session_id']));

            // do some garbage collection
            if (mt_rand(0, 100) < $this->config['gc_probability']) {
                if ($handle = opendir($this->config['path'])) {
                    $expire = $this->time->get_timestamp() - $this->config['expiration_time'];

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
     * @return    $this
     */
    public function destroy()
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
            while (!flock($handle, LOCK_EX)) ;

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
                while (!flock($handle, LOCK_SH)) ;

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
                switch ($name) {
                    case 'cookie_name':
                        if (empty($item) OR !is_string($item)) {
                            $item = 'fuelfid';
                        }
                        break;

                    case 'path':
                        // do we have a path?
                        if (empty($item) OR !is_dir($item)) {
                            throw new Exception('You have specify a valid path to store the session data files.');
                        }
                        // and can we write to it?
                        if (!is_writable($item)) {
                            throw new Exception('The webserver doesn\'t have write access to the path to store the session data files.');
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

}


