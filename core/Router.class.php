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
        $urlMapping  = C('URL_MAPPING', []);
        $matchedList = [];
        if ($urlMapping) {
            $requestInfo      = Request::parseRequest();
            $originGroup      = SmvcArrayHelper::get($requestInfo, 'group');
            $originModule     = SmvcArrayHelper::get($requestInfo, 'module');
            $originController = SmvcArrayHelper::get($requestInfo, 'controller');
            $originAction     = SmvcArrayHelper::get($requestInfo, 'action');
            foreach ($urlMapping as $pattern => $mapping) {
                $matchRate = self::matchRate($pattern, $originGroup, $originModule, $originController, $originAction);
                if ($matchRate == -1) {
                    continue;
                } else {
                    if (isset($matchedList[0]) && $matchedList[0]['matchRate'] < $matchRate) {
                        $matchedList[0] = ['matchRate' => $matchRate, 'pattern' => $pattern, 'mapping' => $mapping];
                    }
                    if ($matchRate == 4) {
                        break;
                    }
                }
            }
        }
        if (count($matchedList) > 0) {
            $first = $matchedList[0]['mapping'];
            Request::mappingUri($first);
        } else {
            Request::parseRequest();
        }
    }

    /**
     * 匹配度
     *
     * @param $pattern
     * @param $mapping
     * @param $originGroup
     * @param $originModule
     * @param $originController
     * @param $originAction
     */
    public static function matchRate($pattern, $originGroup, $originModule, $originController, $originAction)
    {
        list($group, $module, $controller, $action) = each(explode('/', $pattern));
        $groupMatched      = self::match($group, $originGroup);
        $moduleMatched     = self::match($module, $originModule);
        $controllerMatched = self::match($controller, $originController);
        $actionMatched     = self::match($action, $originAction);
        if ($groupMatched === '00' || $moduleMatched === '00' || $controllerMatched === '00' || $actionMatched === '00') {//uri都匹配了 执行mapping操作
            return -1;
        } else {
            $matchStr = sprintf('%s%s%s%s', $groupMatched, $moduleMatched, $controllerMatched, $actionMatched);
            switch ($matchStr) {
                case '01010101':
                    $matchRate = 4;
                    break;
                case '01010110':
                    $matchRate = 3;
                    break;
                case '01011010':
                    $matchRate = 2;
                    break;
                case '01101010':
                    $matchRate = 1;
                    break;
                default:
                    $matchRate = 0;
            }
            return $matchRate;
        }
    }

    public static function match($data1, $data2)
    {
        $compare = '00';
        if ($data1 === $data2) {
            $compare = '01';
        } else if ($data1 === '*') {
            $compare = '10';
        }
        return $compare;
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
        self::doUrlMapping();
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

}