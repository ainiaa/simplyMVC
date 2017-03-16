<?php

/**
 * http相关功能类
 * @author Jeff.Liu<jeff.liu.guo@gmail.com>
 * Class SmvcHttpHelper
 */
class SmvcHttpHelper
{
    /**
     * 请求公共处理逻辑
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     *
     * @param string $url
     * @param array  $data
     * @param string $type 请求类型(POST,GET)
     *
     * @return array
     */
    public static function request($url, $data, $type = 'get')
    {
        $lst = [];
        $ch  = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (strtolower($type) == 'post') {
            $params = '';
            if (empty($data)) {
                $params = http_build_query($data);
            }
            curl_setopt($ch, CURLOPT_POST, 1); //启用POST提交
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params); //POST数据
        }

        $lst['rst']  = curl_exec($ch);
        $lst['info'] = curl_getinfo($ch);
        curl_close($ch);

        return $lst;
    }

    /**
     * 发送 post请求
     *
     * @param $url
     * @param $data
     *
     * @return array
     */
    public static function sendPost($url, $data)
    {
        return self::request($url, $data, 'post');
    }

    /**
     * 发送  get请求
     *
     * @param      $url
     * @param null $data
     *
     * @return array
     */
    public static function sendGet($url, $data = null)
    {
        if (!is_null($data)) {
            $urlComponent = parse_url($url, PHP_URL_QUERY);
            $urlQuery     = isset($urlComponent['query']) ? $urlComponent['query'] : '';
            if ($urlQuery) {
                parse_str($urlQuery, $urlQueryParam);
                $param = array_merge($data, $urlQueryParam);
                [
                        'scheme' => 'https',
                        'host'   => 'www.baidu.com',
                        'path'   => '/pathto/bac/index.php',
                        'query'  => 'a=aa&b=bb&c=cc',
                ];
                $url = $urlComponent['scheme'] . '://' . $urlComponent['host'] . '/' . $urlComponent['path'] . '?' . http_build_query(
                                $param
                        );
            }
        }

        return self::request($url, null, 'get');
    }
}