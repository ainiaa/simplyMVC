<?php
//https://github.com/kahwee/php-db-session-handler/blob/master/DbSessionHandler.php
// --------------------------------------------------------------------

class SmvcDbSession extends SmvcBaseSession
{


    /**
     * @var medoo
     */
    public $db;

    /*
     * @var	session database result object
     */
    protected $record = null;

    /**
     * array of driver config defaults
     */
    protected static $_defaults = array(
            'cookie_name'    => 'smvcid', // name of the session cookie for database based sessions
            'table'          => 'smvc_sessions', // name of the sessions table
            'gc_probability' => 5 // probability % (between 0 and 100) for garbage collection
    );


    public function __construct($config = array())
    {
        parent::__construct($config);
        // merge the driver config with the global config
        $dbConfig     = isset($config['database']) && is_array(
                $config['database']
        ) ? $config['database'] : self::$_defaults;
        $this->config = array_merge($config, $dbConfig);

        $this->config = $this->validateConfig($this->config);

        $this->getDbInstance();
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
    public function read($id = '')
    {
        // initialize the session
        $this->data   = array();
        $this->keys   = array();
        $this->flash  = array();
        $this->record = null;

        // get the session cookie
        $cookie = $this->getCookie();
        SmvcDebugHelper::instance()->debug(
                array(
                        'info'  => $cookie,
                        'label' => '$cookie ' . __METHOD__,
                        'level' => 'info',
                )
        );

        // if a cookie was present, find the session record
        if ($cookie && isset($cookie[0])) {
            // read the session record
            $this->record = $this->db->get(
                    $this->config['table'],
                    '*',
                    array('session_id' => $cookie[0])

            ); // DB::select()->where('session_id', '=', $cookie[0])->from($this->config['table'])->execute(
            //                    $this->config['database']
            //            );
            //todo 这个需要修改
            SmvcDebugHelper::instance()->debug(
                    array(
                            'info'  => $this->record,
                            'label' => '$this->record ' . __METHOD__,
                            'level' => 'info',
                    )
            );

            // record found?
            if (is_array($this->record) && count($this->record) > 0) {
                $payload = isset($this->record['payload']) ? $this->record['payload'] : '';
                $payload = $this->unserialize($payload);
            } else {
                // try to find the session on previous id
                $this->record = $this->db->get(
                        $this->config['table'],
                        '*',
                        array('previous_id' => $cookie[0])

                );
                //                DB::select()->where('previous_id', '=', $cookie[0])->from(
                //                        $this->config['table']
                //                )->execute($this->config['database']);

                // record found?
                if (is_array($this->record) && count($this->record) > 0) {
                    $payload = isset($this->record['payload']) ? $this->record['payload'] : '';
                    $payload = $this->unserialize($payload);
                } else {
                    // cookie present, but session record missing. force creation of a new session
                    return $this->read(true);
                }
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

        SmvcDebugHelper::instance()->debug(
                array(
                        'info'  => $this,
                        'lable' => '$this ' . __METHOD__,
                        'level' => 'info',
                )
        );


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
     * @return    $this
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

            // create the session record, and add the session payload
            $session            = $this->keys;
            $session['payload'] = $this->serialize(array($this->keys, $this->data, $this->flash));

            // do we need to create a new session?
            if (is_null($this->record)) {
                // create the new session record
                //                $result = DB::insert($this->config['table'], array_keys($session))->values($session)->execute(
                //                        $this->config['database']
                //                );
                $result = $this->db->insert($this->config['table'], $session);

            } else {
                // update the database
                $session_id = ''; //todo 需要处理
                $result     = $this->db->update($this->config['table'], array(), array('session_id' => $session_id));
                //                $result = DB::update($this->config['table'])->set($session)->where(
                //                        'session_id',
                //                        '=',
                //                        $this->record->get('session_id')
                //                )->execute($this->config['database']);
            }

            // update went well?
            if ($result !== false) {
                // then update the cookie
                $this->setCookie(array($this->keys['session_id']));
            } else {
                //                logger(\Fuel::L_ERROR, 'Session update failed, session record could not be found. Concurrency issue?');//todo logger
            }

            // do some garbage collection
            if (mt_rand(0, 100) < $this->config['gc_probability']) {
                $expired = SmvcUtilHelper::getTime() - $this->config['expiration_time'];
                $result  = $this->db->delete($this->config['table'], array('updated[<]' => $expired));

                //                $result  = DB::delete($this->config['table'])->where('updated', '<', $expired)->execute(
                //                        $this->config['database']
                //                );
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
        if (!empty($this->keys) and !empty($this->record)) {
            // delete the session record
            $result = $this->db->delete($this->config['table'], array('session_id[<]' => $this->keys['session_id']));
            //            $result = DB::delete($this->config['table'])->where('session_id', '=', $this->keys['session_id'])->execute(
            //                    $this->config['database']
            //            );
        }

        // reset the stored session data
        $this->record = null;

        parent::destroy();

        return $this;
    }

    // --------------------------------------------------------------------

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
        $validated = array();

        foreach ($config as $name => $item) {
            // filter out any driver config
            if (!is_array($item)) {
                switch ($name) {
                    case 'cookie_name':
                        if (empty($item) or !is_string($item)) {
                            $item = 'smvcid';
                        }
                        break;

                    case 'database':
                        // do we have a database?
                        if (empty($item) or !is_string($item)) {
                            Config::load('db', true);
                            $item = Config::get('db.active', false);
                        }
                        if ($item === false) {
                            throw new Exception('You have specify a database to use database backed sessions.');
                        }
                        break;

                    case 'table':
                        // and a table name?
                        if (empty($item) or !is_string($item)) {
                            throw new Exception('You have specify a database table name to use database backed sessions.');
                        }
                        break;

                    case 'gc_probability':
                        // do we have a path?
                        if (!is_numeric($item) or $item < 0 or $item > 100) {
                            // default value: 5%
                            $item = 5;
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

    /**
     * Garbage collection. Remove all expired entries atomically.
     *
     * @param int $maxLifeTime
     *
     * @return boolean
     */
    public function gc($maxLifeTime)
    {
        // TODO: Implement gc() method.
    }

    public function getDbInstance()
    {
        if (empty($this->db)) {
            $this->db = new medoo(array(
                    'database_type' => C('DB_TYPE', 'mysql'),
                    'database_name' => C('DB_NAME', 'test'),
                    'server'        => C('DB_HOST', 'localhost'),
                    'username'      => C('DB_USER', 'root'),
                    'password'      => C('DB_PASS', ''),
            ));
        }

        return $this->db;
    }
}
