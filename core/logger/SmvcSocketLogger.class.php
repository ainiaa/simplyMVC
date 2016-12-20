<?php

/**
 *
 * fileLog
 *
 * Finally, a light, permissions-checking logging class.
 *
 * Usage:
 * $log = new SmvcSocketLogger('/var/log/', 'debug');
 * $log->info('Returned a million search results'); //Prints to the log file
 * $log->error('Oh dear.'); //Prints to the log file
 * $log->debug('x = 5'); //Prints nothing due to current severity threshhold
 *
 */
class SmvcSocketLogger extends SmvcBaseLogger
{

    protected $dateFormat = ''; //日期格式
    protected $defaultDateFormat = 'Y-m-d H:i:s';
    protected $defaultLogLevel = 'debug';
    protected $logLevel = '';

    /**
     * @var string host
     */
    private $host = '127.0.0.1';

    /**
     * @var int port
     */
    private $port = 6000;

    /**
     * @var int The connection timeout, in seconds.
     */
    private $connectTimeout = 5;

    /**
     * @var int
     */
    private $sendTimeout = 5;

    /**
     * @var bool 开启数据压缩开关
     */
    private $isCompress = true;

    private $handle;

    /**
     * Class constructor
     *
     * @param array $info
     */
    public function __construct(
            $info = array( /*'host' => '127.0.0.1', 'port' => '6000', 'sendTimeout' => '5', 'connectTimeout' => 5, 'isCompress' => true*/)
    ) {
        parent::__construct($info);

        $this->logLevel = isset($info['level']) ? $info['level'] : $this->defaultLogLevel;
        if (!empty($info)) {
            $this->host           = empty($info["host"]) ? $this->host : $info["host"];
            $this->port           = empty($info["port"]) ? $this->port : $info["port"];
            $this->sendTimeout    = empty($info["sendTimeout"]) ? $this->sendTimeout : $info["sendTimeout"];
            $this->connectTimeout = empty($info["connectTimeout"]) ? $this->sendTimeout : $info["connectTimeout"];
            $this->isCompress     = empty($info["isCompress"]) ? false : $info["isCompress"];
        }
    }

    /**
     * 初始化socket
     */
    private function initSocket()
    {
        $old          = error_reporting(0);
        $errNo        = 0;
        $errMsg       = '';
        $this->handle = fsockopen($this->host, $this->port, $errNo, $errMsg, $this->connectTimeout);
        error_reporting($old);
        if ($this->handle) {
            stream_set_blocking($this->handle, 0); //0非阻塞模式
            stream_set_timeout($this->handle, $this->sendTimeout);
        } else {
            throw new RuntimeException($this->host . ':' . $this->port . ' could not be connected written to. Check that appropriate permissions have been set.');
        }
    }

    /**
     * Class destructor
     */
    public function __destruct()
    {
        if ($this->handle) {
            fclose($this->handle);
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
        $this->initSocket();
        if ($this->handle) {
            if (is_array($message)) {
                if ($this->isCompress) {
                    $data = gzcompress(json_encode($message));
                } else {
                    $data = json_encode($message);
                }
            } else if (is_scalar($message)) {
                $data = $message;
            } else {
                $data = var_export($message, 1);
            }
            fwrite($this->handle, $data);
            fclose($this->handle);
            unset($this->buffer);
        }
    }
}