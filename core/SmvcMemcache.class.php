<?php

/**
 * https://github.com/simpleframe/Simple-PHP-Framework/blob/master/classes/memcached/cache.php
 * https://github.com/JosephMoniz/php-mcache
 * https://github.com/onassar/PHP-MemcachedCache/blob/master/MemcachedCache.class.php
 *
 * Class SmvcMemcache
 */
class SmvcMemcache
{
    /**
     * @var Memcache
     */
    protected $memcache = null;
    protected $memcacheServer = 0;
    protected $cachePrefix = '';
    private static $instance = null;
    private $pconnect;
    private $group;


    /**
     * 此方法分段获取memcache值，防止memcache过载
     * @param string $key
     *
     * @return array|string
     */
    private function mget($key)
    {
        $splitNum = 200;
        if (count($key) > $splitNum) {
            $rtn = array();
            for ($sk = 0, $al = ceil(count($key) / $splitNum); $sk < $al; ++$sk) {
                $ikey = array_slice($key, $sk * $splitNum, $splitNum);
                $rtn += $this->memcache->get($ikey);
            }
        } else {
            $rtn = $this->memcache->get($key);
        }

        return $rtn;
    }

    /**
     * @param        $key
     * @param string $perstring
     *
     * @return array|bool|null
     */
    public function get($key, $perstring = '')
    {
        $perstring = $this->cachePrefix . $perstring;
        if (is_string($key)) {
            $v = $this->memcache->get($perstring . $key);
            return $this->changeout($v, 'out');
        } elseif (is_array($key) && $perstring) {
            $newa   = $newout = array();
            $lenpre = strlen($perstring);
            foreach ($key as $ek) {
                $newa[] = $perstring . $ek;
            }
            $out = $this->mget($newa);
            if ($out) {
                foreach ($out as $ek => $v) {
                    $v = $this->changeout($v, 'out');
                    $newout[substr($ek, $lenpre)] = $v;
                }
            }

            return $newout;
        } elseif (is_array($key)) {
            $out    = $this->mget($key);
            $newout = array();
            if ($out) {
                foreach ($out as $ek => $v) {
                    $v           = $this->changeout($v, 'out');
                    $newout[$ek] = $v;
                }
            }

            return $newout;
        } else {
            $v = $this->memcache->get($perstring . $key);
            return $this->changeout($v, 'out');
        }
    }

    /**
     * @param $value
     * @param $changetype
     *
     * @return bool|null|string
     */
    public function changeout($value, $changetype)
    {
        if ($changetype == 'in') {
            if ($value === array()) {
                return 'SC:ARR';
            } elseif ($value === '') {
                return 'SC:STR';
            } elseif ($value === 0) {
                return 'SC:INT';
            } elseif ($value === null) {
                return 'SC:NUL';
            } elseif ($value === false) {
                return 'SC:FLS';
            } else {
                return $value;
            }
        } elseif ($changetype == 'out') {
            if (is_string($value)) {
                if (strlen($value) < 30) {
                    if ($value === 'SC:ARR') {
                        return array();
                    } elseif ($value === 'SC:STR') {
                        return '';
                    } elseif ($value === 'SC:INT') {
                        return 0;
                    } elseif ($value === 'SC:NUL') {
                        return null;
                    } elseif ($value === 'SC:FLS') {
                        return false;
                    } else {
                        return $value;
                    }
                }
            }
        }
        return $value;
    }

    /**
     * @param     $key
     * @param     $value
     * @param int $limit
     *
     * @return bool
     */
    public function set($key, $value, $limit = 2160000)
    {
        if (!$value) {
            $value = $this->changeout($value, 'in');
            if (!$value) {
                return $this->del($key);
            }
        }
        if ($this->memcache->replace(
                        $this->cachePrefix . $key,
                        $value,
                        is_scalar($value) ? false : MEMCACHE_COMPRESSED,
                        $limit
                ) || $this->memcache->set(
                        $this->cachePrefix . $key,
                        $value,
                        is_scalar($value) ? false : MEMCACHE_COMPRESSED,
                        $limit
                )
        ) {

            return true;
        } else {
            if ($this->memcache->set(
                    $this->cachePrefix . $key,
                    $value,
                    is_scalar($value) ? false : MEMCACHE_COMPRESSED,
                    $limit
            )
            ) {

                return true;
            } else {
                return $this->del($key);
            }
        }
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function del($key)
    {
        return $this->memcache->delete($this->cachePrefix . $key);
    }

    /**
     *
     */
    public function __destruct()
    {
        if (isset($this->memcache) && !is_null($this->memcache) && !$this->pconnect) {
            $this->memcache->close();
        }
    }

    /**
     * @param $config
     * @param $group
     *
     * @return int
     * @throws Exception
     */
    public function getConn($config, $group)
    {
        try {
            $this->memcache    = new Memcache();
            $cacheConfig       = $config['memcache'];
            $groupConfig       = $config['group'];
            $this->cachePrefix = $config['cache_perfix'];
            $this->group       = $group;

            $server = $groupConfig['memcache'][$group];
            if (count($cacheConfig[$server]) == 1) {
                $cr = $this->memcache->addServer(
                        $cacheConfig[$server][0]['host'],
                        $cacheConfig[$server][0]['port'],
                        false,
                        1,
                        1,
                        60,
                        true,
                        'memcacheErrCallback'
                );
                if ($cr === false) {
                    $p = array('type' => 'memcache', 'config' => $cacheConfig[$server]);
                    throw new Exception($p, 'error Connect Server !@' . json_encode($cacheConfig[$server]));
                }
            } else {
                foreach ($cacheConfig[$server] as $eachmv) {
                    $weight = 1;
                    if (isset($eachmv['weight']) && $eachmv['weight']) {
                        $weight = $eachmv['weight'];
                    }
                    $this->memcache->addServer(
                            $eachmv['host'],
                            $eachmv['port'],
                            false,
                            $weight,
                            1,
                            60,
                            true,
                            'memcacheErrCallback'
                    );
                }
            }
            $this->memcache->setCompressThreshold(20000, 0.2); // ed扩展 Memcached::OPT_COMPRESSION
            return 1;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @static
     * @throws Exception
     *
     * @param null $serverGroup
     * @param null $instanceName
     * @param null $class
     *
     * @return SmvcMemcache
     */
    public static function instance($serverGroup = null, $instanceName = null, $class = null)
    {
        if (is_null($instanceName)) {
            $instanceName = 'SmvcMemcache';
        }
        if (is_null($class)) {
            $class = 'SmvcMemcache';
        }

        $instanceNameSet = $instanceName . '_' . $serverGroup;

        if (!isset(self::$instance[$instanceNameSet])) {
            $tmp                 = new $class();
            $tmp->memcacheserver = $serverGroup;
            $tmp->memcacheserver->getConn($serverGroup);
            self::$instance[$instanceNameSet] = $tmp;
            $tmp                              = null;
        }
        return self::$instance[$instanceNameSet];
    }
}

if (!function_exists('memcacheErrCallback')) {
    function memcacheErrCallback($ip, $port, $rrtp = 0, $errstring = '', $t = 0)
    {
        throw new Exception(false, 'error Connect Server (Memcache::addServer) !@' . json_encode(func_get_args()));
    }
}

