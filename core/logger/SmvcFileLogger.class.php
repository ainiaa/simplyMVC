<?php

/**
 *
 * SmvcFileLogger
 *
 * Finally, a light, permissions-checking logging class.
 *
 * Usage:
 * $log = new SmvcFileLogger('/var/log/', 'debug');
 * $log->info('Returned a million search results'); //Prints to the log file
 * $log->error('Oh dear.'); //Prints to the log file
 * $log->debug('x = 5'); //Prints nothing due to current severity threshhold
 *
 */
class SmvcFileLogger extends SmvcBaseLogger
{

    protected  $dateFormat = ''; //日期格式
    protected $defaultDateFormat = 'Y-m-d H:i:s';
    protected $defaultLogLevel = 'debug';
    protected $logLevel = '';
    /**
     * Path to the log file
     * @var string
     */
    private $logFilePath = null;
    /**
     * This holds the file handle for this instance's log file
     * @var resource
     */
    private $fileHandle = null;
    /**
     * Octal notation for default permissions of the log file
     * @var integer
     */
    private $defaultPermissions = 0777;

    /**
     * Class constructor
     *
     * @param array $info
     *
     * @throws RuntimeException
     */
    public function __construct($info = array( /*'logDir' => '', 'level' => 'debug'*/))
    {
        parent::__construct($info);
        $this->logLevel = isset($info['level']) ? $info['level'] : $this->defaultLogLevel;
        $logDir         = isset($info['logDir']) ? $info['logDir'] : '';
        $logDir         = rtrim($logDir, '\\/');
        if (!file_exists($logDir)) {
            mkdir($logDir, $this->defaultPermissions, true);
        }

        $this->logFilePath = $logDir . DIRECTORY_SEPARATOR . 'log_' . date('Y-m-d') . '.txt';
        if (file_exists($this->logFilePath) && !is_writable($this->logFilePath)) {
            throw new RuntimeException('The file could not be written to. Check that appropriate permissions have been set.');
        }

        $this->fileHandle = fopen($this->logFilePath, 'a');
        if (!$this->fileHandle) {
            throw new RuntimeException('The file could not be opened. Check permissions.');
        }
    }

    /**
     * Class destructor
     */
    public function __destruct()
    {
        if ($this->fileHandle) {
            fclose($this->fileHandle);
        }
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
        if (!is_null($this->fileHandle)) {
            if (fwrite($this->fileHandle, $message) === false) {
                throw new RuntimeException('The file could not be written to. Check that appropriate permissions have been set.');
            }
        }
    }
}