<?php

/**
 * INI File Driver
 */
class Ini implements DriverInterface
{
    public function read($filepath)
    {
        return parse_ini_file($filepath, true) ? : array();
    }
}
