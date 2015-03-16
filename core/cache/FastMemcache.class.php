<?php

/**
 * 具有本地缓存的Memcache实现
 *
 * @author  Jeff Liu
 * @package cache
 */
class FastMemcache extends SmvcMemcache
{
    /**
     * 本地缓存
     *
     * @var array
     */
    private static $cache = array();

    /**
     * 获得本地缓存项
     *
     * @param string $key
     *
     * @return mixed
     */
    private function qget($key)
    {
        if (!isset(self::$cache[$this->memcacheServer])) {
            self::$cache[$this->memcacheServer] = array();
        }

        return isset(self::$cache[$this->memcacheServer][$key][0]) ? self::$cache[$this->memcacheServer][$key][0] : null;
    }

    /**
     * 设置本地缓存项目
     *
     * @param string $key
     * @param mixed  $value
     * @param int    $limit
     * @param bool   $iswrite
     *
     * @return int
     */
    private function qset($key, $value, $limit = 2160000, $iswrite = false)
    {
        if (!isset(self::$cache[$this->memcacheServer])) {
            self::$cache[$this->memcacheServer] = array();
        }

        self::$cache[$this->memcacheServer][$key] = array($value, $limit, $iswrite);

        return 1;
    }

    /**
     * 从Memcache读取数据
     *
     * @param string $key
     * @param string $perstring
     *
     * @return mixed
     */
    public function get($key, $perstring = '')
    {
        if (is_array($key)) {
            $ret   = array();
            $losts = array();
            foreach ($key as $k) {
                if (isset(self::$cache[$this->memcacheServer][$k])) {
                    $ret[$k] = $this->qget($k);
                } else {
                    $losts[] = $k;
                }
            }
            if ($losts) {
                $lostdatas = parent::get($losts, $perstring); //丢失的Key
                foreach ($lostdatas as $k => $v) {
                    $this->qset($k, $v);
                }

                $ret = array_merge($ret, $lostdatas);
            }

            return $ret;
        } else {
            if (isset(self::$cache[$this->memcacheServer][$key])) {
                return $this->qget($key);
            } else { //未命中本地缓存
                $data = parent::get($key, $perstring);
                $this->qset($key, $data);

                return $data;
            }
        }
    }

    /**
     * 设置缓存项
     *
     * @param string $key
     * @param mixed  $value
     * @param int    $limit
     *
     * @return bool
     */
    public function set($key, $value, $limit = 2160000)
    {
        return $this->qset($key, $value, $limit, true);
    }

    /**
     * 删除缓存项目
     *
     * @param string $key
     *
     * @return mixed
     */
    public function del($key)
    {
        if (isset(self::$cache[$this->memcacheServer][$key])) {
            unset(self::$cache[$this->memcacheServer][$key]);
        }

        return parent::del($key);
    }

    /**
     * 延迟执行memcache set
     */
    function __destruct()
    {
        if (isset(self::$cache[$this->memcacheServer])) {
            $cache = self::$cache[$this->memcacheServer];
            foreach ($cache as $key => $val) {
                if ($val[2] === true) {
                    parent::set($key, $val[0], $val[1]);
                }
            }
        }
    }
}
