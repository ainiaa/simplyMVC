<?php

/**
 *
 * fileLog
 *
 * Finally, a light, permissions-checking logging class.
 *
 * Usage:
 * $log = new SmvcLogger('/var/log/', 'debug');
 * $log->info('Returned a million search results'); //Prints to the log file
 * $log->error('Oh dear.'); //Prints to the log file
 * $log->debug('x = 5'); //Prints nothing due to current severity threshhold
 *
 */
interface SmvcLoggerInterface
{

    const LOG_NOTICE = 0;
    const LOG_WARNING = 1;
    const LOG_ERROR = 2;
    const LOG_DEBUG = 3;
    const LOG_FATAL = 4;
    const LOG_INFO = 5;
    const LOG_EXCEPTION = 6;

    public function log($info = array( /* 'msg' => '', 'level' => ''*/));

    public function error($info = array( /* 'msg' => '', 'no' => '', 'param' => array()*/));

    public function info($info = array( /* 'msg' => '', 'param' => array()*/));

    public function warn($info = array( /* 'msg' => '', 'param' => array()*/));

    public function debug($info = array( /* 'msg' => '', 'param' => array()*/));

    public function notice($info = array( /* 'msg' => '', 'param' => array()*/));

    public function fatal($info = array( /* 'msg' => '', 'no' => '', 'param' => array()*/));

    public function write($message);

    public function setDateFormat($info = array( /*'dateFormat' => ''*/));

    public function formatMessage($info = array( /*'level' => '', 'msg' => '', 'format' => ''*/));

    public function getTimestamp();

}