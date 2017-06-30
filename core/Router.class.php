<?php

/**
 * Class Router
 */
class Router
{

    /**
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     */
    public static function doUrlMapping()
    {
        $urlMappingConf = C('URL_MAPPING', []);
        if ($urlMappingConf) {
            $matchedList = [];
            foreach ($urlMappingConf as $pattern => $mapping) {
                list($group, $module, $controller, $action) = each(explode('/', $pattern));
                $requestInfo       = Request::getRequestUri();
                $originGroup       = SmvcArrayHelper::get($requestInfo, 'group');
                $originModule      = SmvcArrayHelper::get($requestInfo, 'module');
                $originController  = SmvcArrayHelper::get($requestInfo, 'controller');
                $originAction      = SmvcArrayHelper::get($requestInfo, 'action');
                $groupMatched      = $group === '*' || $group === $originGroup;
                $moduleMatched     = $module === '*' || $module === $originModule;
                $controllerMatched = $controller === '*' || $controller === $originController;
                $actionMatched     = $action === '*' || $action === $originAction;
                if ($groupMatched && $moduleMatched && $controllerMatched && $actionMatched) {//uri都匹配了 执行mapping操作
                    $matchedList[$pattern] = $mapping;
                }
            }
            if (count($matchedList) > 0) { //todo 这个需要再优化 --- 根据匹配度，设置为最匹配的
                $first = array_pop($matchedList);
                Request::mappingUri($first);
            }
        }
    }

    /**
     * @param $value
     *
     * @return array|string
     */
    public static function stripSlashesDeep($value)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                if (is_array($v)) {
                    $value[$k] = self::stripSlashesDeep($v);
                } else {
                    $value[$k] = stripslashes($v);
                }
            }
        } else {
            $value = stripslashes($value);
        }

        return $value;
    }

    /**
     * URL组装 支持不同URL模式
     *
     * @param array $info
     *
     * @return string
     */
    public static function buildUrl($info)
    {
        $uri_path    = SmvcArrayHelper::get($info, 'uri_path', '');
        $uri_params  = SmvcArrayHelper::get($info, 'uri_params', []);
        $with_domain = SmvcArrayHelper::get($info, 'with_domain', false);
        return make_url($uri_path, $uri_params, $with_domain);
    }

    /**
     * 解析url
     *
     * 目前还是使用的tp的代码 还需要整理
     */
    public static function parseUrl()
    {
        self::initEnv();
    }


    /**
     * 路由检测
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     * @access public
     * @return boolean
     */
    public static function routerCheck()
    {
        $controller = Request::getControllerName(false);
        $action     = Request::getActionName(false);
        if (C('routerFilterMode', 'none') === 'whiteList') {//白名单
            $return = self::routerCheckByWhiteList($controller, $action);
        } else if (C('routerFilterMode', 'none') === 'blacklist') {//黑名单
            $return = self::routerCheckByBlackList($controller, $action);
        } else {
            $return = true;
        }

        return $return;
    }


    /**
     * router检验白名单
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     *
     * @param $controller
     * @param $action
     *
     * @return bool
     */
    public static function routerCheckByWhiteList($controller, $action)
    {
        //只有 请求的route 在白名单中才可以执行
        $whiteList = C('routerFilterWhiteList', []);
        $return    = false;
        if ($whiteList) {
            if ('*.*' === $whiteList) {
                $return = true;
            } else {
                foreach ($whiteList as $currentRoute) {
                    $currentRouteController = isset($currentRoute['controller']) ? $currentRoute['controller'] : '*';
                    $currentRouteAction     = isset($currentRoute['action']) ? $currentRoute['action'] : '*';
                    if ($currentRouteController === '*' && $currentRouteAction === '*') {
                        $return = true;
                        break;
                    } else if ($currentRouteController === '*' && $action === $currentRouteAction) {
                        $return = true;
                        break;
                    } else if ($currentRouteAction === '*' && $controller === $currentRouteController) {
                        $return = true;
                        break;
                    } else if ($currentRouteController === $controller && $action === $currentRouteAction) {
                        $return = true;
                        break;
                    }
                }
            }
        }

        return $return;
    }


    /**
     * router检验黑名单
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     *
     * @param $controller
     * @param $action
     *
     * @return bool
     */
    public static function routerCheckByBlackList($controller, $action)
    {
        //只有 请求的route 不在黑名单中才可以执行
        $blacklist = C('routerFilterBlackList', []);
        $return    = true;
        if ($blacklist) {
            if ('*.*' === $blacklist) {
                $return = false;
            } else {
                foreach ($blacklist as $currentRoute) {
                    $currentController = isset($currentRoute['controller']) ? $currentRoute['controller'] : '*';
                    $currentAction     = isset($currentRoute['action']) ? $currentRoute['action'] : '*';
                    if ($currentController === '*' && $currentAction === '*') {
                        $return = false;
                        break;
                    } else if ($currentController === '*' && $action === $currentAction) {
                        $return = false;
                        break;
                    } else if ($currentAction === '*' && $controller === $currentController) {
                        $return = false;
                        break;
                    } else if ($currentController === $controller && $action === $currentAction) {
                        $return = false;
                        break;
                    }
                }
            }
        }

        return $return;
    }

    /**
     * 初始化环境变量
     *
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     */
    public static function initEnv()
    {
        self::doUrlMapping();
    }

}