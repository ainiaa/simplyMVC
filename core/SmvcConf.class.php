<?php

class SmvcConf implements ArrayAccess
{
    private static $instance = null;
    private $configInfo = null;
    private $configFileList = array();
    private $configPath = '';

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * @return SmvcConf
     */
    public static function instance()
    {
        if (self::$instance == null) {
            self::$instance = new SmvcConf();
            self::$instance->initConfigPath();
        }
        return self::$instance;
    }

    /**
     * @param string $path
     */
    public function initConfigPath($path = '')
    {
        if ($path) {
            $this->configPath = $path;
        } else if (defined('CONF_PATH') && file_exists(CONF_PATH)) {
            $this->configPath = CONF_PATH;
        }
    }


    function offsetExists($index)
    {
        return isset($this->configInfo[$index]);
    }

    function offsetGet($index)
    {
        return isset($this->configInfo[$index]) ? $this->configInfo[$index] : null;
    }

    function offsetSet($index, $newValue)
    {
        $this->configInfo[$index] = $newValue;
    }

    function offsetUnset($index)
    {
        unset($this->configInfo[$index]);
    }

    private function loadConfigFile($configFile = null)
    {

        if ($configFile == null) {
            $configFile = $this->configPath . 'config.inc.php';
        } else {
            $configFile = $this->configPath . $configFile;
        }

        if (is_readable($configFile)) {
            $currentConfInfo = include $configFile;
        } else {
            throw new Exception('Could not load the config file: ' . $configFile);
        }

        if (is_array($this->configInfo)) {
            $this->configInfo += $currentConfInfo;
        } else {
            $this->configInfo = $currentConfInfo;
        }

    }

    public function getfile($index, $configFile = null)
    {
        if (!is_array($this->configFileList) || !array_key_exists($index, $this->configFileList)) {
            $configFile = $this->configPath . $configFile;
            if (is_readable($configFile)) {
                $this->configFileList[$index] = file_get_contents($configFile);
            } else {
                throw new Exception('Could not read the config file: ' . $configFile . '.');
            }
        }
        if (isset($this->configFileList[$index])) {
            return $this->configFileList[$index];
        } else {
            return null;
        }
    }

    /**
     * @param string $configFile
     *
     * @return bool
     */
    public function isConfigFileExists($configFile = '')
    {
        return file_exists($this->configPath . $configFile);
    }

    public function getConfigFilePath($configFile = '')
    {
        return $this->configPath . $configFile;
    }

    public function getConfigPath()
    {
        return $this->configPath;
    }

    public function get($key, $configFile, $default = '')
    {
        if (!is_array($this->configInfo) || !isset($this->configInfo[$key])) {
            $this->loadConfigFile($configFile);
        }
        if (isset($this->configInfo[$key])) {
            return $this->configInfo[$key];
        } else {
            return $default;
        }
    }
}
