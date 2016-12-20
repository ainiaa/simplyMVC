<?php

/**
 * 托管代理类
 * Class FactoryProxy
 */
class FactoryProxy
{
    public $class = null;
    public $params = null;
    public $myQuote = null;

    public function __call($name, $args)
    {
        $instanceid = $this->class;
        if (!is_null($this->params)) {
            $instanceid = $this->class . '_' . hash('crc32', serialize($this->params));
        }
        if (!isset(Factory::$instances[$instanceid]) || get_class(Factory::$instances[$instanceid]) == get_class(
                        $this
                )
        ) {
            Factory::$instances[$instanceid] = null;
            Factory::$instances[$instanceid] = Factory::getInstance($this->class, [], $this->params, true);
        }
        if (!is_null($this->myQuote)) {
            $qclass        = $this->myQuote[0];
            $qpro          = $this->myQuote[1];
            $qclass->$qpro = Factory::$instances[$instanceid];
        }
        return call_user_func_array([Factory::$instances[$instanceid], $name], $args);
    }
}