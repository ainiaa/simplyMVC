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

    public function log($info = array( /* 'msg' => '', 'level' => ''*/));

    public function error($info = array( /* 'msg' => ''*/));

    public function info($info = array( /* 'msg' => ''*/));

    public function warn($info = array( /* 'msg' => ''*/));

    public function debug($info = array( /* 'msg' => ''*/));

    public function notice($info = array( /* 'msg' => ''*/));

    public function fatal($info = array( /* 'msg' => ''*/));

    public function write($message);

    public function setDateFormat($info = array( /*'dateFormat' => ''*/));

    public function formatMessage($info = array( /*'level' => '', 'msg' => '', 'format' => ''*/));

    public function getTimestamp();

}