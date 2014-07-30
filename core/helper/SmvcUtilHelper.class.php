<?php

/**
 *
 * @author jeff liu
 */
class SmvcUtilHelper
{
    public static function def($name, $value)
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }

    public static function getTime($useRequestTime = true)
    {
        if ($useRequestTime) {
            return $_SERVER['REQUEST_TIME'];
        } else {
            return time();
        }
    }
}