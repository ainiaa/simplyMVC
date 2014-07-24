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
        if (!isset(self::$cache[$this->memcacheserver])) {
            self::$cache[$this->memcacheserver] = array();
        }

        return isset(self::$cache[$this->memcacheserver][$key][0]) ? self::$cache[$this->memcacheserver][$key][0] : null;
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
        if (!isset(self::$cache[$this->memcacheserver])) {
            self::$cache[$this->memcacheserver] = array();
        }

        self::$cache[$this->memcacheserver][$key] = array($value, $limit, $iswrite);

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
                if (isset(self::$cache[$this->memcacheserver][$k])) {
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
            if (isset(self::$cache[$this->memcacheserver][$key])) {
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
        if (isset(self::$cache[$this->memcacheserver][$key])) {
            unset(self::$cache[$this->memcacheserver][$key]);
        }

        return parent::del($key);
    }

    /**
     * 延迟执行memcache set
     */
    function __destruct()
    {
        if (isset(self::$cache[$this->memcacheserver])) {
            $cache = self::$cache[$this->memcacheserver];
            foreach ($cache as $key => $val) {
                if ($val[2] === true) {
                    parent::set($key, $val[0], $val[1]);
                }
            }
        }
    }
}
