<?php

class RPCWrapper
{
    public $initAutoload = false;
    public static $instance = null;

    public function __construct()
    {
        $this->initAutoload();
    }

    public function autoload($className)
    {
        if (strpos($className, 'Transfer_file') === 0 || strpos($className, 'GPBMetadata') === 0) {
            $path = str_replace('\\', DIRECTORY_SEPARATOR, $className);
            $path = API_DIR . 'Vendor/resource_service/' . $path . '.php';
            require $path;
        }
    }

    public function initAutoload()
    {
        if (!$this->initAutoload) {
            include INCLUDE_DIR . 'vendor/autoload.php';
            spl_autoload_register([$this, 'autoload']);
            $this->initAutoload = true;
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param $param
     *
     * @return mixed
     */
    public function doTransferFile($param)
    {
        $client = new Transfer_file\TransferFileClient(
                RPC_HOST, [
                        'credentials' => Grpc\ChannelCredentials::createInsecure(),
                ]
        );

        $request  = new Transfer_file\TransferRequest();
        $name     = isset($param['name']) ? $param['name'] : '';
        $nodeId   = isset($param['node_id']) ? $param['node_id'] : '';
        $type     = isset($param['type']) ? $param['type'] : '';
        $fileName = isset($param['file_name']) ? $param['file_name'] : '';
        $request->setName($name);
        $request->setNodeId($nodeId);
        $request->setType($type);
        $fd       = fopen($fileName, 'rb');
        $filesize = filesize($fileName);
        $request->setSize($filesize);
        $content = $contents = fread($fd, $filesize);
        $request->setContent($content);
        $timeout    = isset($param['timeout']) ? $param['timeout'] : '30';
        $finalParam = ['method' => 'DoTransfer', 'metadata' => [], 'options' => [], 'timeout' => $timeout];

        return $this->callRPC($client, $request, $finalParam);
    }

    /**
     * @param      $client    \Grpc\BaseStub
     * @param      $request   \Google\Protobuf\Internal\Message
     * @param      $params    array
     * @param bool $autoClose boolean
     * @param int  $timeout   int
     *
     * @return mixed
     */
    public function callRPC($client, $request, $params)
    {
        $timeout       = isset($params['timeout']) ? $params['timeout'] : 3000;
        $metadata      = isset($params['metadata']) ? $params['metadata'] : [];
        $options       = isset($params['options']) ? $params['options'] : [];
        $method        = isset($params['method']) ? $params['method'] : [];
        $autoClose     = isset($params['autoClose']) ? $params['autoClose'] : true;
        $waitForServer = isset($params['waitForServer']) ? $params['waitForServer'] : true;
        try {
            $isReady = $client->waitForReady($timeout);
        } catch (Exception $e) {
            return $e;
        }
        if ($isReady) {
            /**
             * @var Grpc\ClientStreamingCall
             */
            $call = $client->$method($request, $metadata, $options);
            if ($waitForServer) {
                list($response, $status) = $call->wait();
            } else {
                $response = '';
                $status   = '';
            }

            if ($autoClose) {
                $client->close();
            }
            return ['response' => $response, 'status' => $status];
        }
        return false;
    }

}