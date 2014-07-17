<?php

/**
 * JSON File Driver
 */
class Json implements DriverInterface
{
    public function read($filepath)
    {
        $result = json_decode(file_get_contents($filepath), true);
        return $result ? : array();
    }
}