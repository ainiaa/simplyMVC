<?php

/**
 * Class Api
 */
class ApiCallViaGRPC extends ApiCallBase
{
    const API_SECRET = 'fjzlT9HcyumCwV4VGxKtGkwdyQC9P07p';
    const TIMEOUT = 10;

    /**
     * post方式提交
     *
     * @param      $api
     * @param      $data
     * @param int  $timeout
     *
     * @param null $cookie
     *
     * @return mixed
     */
    public function post($api, $data, $timeout = self::TIMEOUT, $cookie = null)
    {
        $data         = $this->formatData($data);
        $data['sign'] = $this->creatSign($data);
        return $this->curl($api, $timeout, 'post', $data, $cookie);
    }

    /**
     * get方式提交
     *
     * @param       $api
     * @param       $data
     * @param int   $timeout
     * @param array $cookie
     *
     * @return mixed
     */
    public function get($api, $data = [], $timeout = self::TIMEOUT, $cookie = [])
    {
        $data         = $this->formatData($data);
        $data['sign'] = $this->creatSign($data);
        return $this->curl($api, $timeout, 'get', $data, $cookie);
    }

    /**
     * 生成密钥
     *
     * @param $data
     *
     * @return string
     */
    public function creatSign($data)
    {
        $args = [];

        foreach ($data as $k => $v) {
            if ($k != 'sslcert' && $k != 'sslkey') {
                $args[$k] = $v;
            }
        }

        ksort($args, SORT_STRING);
        $string = '';
        foreach ($args as $k => $v) {
            if (is_object($v)) {
                continue;
            }
            if (is_array($v)) {
                $string .= $k . serialize($v);
            } else {
                $string .= "{$k}{$v}";
            }
        }
        return md5($string . self::API_SECRET);
    }

    /**
     * 整理数据
     *
     * @param $array
     *
     * @return array
     */
    public function formatData($array)
    {
        $return = [];
        foreach ($array as $k => $v) {
            if (isset($v)) {
                if ($v !== '') {
                    if (is_array($v)) {
                        $return[$k] = serialize($v);
                    } else {
                        $return[$k] = $v;
                    }
                }
            }
        }
        return $return;
    }

    public function getHanderByApi($api, $data = '')
    {
        $mapping = [
                'category.getList' => 'http://local.smvc.me/index.php?g=modules&m=category&c=api&a=getList',
                'category.getInfo' => 'http://local.smvc.me/index.php?g=modules&m=category&c=api&a=getInfo',
        ];
        $url     = isset($mapping[$api]) ? $mapping[$api] : '';
        if ($data) {
            $url .= '&' . http_build_query($data);
        }
        return $url;
    }

    /**
     * curl获取
     *
     * @param        $api
     * @param int    $second
     * @param string $mode
     * @param null   $data
     * @param array  $cookie
     *
     * @return mixed
     */
    public function curl($api, $second = 30, $mode = 'get', $data = null, $cookie = [])
    {
        $ch = curl_init();
        if ($mode === 'get') {
            $url = self::getHanderByApi($api, $data);
        } else {
            $url = self::getHanderByApi($api);
        }

        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_URL, $url);

        //设置header
        curl_setopt($ch, CURLOPT_HEADER, false);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($mode == 'post') {//post提交方式
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        if (!empty($cookie)) {
            curl_setopt($ch, CURLOPT_COOKIE, http_build_query($cookie, '', ';'));
        }

        //运行curl
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}