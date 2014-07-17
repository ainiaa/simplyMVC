<?php

/**
 *
 * @author jeff liu
 */
class SmvcDebug implements ArrayAccess
{
    private static $instance = null;
    private $debugInfo = null;
    private $debugInstance = null;
    private $_isdebug = false;

    private function __construct()
    {
    }

    function offsetExists($index)
    {
        return isset($this->debugInfo[$index]);
    }

    function offsetGet($index)
    {
        return isset($this->debugInfo[$index]) ? $this->debugInfo[$index] : null;
    }

    function offsetShow($index = null)
    {
        if ($index && isset($this->debugInfo[$index])) {
            foreach ($this->debugInfo[$index] as $ed) {
                var_dump($ed);
                echo '<br/>';
            }
        } else {
            if ($this->debugInfo) {
                foreach ($this->debugInfo as $key => $debuginfo) {
                    echo '<b>' . $key . '</b> :<br/>';
                    foreach ($debuginfo as $ed) {
                        var_dump($ed);
                        echo '<br/>';
                    }
                }
            }
        }
    }

    function offsetCount($index = null)
    {
        if ($index && isset($this->debugInfo[$index])) {
            return count($this->debugInfo[$index]);
        } else {
            return count($this->debugInfo);
        }
    }

    function offsetSet($index, $newValue)
    {
        if ($index != '') {
            $this->debugInfo[$index][] = $newValue;
        } else {
            $this->debugInfo[] = $newValue;
        }
    }

    function offsetUnset($index)
    {
        unset($this->debugInfo[$index]);
    }

    /**
     * @static
     * @return object
     */
    public static function instance()
    {
        if (self::$instance == null) {
            $instance = new SmvcDebug();
            $bool     = false;
            if (defined('SMVC_DEBUG') && SMVC_DEBUG) {
                include ROOT_PATH . '/include/vendor/FirePHP.class.php'; //FireBug Firephp 调试类
                $bool = true;
            }

            $instance->_isdebug = $bool;
            if ($instance->_isdebug) {
                $instance->debugInstance = FirePHP::getInstance(true);
                $instance->debugInstance->setEnabled($bool);
            }
            self::$instance = $instance;
        }
        return self::$instance;
    }

    /**
     * @param array $param
     *
     * @return bool
     */
    public function debug($param = array())
    {
        $info       = isset($param['info']) ? $param['info'] : ''; //这个可以为数组 也可以为字符串
        $label      = isset($param['label']) ? $param['label'] : '';
        $debugLevel = isset($param['level']) ? $param['level'] : ''; //
        $options    = isset($param['options']) ? $param['options'] : ''; //
        if (!$this->_isdebug) {
            return false;
        }
        $debugLevel = strtolower($debugLevel); //默认应该是log
        if (!in_array($debugLevel, array('log', 'info', 'error', 'warn'), true)) {
            $debugLevel = 'dump';
        }
        return $this->debugInstance->$debugLevel($info, $label, $options);
    }
}