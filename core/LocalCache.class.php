<?php

/**
 * 本地静态缓存
 *
 * @author  Jeff Liu
 * @version 0.1.0
 * 使用方式
 * $lc = LocalCache::instance("redis");
 * $lc['aa'] = 10;
 */
class LocalCache implements ArrayAccess
{
    /**
     * 静态实例
     * @var array
     */
    private static $instance = array();

    public static function instance($name)
    {
        if (!isset(self::$instance[$name])) {
            self::$instance[$name] = new LocalCache();
        }

        return self::$instance[$name];
    }

    /**
     * 缓存
     * @var array
     */
    private $cache;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->cache = array();
    }

    /**
     * Whether a offset exists
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return isset($this->cache[$offset]);
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return isset($this->cache[$offset]) ? $this->cache[$offset] : null;
    }

    /**
     * Offset to set
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->cache[$offset] = $value;
    }

    /**
     * Offset to unset
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->cache[$offset]);
    }
}