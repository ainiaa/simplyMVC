<?php

/**
 * 本地静态缓存
 *
 * @author Jeff.Liu<jeff.liu.guo@gmail.com>
 */
class LocalCache implements ArrayAccess
{
    /**
     * 静态实例
     * @var array
     */
    private static $cachedData = [];

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public static function getData($name, $default = false)
    {
        if (!isset(self::$cachedData[$name])) {
            $ret = $default;
        } else {
            $ret = self::$cachedData[$name];
        }

        return $ret;
    }

    /**
     * @param        $name
     * @param        $data
     * @param string $operation
     */
    public static function setData($name, $data, $operation = 'replace')
    {
        if ($operation == 'replace') {
            self::cacheSingle($name, $data);
        } else if ($operation == 'merge') {
            $finalData = self::getNeedCachedData($name, $data);
            self::cacheSingle($name, $finalData);
        }
    }

    /**
     * @param $name
     * @param $data Object
     *
     * @return array
     */
    public static function getNeedCachedData($name, $data)
    {
        $finalData = $data;
        if (is_array($data) || is_object($data)) {
            $originCached = self::getData($name);
            if (is_object($data)) {//将object转换为数组
                $data = get_object_vars($data);
            }

            if ($originCached) {
                $finalData = array_merge((array)$originCached, (array)$data);
            } else {
                $finalData = $data;
            }
        }

        return $finalData;
    }

    /**
     * 主要用于存储简单类型的值 或者
     *
     * @param $name
     * @param $data
     *
     * @return bool
     */
    public static function cacheSingle($name, $data)
    {
        self::$cachedData[$name] = $data;
        return true;
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset(self::$cachedData[$offset]);
    }

    /**
     * @param mixed $offset
     *
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return isset(self::$cachedData[$offset]) ? self::$cachedData[$offset] : null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        self::$cachedData[$offset] = $value;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset(self::$cachedData[$offset]);
    }
}