<?php

/**
 *
 * SmvcBaseLogger
 *
 * Finally, a light, permissions-checking logging class.
 */
class SmvcBaseLogger implements SmvcLoggerInterface
{

    protected $dateFormat = ''; //日期格式
    protected $defaultDateFormat = 'Y-m-d H:i:s';
    protected $defaultLogLevel = 'debug';
    protected $logLevel = '';

    /**
     * Octal notation for default permissions of the log file
     * @var integer
     */
    protected $defaultPermissions = 0777;

    /**
     * Class constructor
     *
     * @param array $info
     */
    public function __construct($info = array( /*'logDir' => '', 'level' => 'debug'*/))
    {
        $this->logLevel = isset($info['level']) ? $info['level'] : $this->defaultLogLevel;
    }

    /**
     * Class destructor
     */
    public function __destruct()
    {
    }

    public function log($info = array( /* 'msg' => '', 'level' => ''*/))
    {
        $message = $this->formatMessage($info);
        $this->write($message);
    }

    public function error($info = array( /* 'msg' => ''*/))
    {
        $info['level'] = 'error';
        $this->log($info);
    }

    public function info($info = array( /* 'msg' => ''*/))
    {
        $info['level'] = 'info';
        $this->log($info);
    }

    public function warn($info = array( /* 'msg' => ''*/))
    {
        $info['level'] = 'warn';
        $this->log($info);
    }

    public function debug($info = array( /* 'msg' => ''*/))
    {
        $info['level'] = 'debug';
        $this->log($info);
    }

    public function notice($info = array( /* 'msg' => ''*/))
    {
        $info['level'] = 'notice';
        $this->log($info);
    }

    public function fatal($info = array( /* 'msg' => ''*/))
    {
        $info['level'] = 'fatal';
        $this->log($info);
    }


    /**
     * Writes a line to the log without prepending a status or timestamp
     *
     * @param $message
     *
     * @throws RuntimeException
     * @internal param string $line Line to write to the log
     *
     * @return void
     */
    public function write($message)
    {
        throw new RuntimeException('pls implements function `write` first.');
    }

    /**
     * Sets the date format used by all instances of KLogger
     *
     * @param array $info
     */
    public function setDateFormat($info = array( /*'dateFormat' => ''*/))
    {
        $this->dateFormat = isset($info['dateFormat']) ? $info['dateFormat'] : $this->defaultDateFormat; //$dateFormat;
    }

    public function formatMessage($info = array( /*'level' => '', 'msg' => '', 'format' => ''*/))
    {
        $level = isset($info['level']) ? $info['level'] : $this->logLevel;
        $msg   = isset($info['msg']) ? $info['msg'] : '';
        return "[{$this->getTimestamp()}] [{$level}] {$msg}" . PHP_EOL;
    }

    /**
     * Gets the correctly formatted Date/Time for the log entry.
     *
     * PHP DateTime is dump, and you have to resort to trickery to get microseconds
     * to work correctly, so here it is.
     *
     * @return string
     */
    public function getTimestamp()
    {
        $originalTime = microtime(true);
        $micro        = sprintf("%06d", ($originalTime - floor($originalTime)) * 1000000);
        $date         = new DateTime(date('Y-m-d H:i:s.' . $micro, $originalTime));

        return $date->format($this->dateFormat);
    }

}