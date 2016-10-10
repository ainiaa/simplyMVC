<?php

class CoreMemcache
{
    /**
     * @var Memcache
     */
    private $memcache = null;
    protected $memcacheserver = 0;
    protected $cache_prefix = '';
    private static $_instance = null;


    /**
     * 此方法分段获取memcache值，防止memcache过载
     * @param $key
     *
     * @return array|string
     */
    private function mget($key)
    {
        $splitnum = 200;
        if (count($key) > $splitnum) {
            $rtn = array();
            for ($sk = 0, $al = ceil(count($key) / $splitnum); $sk < $al; ++$sk) {
                $ikey = array_slice($key, $sk * $splitnum, $splitnum);
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
        if (is_null($this->memcache)) {
            $this->getConn($this->memcacheserver);
        }
        $perstring = $this->cache_prefix . $perstring;
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
                    $v                            = $this->changeout($v, 'out');
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
                return 'SMVC_array';
            } elseif ($value === '') {
                return 'SMVC_string';
            } elseif ($value === 0) {
                return 'SMVC_int';
            } elseif ($value === null) {
                return 'SMVC_null';
            } elseif ($value === false) {
                return 'SMVC_false';
            } else {
                return $value;
            }
        } elseif ($changetype == 'out') {
            if (is_string($value)) {
                if (strlen($value) < 30) {
                    if ($value === 'SMVC_array') {
                        return array();
                    } elseif ($value === 'SMVC_string') {
                        return '';
                    } elseif ($value === 'SMVC_int') {
                        return 0;
                    } elseif ($value === 'SMVC_null') {
                        return null;
                    } elseif ($value === 'SMVC_false') {
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
     * @param int $limit 设置默认时间为：7天=7*24*3600=604800 秒
     *
     * @return bool
     */
    public function set($key, $value, $limit = 604800)
    {
        if (is_null($this->memcache)) {
            $this->getConn($this->memcacheserver);
        }
        if (!$value) {
            $value = $this->changeout($value, 'in');
            if (!$value) {
                return $this->del($key);
            }
        }
        //Modify by Jerry turn false to 0 when not need use compressed set or replace value
        if ($this->memcache->replace(
                        $this->cache_prefix . $key,
                        $value,
                        is_scalar($value) ? 0 : MEMCACHE_COMPRESSED,
                        $limit
                ) || $this->memcache->set(
                        $this->cache_prefix . $key,
                        $value,
                        is_scalar($value) ? 0 : MEMCACHE_COMPRESSED,
                        $limit
                )
        ) {

            return true;
        } else {
            if ($this->memcache->set(
                    $this->cache_prefix . $key,
                    $value,
                    is_scalar($value) ? 0 : MEMCACHE_COMPRESSED,
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
        if (is_null($this->memcache)) {
            $this->getConn($this->memcacheserver);
        }
        return $this->memcache->delete($this->cache_prefix . $key);
    }

    //长连接模式下无需断开
    //    public function __destruct()
    //    {
    //        if (isset($this->memcache))
    //            $this->memcache->close();
    //    }

    /**
     * @param int    $group
     * @param string $memcacheCfg
     * @param string $groupConfig
     * @param string $cachePerfix
     *
     * @return int
     * @throws Exception
     */
    public function getConn($group = 0, $memcacheCfg = '', $groupConfig = '', $cachePerfix = '')
    {
        try {
            $this->memcache     = new Memcache;
            $this->cache_prefix = $cachePerfix;

            $server = $groupConfig['memcache'][$group];
            if (count($memcacheCfg[$server]) == 1) {
                $cr = $this->memcache->connect($memcacheCfg[$server][0]['host'], $memcacheCfg[$server][0]['port']);
                if ($cr === false) {
                    $p = array('type' => 'memcache', 'config' => $memcacheCfg[$server]);
                    throw new Exception($p, 'error Connect Server !@' . json_encode($memcacheCfg[$server]));
                }
            } else {
                foreach ($memcacheCfg[$server] as $eachmv) {
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
                            30,
                            true,
                            'memcacheErrCallback'
                    );
                }
            }
            $this->memcache->setCompressThreshold(20000, 0.2); // ed扩展 Memcached::OPT_COMPRESSION
            return 1;
        }  catch (Exception $e) {
            throw new Exception('Could not load the Memcache ');
        }
    }

    /**
     * TODO 分组功能需要实现。。。
     * @static
     * @throws Exception
     *
     * @param null $uid
     * @param null $servergroup
     * @param null $instancename
     * @param null $class
     *
     * @return CoreMemCache
     */
    public static function instance($uid = null, $servergroup = null, $instancename = null, $class = null)
    {
        $insancenameset = $instancename . '_' . $servergroup;
        //载入替代cache类
        $cacheClass = 'CoreMemCache';
        if ($cacheClass) {
            $class          = $cacheClass;
            $insancenameset = $class . '_' . $servergroup;
        }
        if (!isset(self::$_instance[$insancenameset])) {
            if (!class_exists($class)) {
                $cachefile = __DIR__ . '/' . $class . '.php';
                if (!file_exists($cachefile)) {
                    throw new Exception("Could not find Cache file: $cachefile .");
                }
                require_once $cachefile;
                if (!class_exists($class)) {
                    throw new Exception("Could not find class '$class' in file $cachefile .");
                }
            }
            $tmp                 = new $class();
            $tmp->memcacheserver = $servergroup;
            $tmp->getConn($servergroup);
            self::$_instance[$insancenameset] = $tmp;
            $tmp                              = null;
        }
        return self::$_instance[$insancenameset];
    }
}
