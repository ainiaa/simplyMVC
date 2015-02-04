<?php

class SmvcConf implements ArrayAccess
{
    private static $instance = null;
    private $configData = null;
    private $configFileList = array();
    private $configPath = '';

    private function __construct()
    {
        $this->initConfigPath();
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
        return isset($this->configData[$index]);
    }

    function offsetGet($index)
    {
        return isset($this->configData[$index]) ? $this->configData[$index] : null;
    }

    function offsetSet($index, $newValue)
    {
        $this->configData[$index] = $newValue;
    }

    function offsetUnset($index)
    {
        unset($this->configData[$index]);
    }

    /**
     * @param null $configFile
     */
    private function loadConfigFile($configFile = null)
    {

        if ($configFile == null) {
            $configFile = $this->configPath . 'config.inc.php';
        } else {
            $configFile = $this->configPath . $configFile;
        }

        $currentConfData = Importer::loadConfigFile($configFile);

        if (is_array($this->configData)) {
            $this->configData += $currentConfData;
        } else {
            $this->configData = $currentConfData;
        }

    }

    public static function  init($configPath, $configFileExt)
    {
        SmvcConf::instance()->loadConfigFileList($configPath, $configFileExt);
    }

    /**
     * @param        $configPath
     * @param string $configFileExt
     */
    public function loadConfigFileList($configPath, $configFileExt = 'inc.php')
    {
        $currentConfData = Importer::importConfigFile($configPath, $configFileExt);

        if (is_array($this->configData)) {
            $this->configData += $currentConfData;
        } else {
            $this->configData = $currentConfData;
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

    public function getConfFromFile($key, $configFile = '', $default = '')
    {
        if (!is_array($this->configData) || !isset($this->configData[$key])) {
            $this->loadConfigFile($configFile);
        }
        if (isset($this->configData[$key])) {
            return $this->configData[$key];
        } else {
            return $default;
        }
    }

    /**
     * @param      $key
     * @param bool $default
     *
     * @return mixed
     */
    public function get($key = null, $default = false)
    {
        if ($key == '*' || $key == null) {
            return $this->configData;
        } else if (isset($this->configData[$key])) {
            return $this->configData[$key];
        } else {
            return $default;
        }
    }

    /**
     * @param null   $key
     * @param string $value
     *
     * @return bool
     */
    public function set($key = null, $value = '')
    {
        $this->configData[$key] = $value;
        return true;
    }
}
