<?php

class ExceptionHandle
{
    /**
     * 自定义异常处理
     * @access public
     *
     * @param mixed $e 异常对象
     */
    public static function appException($e)
    {
        echo $e->__toString();
    }

    /**
     * 自定义错误处理
     * @access public
     *
     * @param int    $errno   错误类型
     * @param string $errstr  错误信息
     * @param string $errfile 错误文件
     * @param int    $errline 错误行数
     *
     * @return void
     */
    public static function appError($errno, $errstr, $errfile, $errline)
    {
        switch ($errno) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                ob_end_clean();
                // 页面压缩输出支持
                if (defined('OUTPUT_ENCODE') && OUTPUT_ENCODE) {
                    $zlib = ini_get('zlib.output_compression');
                    if (empty($zlib)) {
                        ob_start('ob_gzhandler');
                    }
                }
                $errorStr = "$errstr " . $errfile . " 第 $errline 行.";
                if (defined('LOG_RECORD') && LOG_RECORD) {
                    //                Log::write("[$errno] " . $errorStr, Log::ERR);
                }
                function_exists('halt') ? halt($errorStr) : exit('ERROR:' . $errorStr); //todo halt方法不存在
                break;
            case E_STRICT:
            case E_USER_WARNING:
            case E_USER_NOTICE:
            default:
                $errorStr = "[$errno] $errstr " . $errfile . " 第 $errline 行.";
                echo $errorStr;
                break;
        }
    }

    // 致命错误捕获
    public static function fatalError()
    {
        if ($e = error_get_last()) {
            self::appError($e['type'], $e['message'], $e['file'], $e['line']);
        }
    }

    public static function init()
    {
        $isDebugMode = C('smvcDebug');
        if ($isDebugMode) {
            error_reporting(E_ALL);
            ini_set('display_errors', 'on');
        } else {
            error_reporting(0);
            ini_set('display_errors', 'off');
        }

        register_shutdown_function(array('ExceptionHandle', 'fatalError'));
        set_error_handler(array('ExceptionHandle', 'appError'));
        set_exception_handler(array('ExceptionHandle', 'appException'));
    }
}