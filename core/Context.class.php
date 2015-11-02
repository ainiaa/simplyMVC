<?php

/**
 *
 * request context
 *
 * Class Context
 */
class Context
{

    /**
     * 当面请求跳转
     * @author Jeff Liu<jeff.liu.guo@gmail.com>
     *
     * @param $url
     * @param $param
     */
    public function forward($url, $param)
    {

    }

    /**
     * 页面跳转
     * @author Jeff Liu<jeff.liu.guo@gmail.com>
     *
     * @param $url
     * @param $param
     *
     * @return array
     */
    public function redirect($url, $param)
    {
        return SmvcHttpHelper::sendGet($url, $param);
    }

    /**
     * 禁用浏览器缓存
     * @author Jeff Liu<jeff.liu.guo@gmail.com>
     */
    public function closeCache()
    {
        header('Pragma:No-cache');
        header('Cache-Control:no-cache,must-revalidate');
        header('Expires:Wed, 26 Feb 1997 08:21:57 GMT');
        header('Expires:0');
    }
}