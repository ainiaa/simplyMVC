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

    public function log($info = [ /* 'msg' => '', 'level' => ''*/]);

    public function error($info = [ /* 'msg' => '', 'no' => '', 'param' => []*/]);

    public function info($info = [ /* 'msg' => '', 'param' => []*/]);

    public function warn($info = [ /* 'msg' => '', 'param' => []*/]);

    public function debug($info = [ /* 'msg' => '', 'param' => []*/]);

    public function notice($info = [ /* 'msg' => '', 'param' => []*/]);

    public function fatal($info = [ /* 'msg' => '', 'no' => '', 'param' => []*/]);

    public function write($message);

    public function setDateFormat($info = [ /*'dateFormat' => ''*/]);

    public function formatMessage($info = [ /*'level' => '', 'msg' => '', 'format' => ''*/]);

    public function getTimestamp();

}