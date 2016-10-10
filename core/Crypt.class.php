<?php

class Crypt
{

    /*
     * Crypto object used to encrypt/decrypt
     *
     * @var	encrypt
     */
    private static $crypter = null;

    /*
     * Hash object used to generate hashes
     *
     * @var	object
     */
    private static $hasher = null;

    /*
     * Crypto configuration
     *
     * @var	array
     */
    private static $config = array();

    private static $inited = false;

    /*
     * initialisation and auto configuration
     */
    public static function _init()
    {
        if (empty(self::$inited)) {
            self::$crypter = new Crypt_AES();
            self::$hasher  = new Crypt_Hash('sha256');

            self::$config = C('crypt', array());

            // generate random crypto keys if we don't have them or they are incorrect length
            $update = false;
            foreach (array('crypto_key', 'crypto_iv', 'crypto_hmac') as $key) {
                if (empty(self::$config[$key]) or (strlen(self::$config[$key]) % 4) != 0) {
                    $crypto = '';
                    for ($i = 0; $i < 8; $i++) {
                        $crypto .= self::safe_b64encode(pack('n', mt_rand(0, 0xFFFF)));
                    }
                    self::$config[$key] = $crypto;
                    $update             = true;
                }
            }

            // update the config if needed
            if ($update === true) {
                try {
                    SC('crypt', self::$config);
                } catch (Exception $e) {
                }
            }

            self::$crypter->enableContinuousBuffer();

            self::$hasher->setKey(self::safe_b64decode(self::$config['crypto_hmac']));
            self::$inited = true;
        }

    }


    /*
     * encrypt a string value, optionally with a custom key
     *
     * @param	string	value to encrypt
     * @param	string	optional custom key to be used for this encryption
     * @access	public
     * @return	string	encrypted value
     */
    public static function encode($value, $key = false)
    {
        self::_init();

        $key ? self::$crypter->setKey($key) : self::$crypter->setKey(
                self::safe_b64decode(self::$config['crypto_key'])
        );
        self::$crypter->setIV(self::safe_b64decode(self::$config['crypto_iv']));

        $value = self::$crypter->encrypt($value);
        return self::safe_b64encode(self::add_hmac($value));

    }

    // --------------------------------------------------------------------

    /*
     * decrypt a string value, optionally with a custom key
     *
     * @param	string	value to decrypt
     * @param	string	optional custom key to be used for this encryption
     * @access	public
     * @return	string	encrypted value
     */
    public static function decode($value, $key = false)
    {
//        return $value;
        self::_init();
        $key ? self::$crypter->setKey($key) : self::$crypter->setKey(
                self::safe_b64decode(self::$config['crypto_key'])
        );
        self::$crypter->setIV(self::safe_b64decode(self::$config['crypto_iv']));

        $value = self::safe_b64decode($value);
        if ($value = self::validate_hmac($value)) {
            return self::$crypter->decrypt($value);
        } else {
            return false;
        }
    }

    // --------------------------------------------------------------------

    private static function safe_b64encode($value)
    {
        $data = base64_encode($value);
        $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);
        return $data;
    }

    private static function safe_b64decode($value)
    {
        $data = str_replace(array('-', '_'), array('+', '/'), $value);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

    private static function add_hmac($value)
    {
        // calculate the hmac-sha256 hash of this value
        $hmac = self::safe_b64encode(self::$hasher->hash($value));

        // append it and return the hmac protected string
        return $value . $hmac;
    }

    private static function validate_hmac($value)
    {
        self::_init();
        // strip the hmac-sha256 hash from the value
        $hmac = substr($value, strlen($value) - 43);

        // and remove it from the value
        $value = substr($value, 0, strlen($value) - 43);

        // only return the value if it wasn't tampered with
        return (self::secure_compare(self::safe_b64encode(self::$hasher->hash($value)), $hmac)) ? $value : false;
    }

    private static function secure_compare($a, $b)
    {
        // make sure we're only comparing equal length strings
        if (strlen($a) !== strlen($b)) {
            return false;
        }

        // and that all comparisons take equal time
        $result = 0;
        for ($i = 0; $i < strlen($a); $i++) {
            $result |= ord($a[$i]) ^ ord($b[$i]);
        }
        return $result === 0;
    }
}


