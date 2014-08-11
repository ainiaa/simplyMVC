<?php

/**
 *
 * @author jeff liu
 */
class SmvcUtilHelper
{
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