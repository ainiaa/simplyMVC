<?php

/**
 * 单例化调用
 * Factory('TestDAORedis')->getByPk(123);
 * Factory('TestService')->test(123);
 * $tmp = Factory('TestService');  $tmp->test('123'); //寄存， //有调用时候再具体实例化
 */
class Factory
{
    public static $instances = [];

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
    public static function getInstance($instanceName, $quoteObj = [], $params = [], $getNow = false)
    {
        if (!is_string($instanceName) || $instanceName == '') {
            throw new Exception('$instanceName must be a string!!!');
        }
        $params     = $params ?: [];
        $instanceid = $instanceName . '_' . hash('crc32', serialize($params));
        if (!isset(self::$instances[$instanceid])) {
            if ($getNow) {
                self::$instances[$instanceid] = self::getInstanceNow($instanceName, $params);
            } else {
                $proxy         = new FactoryProxy();
                $proxy->class  = $instanceName;
                $proxy->params = $params;
                if ($quoteObj && is_array($quoteObj)) {
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

    public static function initPropert()
    {

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
    public static function getInstanceNow($name, $constructparams = [])
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
                $reflectionClass = new ReflectionClass($name);
                $action          = new $name();
                $properts        = $reflectionClass->getProperties();
                foreach ($properts as $prop) {
                    $prop               = $prop->getName();
                    $reflectionProperty = $reflectionClass->getProperty($prop);
                    $reflectionProperty->setAccessible(true);
                    if (substr($prop, -$serviceSuffixLen) === $serviceSuffix) {
                        $reflectionProperty->setValue($action, self::getInstance($prop, [$action, $prop]));
                    } else {
                        if (isset($constructparams[$prop])) {
                            $reflectionProperty->setAccessible(true);
                            $reflectionProperty->setValue($action, $constructparams[$prop]);
                        }
                    }
                }
                self::callSpecialMethod($action, '_initialize', $name);
                return $action;
            } else {
                throw new Exception('Class: ' . $name . ' is not exists!');
            }
        } else if (substr($name, -$serviceSuffixLen) === $serviceSuffix) {
//            $serviceObject   = self::getRealObject($name, $constructparams);
            $serviceObject = new $name();
            $reflectionClass = new ReflectionClass($name);
            $properts        = $reflectionClass->getProperties();
            foreach ($properts as $prop) {
                $propertName  = $prop->getName();
                $getInstance = false;
                if (substr($propertName, -$daoDBSuffixLen) === $daoDBSuffix) {
                    $getInstance = true;
                } else if (substr($propertName, -$daoRedisSuffixLen) === $daoRedisSuffix) {
                    $getInstance = true;
                } else if (substr($propertName, -$daoSuffixLen) == $daoSuffix) {
                    $getInstance = true;
                }

                if ($getInstance) {
                    $reflectionProperty = $reflectionClass->getProperty($propertName);
                    $reflectionProperty->setAccessible(true);
                    $reflectionProperty->setValue($serviceObject, self::getInstance($propertName, [$serviceObject, $propertName]));
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
                $constructparams = [];
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
            $constructparams = [$constructparams];
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