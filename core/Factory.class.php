<?php

/**
 * 单例化调用
 * Factory('TestDAORedis')->getByPk(123);
 * Factory('TestService')->test(123);
 * $tmp = Factory('TestService');  $tmp->test('123'); //寄存， //有调用时候再具体实例化
 */
class Factory
{
    public static $instances = array();

    //$QuoteObj = array(  引用的obj，obj的变量，引用参数  )

    /**
     * @static
     *
     * @param null       $instanceName 实例名
     * @param array      $quoteObj     相关引用的信息
     * @param array|null $params       初始化参数
     * @param bool       $getNow       是否立即获取单例化
     *
     * @throws Exception
     * @return $instanceName
     *
     * return XXXService , XXXDAODb ..
     */
    public static function getInstance($instanceName = null, $quoteObj = array(), $params = array(), $getNow = false)
    {
        if (!is_string($instanceName) || $instanceName == '') {
            throw new Exception('$instanceName must be string!!!');
        }
        $params     = $params ? : array();
        $instanceid = $instanceName . '_' . hash('crc32', serialize($params));
        if (!isset(self::$instances[$instanceid])) {
            if ($getNow) {
                self::$instances[$instanceid] = self::getInstanceNow($instanceName, $params);
            } else {
                $tmp         = new FactoryProxy();
                $tmp->class  = $instanceName;
                $tmp->params = $params;
                if (!empty($quoteObj) && is_array($quoteObj)) {
                    if (!is_object($quoteObj[0])) {
                        throw new Exception('$p must be object!!!');
                    }
                    if (isset($quoteObj[1])) {
                        if (!is_string($quoteObj[1]) || $quoteObj[1] == '') {
                            throw new Exception('$pp must be string!!!');
                        }
                    } else {
                        $quoteObj[1] = $instanceName;
                    }
                    $tmp->myQuote = $quoteObj;
                }
                self::$instances[$instanceid] = $tmp;
            }
        }
        return self::$instances[$instanceid];
    }

    public static function getInstanceNow($name, $constructparams = array())
    {
        if (substr($name, -10) === 'Controller') {
            if (class_exists($name)) {
                $action = new $name();
                foreach ($action as $eachp => $v) {
                    if (substr($eachp, -7) === 'Service') {
                        $action->$eachp = self::getInstance($eachp, array($action, $eachp));
                    } else {
                        if (isset($constructparams[$eachp])) {
                            $action->$eachp = $constructparams[$eachp];
                        }
                    }
                }
                return $action;
            } else {
                throw new Exception("Class: {$name} is not exists!");
            }
        } elseif (substr($name, -7) === 'Service') {
            $tmp = self::RetNewClass($name, $constructparams);
            foreach ($tmp as $pk => $pv) {
                //                echo '$pk =>',$pk,'==>$pv',var_export($pv,1),'<br />';
                if (is_null($pv)) {
                    if (substr($pk, -5) === 'DAODb' || substr($pk, -8) === 'DAORedis' || substr($pk, -3) == 'DAO') {
                        $tmp->$pk = self::getInstance($pk, array($tmp, $pk));
                    }
                }
            }
            //            exit;
            return $tmp;
        } elseif (substr($name, -5) === 'DAODb' || substr($name, -8) === 'DAORedis') {
            $class = $name;
            if (!is_array($constructparams)) {
                $constructparams = array();
            }
            return self::RetNewClass($class, $constructparams);
        } else {
            $class = $name;
        }
        return new $class($constructparams);
    }

    static private function RetNewClass($class, $constructparams)
    {
        if (!is_array($constructparams)) {
            $constructparams = array($constructparams);
        }
        if ($constructparams) {
            $classRef = new ReflectionClass($class);
            if ($classRef->hasMethod('__construct')) {
                return $classRef->newInstanceArgs($constructparams);
            } else {
                return $classRef->newInstance();
            }
        } else {
            $object = new $class();
            return $object;
        }
    }

}

//$xxService 等的托管代理类
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
        if (!isset(Factory::$instances[$instanceid]) || get_class(
                        Factory::$instances[$instanceid]
                ) == 'FactoryProxy'
        ) {
            Factory::$instances[$instanceid] = null;
            Factory::$instances[$instanceid] = Factory::getInstance(
                    $this->class,
                    array(),
                    $this->params,
                    true
            );
        }
        if (!is_null($this->myQuote)) {
            $qclass        = $this->myQuote[0];
            $qpro          = $this->myQuote[1];
            $qclass->$qpro = Factory::$instances[$instanceid];
        }
        return call_user_func_array(array(Factory::$instances[$instanceid], $name), $args);
    }
}