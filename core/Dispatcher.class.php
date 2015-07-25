<?php

/**
 * 任务派发类
 * @author Jeff Liu
 */
class Dispatcher
{

    /**
     * 任务派遣方法
     * @author Jeff Liu
     * @return bool
     * @throws Exception
     */
    public static function dispatch()
    {
        $checkResult = Router::routerCheck();

        if (!$checkResult) {//路由检测失败
            throw new Exception('route deny!!!');
        } else {
            //设置  usersplit
            self::setRuntimeConst();

            $uId = self::getUserId();
            LocalCache::setData('uId', $uId);
            if (C('useUserSplit', false) && $uId) {//是否使用分库功能 切 正确的获得了uid
                /**
                 * @var UserSplitService
                 */
                $userSplitService = Factory::getInstanceNow('UserSplitService');
                $userSplit        = $userSplitService->getUserSplit($uId);
            } else {
                $userSplit = null;
            }
            LocalCache::setData('userSplit', $userSplit);
        }


        //获得controller
        $controllerName = Router::getControllerName();

        //获得controller实例
        $controller = self::getControllerInstance($controllerName);

        //获得action
        $actionName = Router::getActionName();

        //获得Params
        $params = self::getParams($controller, $actionName);

        //前置操作
        self::preDispatch(
                array(
                        'controller' => $controller,
                        'actionName' => $actionName,
                        'params'     => $params
                )
        );

        //这一块使用反射是不是太重了？？？
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

        //前置操作
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
     * 设置运行时 常量 todo 还需要处理
     */
    private static function setRuntimeConst()
    {
    }

    /**
     * todo 是否还有其他方法获取uId
     * @return int
     */
    private static function getUserId()
    {
        if (isset($_SESSION['uId'])) {
            $uId = $_SESSION['uId'];
        } else if (isset($_REQUEST['uId'])) {
            $uId = $_REQUEST['uId'];
        } else if (isset($_COOKIE['uId'])) {
            $uId = $_COOKIE['uId'];
        } else {
            $uId = 0;
        }

        return $uId;
    }


    /**
     * 获得controller 实例
     * @author Jeff Liu
     *
     * @param $controller_name
     *
     * @return object
     */
    public static function getControllerInstance($controller_name)
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
                    case 'GET':
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
     * @author Jeff Liu
     *
     * @param array $info
     */
    private static function postDispatch($info = array())
    {
    }

}