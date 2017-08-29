<?php

/**
 * Class Api
 */
class ApiCallViaGRPC
{
    const API_SECRET = 'fjzlT9HcyumCwV4VGxKtGkwdyQC9P07p';
    const TIMEOUT = 10;

    /**
     * post方式提交
     *
     * @param      $api
     * @param      $data
     * @param int  $second
     *
     * @param null $cookie
     *
     * @return mixed
     */
    public static function post($api, $data, $second = self::TIMEOUT, $cookie = null)
    {
        $data         = self::formatData($data);
        $data['sign'] = self::CreatSign($data);
        return self::curl($api, $second, 'post', $data, $cookie);
    }

    /**
     * get方式提交
     *
     * @param       $api
     * @param       $data
     * @param int   $second
     * @param array $cookie
     *
     * @return mixed
     */
    public static function get($api, $data = [], $second = self::TIMEOUT, $cookie = [])
    {
        $data         = self::formatData($data);
        $data['sign'] = self::CreatSign($data);
        return self::curl($api, $second, 'get', $data, $cookie);
    }

    /**
     * 生成密钥
     *
     * @param $data
     *
     * @return string
     */
    private static function CreatSign($data)
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
    private static function formatData($array)
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

    private static function getUrlByApi($api, $data = '')
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
    private static function curl($api, $second = 30, $mode = 'get', $data = null, $cookie = [])
    {
        $ch = curl_init();
        if ($mode === 'get') {
            $url = self::getUrlByApi($api, $data);
        } else {
            $url = self::getUrlByApi($api);
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


    public static function apiReturn($data)
    {

        $result = json_decode($data, true);
        if ((int)$result['code'] === 0) {
            self::jsonReturn($result['data']);
        } else {
            self::jsonReturn(null, $result['code'], $result['msg']);
        }
    }

    /**
     *  成功后输出提示，且无数据
     *
     * @param $data
     */
    public static function reApiReturn($data)
    {
        $result = json_decode($data, true);
        if ((int)$result['code'] === 0) {
            self::jsonReturn(null, $result['code'], $result['data']);
        } else {
            self::jsonReturn(null, $result['code'], $result['msg']);
        }
    }

    /**
     * json输出
     *
     * @param mixed      $data
     * @param int|number $errcode
     * @param string     $err
     */
    public static function jsonReturn($data, $errcode = 0, $err = '')
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods:POST,GET,OPTIONS,PUT,DELETE');
        header('Access-Control-Allow-Headers:x-requested-with,content-type');

        $header_list = headers_list();
        error_log('$header_list:' . var_export($header_list, 1), 3, '/tmp/JSONRET');

        exit(json_encode(['code' => (int)$errcode, 'data' => $data, 'msg' => $err], JSON_UNESCAPED_UNICODE));
    }

    public static function csv_h($filename)
    {
        header("Content-type:text/csv");
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=" . $filename . ".csv");
        header('Expires:0');
        header('Pragma:public');
    }

    public static function downloadCsvData($csv_data = [], $arrayhead = [])
    {
        $csv_string = null;
        $csv_row    = [];
        if (!empty($arrayhead)) {
            $current = [];
            foreach ($arrayhead AS $item) {
                $current[] = mb_convert_encoding($item, 'GBK', 'UTF-8');
            }
            $csv_row[] = trim(implode(",", $current), ',');
        }
        foreach ($csv_data as $key => $csv_item) {
            $current = [];
            foreach ($csv_item AS $item) {
                if (preg_match('/^(\d){15,}$/', $item)) {
                    $item = "'" . $item . "'";
                }
                $current[] = mb_convert_encoding($item, 'GBK', 'UTF-8');
            }
            $csv_row[] = trim(implode(",", $current), ',');
        }
        $csv_string = implode("\r\n", $csv_row);
        echo $csv_string;
    }
}