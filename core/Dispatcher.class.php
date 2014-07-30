<?php

/**
 * 任务派发类
 * @author jeff liu
 */
class Dispatcher
{

    /**
     * 任务派遣方法
     * @author jeff liu
     * @throws ReflectionException
     * @return boolean
     */
    public static function dispatch()
    {
        /**
         * 解析url
         */
        Router::parseUrl();

        //在Importer里面加入一个loadController的方法 专门用来加载load
        //autoload也需要修改 需要优先加载当前group下的model 然后再加载公共model 最后是其他group下的model
        $controllerName = Router::getController();
        $controller     = self::getController($controllerName);
        $actionName     = Router::getAction();
        $params         = self::getParams($controller, $actionName);

        self::preDispatch(
                array(
                        'controller' => $controller,
                        'actionName' => $actionName,
                        'params'     => $params
                )
        );

        try {
            //执行当前操作
            $method = new ReflectionMethod($controller, $actionName);
            if ($method->isPublic()) {
                $class = new ReflectionClass($controller);
                // 前置操作 TODO 可以实现成 before 和after 操作都是在controller成员变量中配置成多个的。。。这个也可以是拦截器。。
                if ($class->hasMethod('pre' . $actionName)) {
                    $before = $class->getMethod('pre' . $actionName);
                    if ($before->isPublic()) {
                        $before->invoke($controller);
                    }
                }
                // URL参数绑定检测
                if ($method->getNumberOfParameters() > 0) {
                    $method->invokeArgs($controller, $params);
                } else {
                    $method->invoke($controller);
                }
                // 后置操作
                if ($class->hasMethod('post' . $actionName)) {
                    $after = $class->getMethod('post' . $actionName);
                    if ($after->isPublic()) {
                        $after->invoke($controller);
                    }
                }
            } else { // 操作方法不是Public 抛出异常
                throw new ReflectionException();
            }
        } catch (ReflectionException $e) {
            // 方法调用发生异常后 引导到__call方法处理
            $method = new ReflectionMethod($controller, '__call');
            $method->invokeArgs($controller, array($actionName, ''));
        }
        self::postDispatch(
                array(
                        'controller' => $controller,
                        'action'     => $actionName,
                        'params'     => $params
                )
        ); //todo 需要重新实现
        return;
    }

    /**
     * 获得controller 实例
     * @author Jeff Liu
     *
     * @param $controller_name
     *
     * @return object
     */
    public static function getController($controller_name)
    {
        $controller = Factory::getInstanceNow($controller_name);
        return $controller;
    }


    /**
     * 获得相应的param
     * @author Jeff Liu
     *
     * @param $controller
     * @param $actionName
     *
     * @return array
     */
    public static function getParams($controller, $actionName)
    {
        $finalParams = array();
        try {
            //执行当前操作
            $method = new ReflectionMethod($controller, $actionName);
            if ($method->isPublic()) {
                switch ($_SERVER['REQUEST_METHOD']) {
                    case 'POST':
                        $vars = $_POST;
                        break;
                    case 'PUT':
                        parse_str(file_get_contents('php://input'), $vars);
                        break;
                    default:
                        $vars = $_GET;
                }
                $params      = $method->getParameters();
                $finalParams = array();
                foreach ($params as $param) {
                    $name = $param->getName();
                    if (isset($vars[$name])) {
                        $finalParams[] = $vars[$name];
                    } elseif ($param->isDefaultValueAvailable()) {
                        $finalParams[] = $param->getDefaultValue();
                    } else {
                        //TODO  错误处理
                    }
                }
            }
        } catch (ReflectionException $e) { // 方法调用发生异常后 引导到__call方法处理
            $method = new ReflectionMethod($controller, '__call');
            $method->invokeArgs($controller, array($actionName, ''));
        }
        return $finalParams;
    }


    /**
     * 派遣任务前需要执行的动作
     *
     * @param array $info
     */
    private static function preDispatch($info = array())
    {
    }


    /**
     * 派遣任务后需要执行的动作
     * @author jeff liu
     *
     * @param array $info
     */
    private static function postDispatch($info = array())
    {
    }

}