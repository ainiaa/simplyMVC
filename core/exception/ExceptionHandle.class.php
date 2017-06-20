<?php

/**
 * @author Jeff.Liu<jeff.liu.guo@gmail.com>
 * Class ExceptionHandle
 */
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

        if (defined('LOG_RECORD') && LOG_RECORD) {
            Logger::getInstance()->log(['msg' => $e->__toString(), 'level' => SmvcLoggerInterface::LOG_EXCEPTION]);
        }
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
                $errorStr = sprintf('%s %s  第 %s 行.', $errstr, $errfile, $errline);
                if (defined('LOG_RECORD') && LOG_RECORD) {
                    Logger::getInstance()->log(['msg' => $errorStr, 'level' => SmvcLoggerInterface::LOG_ERROR]);
                }
                function_exists('halt') ? halt($errorStr) : exit('ERROR:' . $errorStr);
                break;
            case E_STRICT:
            case E_USER_WARNING:
            case E_USER_NOTICE:
            default:
                $errorStr = sprintf('[%s] %s %s 第 %s 行.', $errno, $errstr, $errfile, $errline);
                echo $errorStr;
                if (defined('LOG_RECORD') && LOG_RECORD) {
                    Logger::getInstance()->log(['msg' => $errorStr, 'level' => SmvcLoggerInterface::LOG_WARNING]);
                }
                break;
        }
    }

    /**
     * 致命错误捕获
     */
    public static function fatalError()
    {
        if ($e = error_get_last()) {
            self::appError($e['type'], $e['message'], $e['file'], $e['line']);
        }
    }

    /**
     * 设定错误和异常处理
     */
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

        register_shutdown_function(['ExceptionHandle', 'fatalError']);
        set_error_handler(['ExceptionHandle', 'appError']);
        set_exception_handler(['ExceptionHandle', 'appException']);
    }
}