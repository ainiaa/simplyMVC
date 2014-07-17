<?php

/**
 * YAML File Driver
 * YAML 官网 http://www.yaml.org/
 */
class Yaml implements DriverInterface
{
    public function __construct()
    {
        if (!class_exists("Spyc")) {
            throw new Exception("Missing Spyc dependency.");
        }
    }

    // --------------------------------------------------------------

    public function read($filepath)
    {
        try {
            return Spyc::YAMLLoad($filepath) ? : array();
        } catch (Exception $e) {
            return array();
        }
    }
}