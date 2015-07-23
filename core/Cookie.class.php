<?php

class Cookie
{

    /**
     * @var  array  Cookie class configuration defaults
     */
    protected static $config = array(
            'expiration' => 0,
            'path'       => '/',
            'domain'     => null,
            'secure'     => false,
            'http_only'  => false,
    );

    /*
     * initialisation and auto configuration
     */
    public static function _init()
    {
        static::$config = array_merge(self::$config, C('cookie', array()));
    }

    /**
     * Gets the value of a signed cookie. Cookies without signatures will not
     * be returned. If the cookie signature is present, but invalid, the cookie
     * will be deleted.
     *
     *     // Get the "theme" cookie, or use "blue" if the cookie does not exist
     *     $theme = Cookie::get('theme', 'blue');
     *
     * @param  string $name    cookie name
     * @param   mixed $default value to return
     *
     * @return  string
     */
    public static function get($name = null, $default = null)
    {
        return Router::getCookie($name, $default);
    }

    /**
     * Sets a signed cookie. Note that all cookie values must be strings and no
     * automatic serialization will be performed!
     *
     *     // Set the "theme" cookie
     *     Cookie::set('theme', 'red');
     *
     * @param   string  $name       name of cookie
     * @param   string  $value      value of cookie
     * @param   integer $expiration lifetime in seconds
     * @param   string  $path       path of the cookie
     * @param   string  $domain     domain of the cookie
     * @param   boolean $secure     if true, the cookie should only be transmitted over a secure HTTPS connection
     * @param   boolean $http_only  if true, the cookie will be made accessible only through the HTTP protocol
     *
     * @return  boolean
     */
    public static function set(
            $name,
            $value,
            $expiration = null,
            $path = null,
            $domain = null,
            $secure = null,
            $http_only = null
    ) {
        // you can't set cookies in CLi mode
//        if (defined('IS_CGI') && IS_CGI) {
//            return false;
//        }
        $value = SimpleMVC::value($value);

        // use the class defaults for the other parameters if not provided
        is_null($expiration) and $expiration = self::$config['expiration'];
        is_null($path) and $path = self::$config['path'];
        is_null($domain) and $domain = self::$config['domain'];
        is_null($secure) and $secure = self::$config['secure'];
        is_null($http_only) and $http_only = self::$config['http_only'];

        // add the current time so we have an offset
        $expiration = $expiration > 0 ? $expiration + time() : 0;
        return setcookie($name, $value, $expiration, $path, $domain, $secure, $http_only);
    }

    /**
     * Deletes a cookie by making the value null and expiring it.
     *
     *     Cookie::delete('theme');
     *
     * @param   string  $name      cookie name
     * @param   string  $path      path of the cookie
     * @param   string  $domain    domain of the cookie
     * @param   boolean $secure    if true, the cookie should only be transmitted over a secure HTTPS connection
     * @param   boolean $http_only if true, the cookie will be made accessible only through the HTTP protocol
     *
     * @return  boolean
     * @uses    static::set
     */
    public static function delete($name, $path = null, $domain = null, $secure = null, $http_only = null)
    {
        // Remove the cookie
        unset($_COOKIE[$name]);

        // Nullify the cookie and make it expire
        return self::set($name, null, -86400, $path, $domain, $secure, $http_only);
    }
}


