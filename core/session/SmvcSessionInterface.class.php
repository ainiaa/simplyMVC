<?php

interface SmvcSessionInterface
{

    /**
     * create a new session
     *
     * @access    public
     * @return    void
     */
    public function create();


    // --------------------------------------------------------------------
    // generic driver methods
    // --------------------------------------------------------------------

    /**
     * destroy the current session
     *
     * @access    public
     */
    public function destroy();

    /**
     * read the session
     *
     * @access    public
     * @return    SessionDriver
     */
    public function read();

    // --------------------------------------------------------------------

    /**
     * write the session
     *
     * @access    public
     * @return    SessionDriver
     */
    public function write();

    // --------------------------------------------------------------------

    /**
     * generic driver initialisation
     *
     * @access    public
     * @return    void
     */
    public function init();

    // --------------------------------------------------------------------

    /**
     * set session variables
     *
     * @param    string|array       $name  of the variable to set or array of values, array(name => value)
     * @param                 mixed $value value
     *
     * @access    public
     */
    public function set($name, $value = null);

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
    public function get($name, $default = null);

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
    public function key($name = 'session_id');

    // --------------------------------------------------------------------

    /**
     * delete session variables
     *
     * @param    string $name name of the variable to delete
     *
     * @access    public
     */
    public function delete($name);

    // --------------------------------------------------------------------

    /**
     * force a session_id rotation
     *
     * @access    public
     *
     * @param    boolean , if true, force a session id rotation
     *
     */
    public function rotate($force = true);

    // --------------------------------------------------------------------

    /**
     * set session flash variables
     *
     * @param    string $name  name of the variable to set
     * @param    mixed  $value value
     *
     * @access    public
     */
    public function setFlash($name, $value);

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
    public function getFlash($name, $default = null, $expire = null);

    // --------------------------------------------------------------------

    /**
     * keep session flash variables
     *
     * @access    public
     *
     * @param    string $name name of the variable to keep
     *
     */
    public function keepFlash($name);

    // --------------------------------------------------------------------

    /**
     * delete session flash variables
     *
     * @param    string $name name of the variable to delete
     *
     * @access    public
     */
    public function deleteFlash($name);

    // --------------------------------------------------------------------

    /**
     * set the session flash id
     *
     * @param    string $name name of the id to set
     *
     * @access    public
     */
    public function setFlashId($name);

    // --------------------------------------------------------------------

    /**
     * get the current session flash id
     *
     * @access    public
     * @return    string    name of the flash id
     */
    public function getFlashId();
    // --------------------------------------------------------------------

    /**
     * get a runtime config value
     *
     * @param    string $name name of the config variable to get
     *
     * @access    public
     * @return  mixed
     */
    public function getConfig($name);

    // --------------------------------------------------------------------

    /**
     * set a runtime config value
     *
     * @param    string $name name of the config variable to set
     *
     * @param null      $value
     *
     * @access    public
     * @return  SessionDriver
     */
    public function setConfig($name, $value = null);

    // --------------------------------------------------------------------

    /**
     * removes flash variables marked as old
     *
     * @access    public
     * @return  void
     */
    public function cleanupFlash();

    // --------------------------------------------------------------------

    /**
     * generate a new session id
     *
     * @access    public
     * @return
     */
    public function newSessionId();

    // --------------------------------------------------------------------

    /**
     * write a cookie
     *
     * @access    public
     *
     * @param    array $payload cookie payload
     *
     * @return  void
     */
    public function setCookie($payload = array());

    // --------------------------------------------------------------------

    /**
     * read a cookie
     *
     * @access    public
     * @return
     */
    public function getCookie();

    // --------------------------------------------------------------------

    /**
     * Serialize an array
     *
     * This function first converts any slashes found in the array to a temporary
     * marker, so when it gets unserialized the slashes will be preserved
     *
     * @access    public
     *
     * @param    array $data
     *
     * @return    string
     */
    public function serialize($data);

    // --------------------------------------------------------------------

    /**
     * Unserialize
     *
     * This function unserializes a data string, then converts any
     * temporary slash markers back to actual slashes
     *
     * @access    public
     *
     * @param    array $input
     *
     * @return    string
     */
    public function unserialize($input);

    // --------------------------------------------------------------------

    /**
     * validate__config
     *
     * This function validates all global (driver independent) configuration values
     *
     * @access    public
     *
     * @param    array $config
     *
     * @return    array
     */
    public function validateConfig($config);

}


