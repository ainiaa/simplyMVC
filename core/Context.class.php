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
     * ����������ת
     * @author Jeff Liu<jeff.liu.guo@gmail.com>
     *
     * @param $url
     * @param $param
     */
    public function forward($url, $param)
    {

    }

    /**
     * ҳ����ת
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
     * �������������
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