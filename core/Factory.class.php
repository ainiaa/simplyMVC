<?php

/**
 * 单例化调用
 * Factory('TestDAORedis')->getByPk(123);
 * Factory('TestService')->test(123);
 * $tmp = Factory('TestService');  $tmp->test('123'); //寄存， //有调用时候再具体实例化
 */
class Factory
{
    static $instanceStorages = array();

    //$QuoteObj = array(  引用的obj，obj的变量，引用参数  )

    /**
     * @static
     *
     * @param null       $instanceName    实例名
     * @param array      $QuoteObj        相关引用的信息
     * @param array|null $constructparams 初始化参数
     * @param bool       $getinstancenow  是否立即获取单例化
     *
     * @throws Exception
     * @return $instanceName
     *
     * return XXXService , XXXDAODb ..
     */
    static public function getInstance(
            $instanceName = null,
            $QuoteObj = array(),
            $constructparams = array(),
            $getinstancenow = false
    ) {
        if (!is_string($instanceName) || $instanceName == '') {
            throw new Exception('$instanceName must be string!!!');
        }
        $constructparams = $constructparams ? : array();
        $instanceid      = $instanceName . '_' . hash('crc32', serialize($constructparams));
        if (!isset(self::$instanceStorages[$instanceid])) {
            if ($getinstancenow) {
                self::$instanceStorages[$instanceid] = self::getInstanceNow($instanceName, $constructparams);
            } else {
                $tmp                  = new FactoryProxy();
                $tmp->class           = $instanceName;
                $tmp->constructparams = $constructparams;
                if (!empty($QuoteObj) && is_array($QuoteObj)) {
                    if (!is_object($QuoteObj[0])) {
                        throw new Exception('$p must be object!!!');
                    }
                    if (isset($QuoteObj[1])) {
                        if (!is_string($QuoteObj[1]) || $QuoteObj[1] == '') {
                            throw new Exception('$pp must be string!!!');
                        }
                    } else {
                        $QuoteObj[1] = $instanceName;
                    }
                    $tmp->myQuote = $QuoteObj;
                }
                self::$instanceStorages[$instanceid] = $tmp;
            }
        }
        return self::$instanceStorages[$instanceid];
    }

    static public function getInstanceNow($name, $constructparams = array())
    {
        if (substr($name, -7) === 'Control') {
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
            //var_dump($classRef);
        } else {
            $object = new $class();
            //var_dump($object);exit;
            return $object;
        }
    }

}

//$xxService 等的托管代理类
class FactoryProxy
{
    public $class = null;
    public $constructparams = null;
    public $myQuote = null;

    public function __call($name, $args)
    {
        $instanceid = $this->class;
        if (!is_null($this->constructparams)) {
            $instanceid = $this->class . '_' . hash('crc32', serialize($this->constructparams));
        }
        if (!isset(Factory::$instanceStorages[$instanceid]) || get_class(
                        Factory::$instanceStorages[$instanceid]
                ) == 'FactoryProxy'
        ) {
            Factory::$instanceStorages[$instanceid] = null;
            Factory::$instanceStorages[$instanceid] = Factory::getInstance(
                    $this->class,
                    array(),
                    $this->constructparams,
                    true
            );
        }
        if (!is_null($this->myQuote)) {
            $qclass        = $this->myQuote[0];
            $qpro          = $this->myQuote[1];
            $qclass->$qpro = Factory::$instanceStorages[$instanceid];
        }
        //var_dump(Factory::$instanceStorages[$instanceid]);exit;
        return call_user_func_array(array(Factory::$instanceStorages[$instanceid], $name), $args);
    }
}