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

    /**
     * @param array $info
     */
    public function log($info = array( /* 'msg' => '', 'no' => '', 'param' => array()*/))
    {
        $message = $this->formatMessage($info);
        $this->write($message);
    }

    /**
     * @param array $info
     */
    public function error($info = array( /*'msg' => '', 'no' => '', 'param' => array()*/))
    {
        $info['level'] = 'error';
        $this->log($info);
    }

    /**
     * @param array $info
     */
    public function info($info = array( /* 'msg' => '', 'param' => array()*/))
    {
        $info['level'] = 'info';
        $this->log($info);
    }

    /**
     * @param array $info
     */
    public function warn($info = array( /* 'msg' => '', 'param' => array()*/))
    {
        $info['level'] = 'warn';
        $this->log($info);
    }

    /**
     * @param array $info
     */
    public function debug($info = array( /* 'msg' => '', 'param' => array()*/))
    {
        $info['level'] = 'debug';
        $this->log($info);
    }

    /**
     * @param array $info
     */
    public function notice($info = array( /* 'msg' => '', 'param' => array()*/))
    {
        $info['level'] = 'notice';
        $this->log($info);
    }


    /**
     * @param array $info
     */
    public function fatal($info = array( /* 'msg' => '', 'no' => '', 'param' => array()*/))
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

    /**
     * 格式化 message
     *
     * @param array $info
     *
     * @return string
     */
    public function formatMessage(
            $info = array( /*'level' => '', 'msg' => '', 'no' => '', 'param' => array(), 'format' => ''*/)
    ) {
        $level = isset($info['level']) ? $info['level'] : $this->logLevel;
        $msg   = isset($info['msg']) ? $info['msg'] : '';
        $no    = isset($info['no']) ? $info['no'] : null;
        $param = isset($info['param']) ? $info['param'] : array();
        if ($param) {
            return "{$this->getTimestamp()}|{$level}|{$no}|{$msg}|" . var_export($param, 1) . PHP_EOL;
        } else {
            return "{$this->getTimestamp()}|{$level}|{$no}|{$msg}" . PHP_EOL;
        }
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