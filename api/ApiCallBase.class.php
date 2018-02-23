<?php

/**
 * Class Api
 */
abstract class ApiCallBase
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
    abstract function post($api, $data, $timeout = self::TIMEOUT, $cookie = null);

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
    abstract function get($api, $data = [], $timeout = self::TIMEOUT, $cookie = []);

    /**
     * @param        $api
     * @param string $data
     *
     * @return mixed
     */
    abstract function getUrlByApi($api, $data = '');

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

    /**
     * @param $data
     */
    public function apiReturn($data)
    {
        $result = json_decode($data, true);
        if ((int)$result['code'] === 0) {
            $this->jsonReturn($result['data']);
        } else {
            $this->jsonReturn(null, $result['code'], $result['msg']);
        }
    }

    /**
     *  成功后输出提示，且无数据
     *
     * @param $data
     */
    public function reApiReturn($data)
    {
        $result = json_decode($data, true);
        if ((int)$result['code'] === 0) {
            $this->jsonReturn(null, $result['code'], $result['data']);
        } else {
            $this->jsonReturn(null, $result['code'], $result['msg']);
        }
    }

    /**
     * json输出
     *
     * @param mixed      $data
     * @param int|number $errcode
     * @param string     $err
     */
    public function jsonReturn($data, $errcode = 0, $err = '')
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods:POST,GET,OPTIONS,PUT,DELETE');
        header('Access-Control-Allow-Headers:x-requested-with,content-type');

        $header_list = headers_list();
        error_log('$header_list:' . var_export($header_list, 1), 3, '/tmp/JSONRET');

        exit(json_encode(['code' => (int)$errcode, 'data' => $data, 'msg' => $err], JSON_UNESCAPED_UNICODE));
    }

    public function csvHeader($filename)
    {
        header("Content-type:text/csv");
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=" . $filename . ".csv");
        header('Expires:0');
        header('Pragma:public');
    }

    /**
     * @param array $csv_data
     * @param array $arrayhead
     */
    public function downloadCsvData($csv_data = [], $arrayhead = [])
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