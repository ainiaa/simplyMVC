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
            throw new Exception('$instanceName must be a string!!!');
        }
        $params     = $params ?: array();
        $instanceid = $instanceName . '_' . hash('crc32', serialize($params));
        if (!isset(self::$instances[$instanceid])) {
            if ($getNow) {
                self::$instances[$instanceid] = self::getInstanceNow($instanceName, $params);
            } else {
                $proxy         = new FactoryProxy();
                $proxy->class  = $instanceName;
                $proxy->params = $params;
                if (!empty($quoteObj) && is_array($quoteObj)) {
                    if (!is_object($quoteObj[0])) {
                        throw new Exception('$quoteObj[0] must be object!!!');
                    }
                    if (isset($quoteObj[1])) {
                        if (!is_string($quoteObj[1]) || $quoteObj[1] == '') {
                            throw new Exception('$quoteObj[1] must be string!!!');
                        }
                    } else {
                        $quoteObj[1] = $instanceName;
                    }
                    $proxy->myQuote = $quoteObj;
                }
                self::$instances[$instanceid] = $proxy;
            }
        }
        return self::$instances[$instanceid];
    }

    /**
     * 立刻获得所需对象实例
     *
     * @param       $name
     * @param array $constructparams
     *
     * @return object
     * @throws Exception
     */
    public static function getInstanceNow($name, $constructparams = array())
    {
        $controllerSuffix    = 'Controller';
        $controllerSuffixLen = strlen($controllerSuffix);
        $serviceSuffix       = 'Service';
        $serviceSuffixLen    = strlen($serviceSuffix);
        $daoDBSuffix         = 'DAODb';
        $daoDBSuffixLen      = strlen($daoDBSuffix);
        $daoSuffix           = 'DAO';
        $daoSuffixLen        = strlen($daoSuffix);
        $daoRedisSuffix      = 'DAORedis';
        $daoRedisSuffixLen   = strlen($daoRedisSuffix);
        if (substr($name, -$controllerSuffixLen) === $controllerSuffix) {
            if (class_exists($name)) {
                $action = new $name();
                foreach ($action as $eachParamKey => $eachParamValue) {
                    if (substr($eachParamKey, -$serviceSuffixLen) === $serviceSuffix) {
                        $action->$eachParamKey = self::getInstance($eachParamKey, array($action, $eachParamKey));
                    } else {
                        if (isset($constructparams[$eachParamKey])) {
                            $action->$eachParamKey = $constructparams[$eachParamKey];
                        }
                    }
                }
                self::callSpecialMethod($action, '_initialize', $name);
                return $action;
            } else {
                throw new Exception('Class: ' . $name . ' is not exists!');
            }
        } else if (substr($name, -$serviceSuffixLen) === $serviceSuffix) {
            $serviceObject = self::getRealObject($name, $constructparams);
            foreach ($serviceObject as $eachParamKey1 => $eachParamValue1) {
                if (is_null($eachParamValue1)) {
                    if (substr($eachParamKey1, -$daoDBSuffixLen) === $daoDBSuffix || substr(
                                    $eachParamKey1,
                                    -$daoRedisSuffixLen
                            ) === $daoRedisSuffix || substr($eachParamKey1, -$daoSuffixLen) == $daoSuffix
                    ) {
                        $serviceObject->$eachParamKey1 = self::getInstance(
                                $eachParamKey1,
                                array($serviceObject, $eachParamKey1)
                        );
                    }
                }
            }
            self::callSpecialMethod($serviceObject, '_initialize', $name);
            return $serviceObject;
        } else if (substr($name, -$daoDBSuffixLen) === $daoDBSuffix || substr(
                        $name,
                        -$daoRedisSuffixLen
                ) === $daoRedisSuffix
        ) {
            $class = $name;
            if (!is_array($constructparams)) {
                $constructparams = array();
            }
            return self::getRealObject($class, $constructparams);
        } else {
            $class = $name;
            return self::getRealObject($class, $constructparams);
        }
    }

    /**
     * @param      $obj
     * @param      $methodName
     * @param null $className
     *
     * @return mixed
     */
    private static function callSpecialMethod($obj, $methodName, $className = null)
    {
        if (method_exists($obj, $methodName)) {
            if (is_null($className)) {
                $className = get_class($obj);
            }
            $reflectionMethod = new ReflectionMethod($className, $methodName);
            $reflectionMethod->setAccessible(true);
            return $reflectionMethod->invoke($obj);
        }
        return null;
    }

    /**
     * @param $class
     * @param $constructparams
     *
     * @return null|object
     */
    private static function getRealObject($class, $constructparams)
    {
        $realObject = null;
        if (!is_array($constructparams)) {
            $constructparams = array($constructparams);
        }
        if ($constructparams) {
            $classRef = new ReflectionClass($class);
            if ($classRef->hasMethod('__construct')) {
                $realObject = $classRef->newInstanceArgs($constructparams);
            } else {
                $realObject = $classRef->newInstance();
            }
        } else {
            $realObject = new $class();
        }

        self::callSpecialMethod($realObject, '_initialize', $class);
        return $realObject;
    }

}

/**
 * 等的托管代理类
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
            Factory::$instances[$instanceid] = Factory::getInstance($this->class, array(), $this->params, true);
        }
        if (!is_null($this->myQuote)) {
            $qclass        = $this->myQuote[0];
            $qpro          = $this->myQuote[1];
            $qclass->$qpro = Factory::$instances[$instanceid];
        }
        return call_user_func_array(array(Factory::$instances[$instanceid], $name), $args);
    }
}