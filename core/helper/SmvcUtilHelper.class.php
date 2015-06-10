<?php

/**
 *
 * @author Jeff Liu
 */
class SmvcUtilHelper
{

    /**
     * json encode 操作
     *
     * @param $array
     *
     * @return string
     */
    public static function encodeData($array)
    {
        return json_encode($array);
    }

    /**
     * json_decode 操作
     *
     * @param $string
     *
     * @return mixed
     */
    public static function decodeData($string)
    {
        return json_decode($string, true);
    }


    /**
     * @param        $string
     * @param string $source
     * @param string $target
     *
     * @return mixed|string
     */
    public static function replaceData($string, $source = "'", $target = '"')
    {
        if (!is_string($string)) {
            return $string;
        }
        $result = str_replace($source, $target, $string);

        $length = strlen($result);
        if (!empty($length) && '\\' == $result[$length - 1]) {
            $result .= ' ';
        }

        return $result;
    }


    /**
     * @param $name
     * @param $value
     */
    public static function def($name, $value)
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }

    public static function defArray($info)
    {
        if (is_array($info)) {
            foreach ($info as $name => $value) {
                if (!defined($name)) {
                    define($name, $value);
                }
            }
        }
    }


    /**
     * @param bool $useRequestTime
     *
     * @return int
     */
    public static function getTime($useRequestTime = true)
    {
        if ($useRequestTime) {
            return $_SERVER['REQUEST_TIME'];
        } else {
            return time();
        }
    }


    /**
     * @see    php manual gettype
     * @author Jeff Liu
     *
     * @param $value
     *
     * @return string
     */
    public static function getType($value)
    {
        if (is_array($value)) {
            return 'array';
        }
        if (is_bool($value)) {
            return 'boolean';
        }
        if (is_float($value)) {
            return 'float';
        }
        if (is_int($value)) {
            return 'integer';
        }
        if (is_null($value)) {
            return 'NULL';
        }
        if (is_numeric($value)) {
            return 'numeric';
        }
        if (is_object($value)) {
            return 'object';
        }
        if (is_resource($value)) {
            return 'resource';
        }
        if (is_string($value)) {
            return 'string';
        }
        return 'unknown type';

    }
}