<?php

/**
 * Redis操作助手类 这个类还需要加强, 很多功能没有完成
 *
 * @author  Jeff Liu
 * @version 0.1
 */
class SmvcRedisHelper
{

    const REDIS_EXPIRE_TIME = -1;

    private static $expire = 0;

    /**
     *
     * @var array
     */
    private static $instance = array();

    private $decodedPrefix = 'RH_DECODED:';
    private $decodedPrefixLen;

    /**
     *
     * @param int   $index
     * @param array $config
     *
     * @return Redis|SmvcRedisHelper
     */
    public static function getInstance($index = 0, $config = [])
    {
        if (!isset(self::$instance[$index])) {
            self::$instance[$index] = new self($index, $config);
        }

        return self::$instance[$index];
    }

    /**
     * @param $expire
     */
    public static function setExpire($expire)
    {
        self::$expire = $expire;
    }

    public static function getExpire()
    {
        return self::$expire;
    }

    /**
     * todo 还没有实现
     *
     * @param number $uid
     */
    public static function instanceById($uid)
    {
    }

    /**
     *
     * @var Redis
     */
    private $handle = null;

    /**
     *
     * @var array
     */
    private $config;

    /**
     *
     * @var string
     */
    private $prefix;

    /**
     * RedisHelper constructor.
     *
     * @param       $index
     * @param array $config
     */
    private function __construct($index, $config = [])
    {
        if (empty($config)) {
            $config = C('REDIS');
        }
        if (isset($config['host'])) {
            $config[0] = $config;
        }
        $prefix = isset($config['prefix']) ? $config['prefix'] : '';
        if (empty($prefix)) {
            $prefix = 'smvc:';
        }
        $this->config           = $config[$index];
        $this->prefix           = $prefix;
        $this->decodedPrefixLen = strlen($this->decodedPrefix);
    }

    /**
     * 连接redis 服务器
     *
     * @return bool
     */
    private function init()
    {
        $result = true;
        if (null === $this->handle) {
            $this->handle = new Redis();

            if (isset($this->config['pconnect']) && $this->config['pconnect']) {
                $result = $this->handle->pconnect($this->config['host'], $this->config['port']);
            } else {
                $result = $this->handle->connect($this->config['host'], $this->config['port']);
            }
            if ($result) {
                if (isset($this->config['password']) && $this->config['password']) {
                    $result = $this->handle->auth($this->config['password']);
                }
            }
            if (isset($this->config['database']) && is_int($this->config['database'])) {
                $result = $this->handle->select($this->config['database']);
            }
        }
        return $result;
    }

    /**
     * @param $key
     * @param $hashKey
     * @param $value
     *
     * @return mixed|null
     */
    public function hIncrBy($key, $hashKey, $value)
    {
        $data = $this->__call('hIncrBy', [$key, $hashKey, $value]);
        return $data;
    }

    /**
     *
     * @param $key
     *
     * @return mixed|null
     */
    public function hGetAll($key)
    {
        $datas = $this->__call('hgetall', [$key]);
        if ($datas) {
            foreach ($datas as &$data) {
                $data = $this->tryDecodeData($data);
            }
        }
        return $datas;
    }

    /**
     *
     * @param $key
     * @param $field
     *
     * @return mixed|null
     */
    public function hdel($key, $field)
    {
        return $this->__call('hdel', [$key, $field]);
    }

    /**
     * @param $keys
     *
     * @return mixed|null
     */
    public function delete($keys)
    {
        $keys = func_get_args();
        return $this->del($keys);
    }

    /**
     * @param $key
     * @param $hashKeys
     *
     * @return mixed|null
     */
    public function hMGet($key, $hashKeys)
    {
        $reData = $this->__call('hmget', [$key, $hashKeys]);
        foreach ($reData as &$data) {
            $data = $this->tryDecodeData($data);
        }
        return $reData;
    }

    /**
     * @param $key
     * @param $datas
     *
     * @return mixed|null
     */
    public function hMset($key, $datas)
    {
        foreach ($datas as &$data) {
            $data = $this->tryEncodeData($data);
        }
        $reData = $this->__call('hmset', [$key, $datas]);
        return $reData;
    }

    /**
     * @param $key
     * @param $field
     * @param $data
     *
     * @return mixed|null
     */
    public function hSet($key, $field, $data)
    {
        $data   = $this->tryEncodeData($data);
        $reData = $this->__call('hset', [$key, $field, $data]);
        return $reData;
    }

    /**
     *
     * @param $key
     * @param $field
     *
     * @return mixed|null
     */
    public function hGet($key, $field)
    {
        $reData = $this->__call('hget', [$key, $field]);
        $reData = $this->tryDecodeData($reData);
        return $reData;
    }

    public function get($key)
    {
        $reData = $this->__call('get', [$key]);
        $reData = $this->tryDecodeData($reData);
        return $reData;
    }

    public function set($key, $value)
    {
        $reData = $this->tryEncodeData($value);
        $reData = $this->__call('set', [$key, $reData]);

        return $reData;
    }

    /**
     *
     * @param     $key
     * @param     $value
     * @param int $expire
     *
     * @return mixed
     */
    public function setWithExpire($key, $value, $expire = 1800)
    {
        $reData = $this->tryEncodeData($value);
        $reData = $this->__call('set', [$key, $reData]);
        if ($reData) { //设置成功 设置有效期
            $reData = $this->expire($key, $expire);
        }
        return $reData;
    }

    /**
     * @param $data
     *
     * @return bool
     */
    private function needEncode($data)
    {
        return !is_scalar($data);
    }

    /**
     * @param $data
     *
     * @return bool
     */
    private function needDecode($data)
    {
        return strpos($data, $this->decodedPrefix) === 0;
    }

    /**
     * @param $data
     *
     * @return mixed|string
     */
    private function tryDecodeData($data)
    {
        if ($this->needDecode($data)) {
            $data = substr($data, $this->decodedPrefixLen);
            $data = json_decode($data, true);
        }
        return $data;
    }

    /**
     * @param $data
     *
     * @return string
     */
    private function tryEncodeData($data)
    {
        if ($this->needEncode($data)) {
            $data = $this->decodedPrefix . json_encode($data);
        }
        return $data;
    }

    /**
     * @param $key
     *
     * @return mixed|null
     */
    public function hGets($key)
    {
        return $this->hGetAll($key);
    }

    /**
     * @param            $Output
     * @param            $ZSetKeys
     * @param array|null $Weights
     * @param string     $aggregateFunction
     *
     * @return mixed|null
     */
    public function zUnion($Output, $ZSetKeys, array $Weights = null, $aggregateFunction = 'SUM')
    {
        $datas = $this->__call('zunion', [$Output, $ZSetKeys, $Weights, $aggregateFunction]);
        return $datas;
    }

    /**
     * @param       $cmd
     * @param array $args
     *
     * @return mixed|null
     */
    public function __call($cmd, $args = [])
    {
        $flag = $this->init();
        if (!$flag) { //init失败直接报错
            trigger_error('Redis init error', E_USER_ERROR);
        }
        if (stripos($args[0], $this->prefix) === 0) {//先去除前缀
            $args[0] = substr($args[0], strlen($this->prefix));
        }
        if (!in_array($cmd, ['script', 'evalSha', 'evaluate'])) {
            $args[0] = $this->prefix . $args[0];
        }
        if ($cmd === 'evalSha' || $cmd === 'evaluate') {
            array_unshift($args[1], $this->prefix);
            $args[2] += 1;
        }
        if ($cmd == 'zunion') {
            $keys = isset($args[1]) ? $args[1] : [];
            if (is_array($keys)) {
                foreach ($keys as $index => $key) {
                    $keys[$index] = $this->prefix . $key;
                }
                $args[1] = $keys;
            }
        }

        if ($flag) {
            $ret = call_user_func_array([&$this->handle, $cmd], $args);
            return $ret;
        } else {
            return null;
        }
    }

    /**
     * @param $key
     * @param $value
     *
     * @return mixed|null
     */
    public function setnx($key, $value)
    {
        $datas = $this->__call('setnx', [$key, $value]);

        return $datas;
    }

    /**
     * @param $key
     * @param $ttl
     *
     * @return mixed|null
     */
    public function expire($key, $ttl)
    {
        $datas = $this->__call('expire', [$key, $ttl]);
        return $datas;
    }

    /**
     * @param $key
     *
     * @return mixed|null
     */
    public function ttl($key)
    {
        $datas = $this->__call('ttl', [$key]);
        return $datas;
    }

    /**
     * @param $key
     *
     * @return mixed|null
     */
    public function watch($key)
    {
        $datas = $this->__call('watch', [$key]);
        return $datas;
    }

    /**
     * @return mixed|null
     */
    public function unwatch()
    {
        $datas = $this->__call('unwatch', []);
        return $datas;
    }

    /**
     * @return mixed|null
     */
    public function multi()
    {
        $datas = $this->__call('multi', []);
        return $datas;
    }

    /**
     * @return mixed|null
     */
    public function exec()
    {
        $datas = $this->__call('exec', []);
        return $datas;
    }

    /**
     * @param $key
     *
     * @return mixed|null
     */
    public function incr($key)
    {
        $datas = $this->__call('incr', [$key]);
        return $datas;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return mixed|null
     */
    public function incrBy($key, $value)
    {
        $datas = $this->__call('incrBy', [$key, $value]);
        return $datas;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return mixed|null
     */
    public function incrByFloat($key, $value)
    {
        $datas = $this->__call('incrByFloat', [$key, $value]);
        return $datas;
    }

    /**
     * @param $key
     *
     * @return mixed|null
     */
    public function decr($key)
    {
        $datas = $this->__call('decr', [$key]);
        return $datas;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return mixed|null
     */
    public function decrBy($key, $value)
    {
        $datas = $this->__call('decrBy', [$key, $value]);
        return $datas;
    }

    /**
     * @return mixed|null
     */
    public function getLastError()
    {
        return $this->__call('getLastError');
    }

    /**
     * @param        $command
     * @param string $script
     *
     * @return mixed|null
     */
    public function script($command, $script = '')
    {
        $datas = $this->__call('script', [$command, $script]);
        return $datas;
    }

    /**
     * @param       $scriptSha
     * @param array $args
     * @param int   $numKeys
     *
     * @return mixed|null
     */
    public function evalSha($scriptSha, $args = array(), $numKeys = 0)
    {
        $datas = $this->__call('evalSha', [$scriptSha, $args, $numKeys]);
        return $datas;
    }

    /**
     * @param       $script
     * @param array $args
     * @param int   $numKeys
     *
     * @return mixed|null
     */
    public function evaluate($script, $args = array(), $numKeys = 0)
    {
        $datas = $this->__call('evaluate', [$script, $args, $numKeys]);
        return $datas;
    }

    /**
     * 构建流水号
     * @author Jeff.Liu<liuwy@imageco.com.cn>
     *
     * @param     $name
     *
     * @param int $start
     *
     * @return int|mixed|null|string
     */
    public function genreateSerialNumber($name, $start = 100000000000)
    {
        $r   = null;
        $key = 'serialno';
        $rs  = $this->multi();
        if (is_null($rs)) {
            return $rs;
        }

        $origin = $this->hGet($key, $name);
        $r      = sprintf('%.0f', $origin);
        if ($origin == 0) {
            $r = $start;
        } else {
            $r += 1;
        }

        $this->hSet($key, $name, $r);
        $this->exec();

        return $r;
    }

    /**
     * @param     $lockName
     * @param int $acquireTimeout
     * @param int $lockTimeout
     *
     * @return bool|string
     */
    public function acquireLockWithTimeoutByLua($lockName, $acquireTimeout = 0, $lockTimeout = 0)
    {
        $identifier  = com_create_guid();
        $lockName    = 'lock:' . $lockName;
        $lockTimeout = ceil($lockTimeout);

        $acquired = false;
        $end      = time() + $acquireTimeout;
        $script   = <<<'SCRIPT'
            if redis.call('exists', KEYS[1]) == 0  then
                return redis.call('setex', KEYS[1], unpack(ARGV))
            end
SCRIPT;
        while (time() < $end && !$acquired) {
            $acquired = $this->loadScriptAndExec($script, [$lockName], [$lockTimeout, $identifier]) == 'OK';
            if ($acquired) {
                return $identifier;
            }
            usleep(1);
        }

        return false;
    }

    /**
     * @param $lockName
     * @param $identifier
     *
     * @return mixed|null
     */
    public function releaseLockByLua($lockName, $identifier)
    {
        $lockName = 'lock:' . $lockName;
        $script   = <<<'SCRIPT'
            if redis.call('get', KEYS[1]) == ARGV[1]  then
                return redis.call('del', KEYS[1]) or true
            end
SCRIPT;
        return $this->loadScriptAndExec($script, [$lockName], [$identifier]);
    }

    /**
     * todo 需要优化 怎么获得 script sha，以后不需要script load 直接执行 evalsha？？？？ 写文件吗？？？
     * 可以搞一个 sha-script mapping 直接传递 sha过来
     *
     * @param       $script
     * @param array $keys
     * @param array $args
     *
     * @return mixed|null
     */
    public function loadScriptAndExec($script, $keys = [], $args = [])
    {
        $ret = null;
        $sha = $this->script('load', $script);
        if ($sha) { //加载成功
            $ret = $this->evalSha($sha, array_merge($keys, $args), count($keys));
        }

        if (is_null($ret)) {
            $ret = $this->evaluate($script, array_merge($keys, $args), count($keys));
        }

        return $ret;
    }

    /**
     * 获得lua util字符串
     *
     * @param $filePath
     *
     * @return string
     */
    private function getLuaContent($filePath)
    {
        static $fileContent;
        if (empty($fileContent[$filePath])) {
            if (file_exists($filePath)) {
                $fileContent[$filePath] = file_get_contents($filePath);
            }
        }
        if (isset($fileContent[$filePath])) {
            return $fileContent[$filePath];
        } else {
            return '';
        }
    }

    /**
     * @param array $scriptAndAlias alias:脚本别名 luaFiles：需要加载的lua文件列表(注意顺序,  再alias不存在的时候会获取指定的lua内容)
     * @param array $keys
     * @param array $args
     * @param null  $error
     *
     * @return bool|mixed|null|string
     */
    public function loadAndExecScriptWithAlias($scriptAndAlias, $keys = [], $args = [], &$error = null)
    {
        $this->multi();
        $scriptShaKey = 'script:sha';
        $alias        = $scriptAndAlias['alias'];
        $sha          = $this->hGet($scriptShaKey, $alias);
        if (empty($sha)) {
            $sha = $this->getRealSha($scriptAndAlias);
        }
        if ($sha === false) {
            return false;
        }
        $ret = $this->evalSha($sha, array_merge($keys, $args), count($keys));
        if ($ret === false) {
            $sha = $this->getRealSha($scriptAndAlias);
            $ret = $this->evalSha($sha, array_merge($keys, $args), count($keys));
        } else {
            $this->error = null;
        }
        $this->exec();
        if ($ret === false) { //lua script error
            $error       = $this->handle->getLastError();
            $this->error = $error;
            return false;
        } else {
            $this->error = null;
        }
        $ret = $this->tryDecodeData($ret);
        return $ret;
    }

    private function getRealSha($scriptAndAlias)
    {
        $scriptShaKey = 'script:sha';
        $alias        = $scriptAndAlias['alias'];
        $scriptFiles  = $scriptAndAlias['luaFiles'];
        $script       = '';
        foreach ($scriptFiles as $scriptFile) {
            $script .= PHP_EOL . self::getLuaContent($scriptFile);
        }
        $sha = $this->script('load', $script);
        if ($sha === false) { //lua script error
            $error       = $this->handle->getLastError();
            $this->error = $error;
            return false;
        }
        $r = $this->hSet($scriptShaKey, $alias, $sha);
        if ($r === false) {
            $error       = $this->handle->getLastError();
            $this->error = $error;
            return false;
        }
        return $sha;
    }

    private $error = null;

    public function getScriptError()
    {
        return $this->error;
    }


    /**
     * 获取锁
     *
     * @param string $lockName
     * @param int    $acquireTimeout
     * @param int    $lockTimeout
     *
     * @return bool|string
     */
    public function acquireLockWithTimeout($lockName, $acquireTimeout = 0, $lockTimeout = 0)
    {
        $identifier  = com_create_guid();
        $lockName    = 'lock:' . $lockName;
        $lockTimeout = ceil($lockTimeout);
        $end         = time() + $acquireTimeout;
        while (time() < $end) {
            if ($this->setnx($lockName, $identifier)) {
                $this->expire($lockName, $lockTimeout);
                return $identifier;
            } else if ($this->ttl($lockName)) {
                $this->expire($lockName, $lockTimeout);
            }
            usleep(1);
        }

        return false;
    }

    /**
     * 释放锁
     *
     * @param $lockName
     * @param $identifier
     *
     * @return bool
     */
    public function releaseLock($lockName, $identifier)
    {
        $lockName = 'lock:' . $lockName;
        while (true) {
            try {
                $this->watch($lockName);
                if ($identifier === $this->get($identifier)) {
                    $this->multi();
                    $this->delete($lockName);
                    $this->exec();
                    return true;
                }
                $this->unwatch();
                break;
            } catch (Exception $e) {
                continue;
            }
        }
        return false;
    }

    public function close()
    {
        $this->__call('quit');
    }

    public function select($index)
    {
        return $this->__call('select', [$index]);
    }

    public function type($key)
    {
        return $this->__call('type', [$key]);
    }

    public function getMultiple($keys)
    {
        return $this->__call('getMultiple', [$keys]);
    }

    public function del($keys)
    {
        $keys = func_get_args();
        return $this->__call('del', [$keys]);
    }

    public function setex($key, $ttl, $value)
    {
        return $this->__call('setex', [$key, $ttl, $value]);
    }

    public function mset($array)
    {
        $newarr = [];
        foreach ($array as $key => $value) {
            $newarr[$key] = $this->tryEncodeData($value);
        }
        return $this->__call('mset', [$newarr]);
    }

    public function append($key, $value)
    {
        return $this->__call('append', [$key, $value]);
    }

    public function getRange($key, $start, $end)
    {
        return $this->__call('getRange', [$key, $start, $end]);
    }

    public function setRange($key, $offset, $value)
    {
        return $this->__call('setRange', [$key, $offset, $value]);
    }

    public function sort($key, $option = null)
    {
        return $this->__call('sort', [$key, $option]);
    }

    public function exists($key)
    {
        return $this->__call('exists', [$key]);
    }

    public function lPush($key, $args = null)
    {
        $args = func_get_args();
        return $this->__call('lPush', [$args]);
    }

    public function rPush($key, $args)
    {
        $args = func_get_args();
        return $this->__call('rPush', [$args]);
    }

    public function lRange($key, $start, $end)
    {
        return $this->__call('lRange', [$key, $start, $end]);
    }

    public function lPop($key)
    {
        return $this->__call('lPop', [$key]);
    }

    public function rPop($key)
    {
        return $this->__call('rPop', [$key]);
    }

    public function lSize($key)
    {
        return $this->__call('lSize', [$key]);
    }

    public function lIndex($key, $index)
    {
        return $this->__call('lIndex', [$key, $index]);
    }

    public function lSet($key, $index, $value)
    {
        return $this->__call('lSet', [$key, $index, $value]);
    }

    public function lTrim($key, $start, $stop)
    {
        return $this->__call('lTrim', [$key, $start, $stop]);
    }

    public function lRem($key, $value, $count)
    {
        return $this->__call('lRem', [$key, $value, $count]);
    }

    public function lRemove($key, $value, $count)
    {
        return $this->__call('lRemove', [$key, $value, $count]);
    }

    public function lInsert($key, $position, $pivot, $value)
    {
        return $this->__call('lInsert', [$key, $position, $pivot, $value]);
    }

    public function hVals($key)
    {
        return $this->__call('hVals', [$key]);
    }

    public function hKeys($key)
    {
        return $this->__call('hKeys', [$key]);
    }

    public function hLen($key)
    {
        return $this->__call('hLen', [$key]);
    }

    public function hSetNx($key, $hashKey, $value)
    {
        $value = $this->tryEncodeData($value);
        return $this->__call('hSetNx', [$key, $hashKey, $value]);
    }

    /**
     * @param      $key
     * @param      $args
     *
     * @return mixed|null
     */
    public function sAdd($key, $args)
    {
        $args = func_get_args();
        return $this->__call('sAdd', [$args]);
    }

    public function sRem($key, $args)
    {
        $args = func_get_args();
        return $this->__call('sRem', [$args]);
    }

    public function sRemove($key, $args)
    {
        $args = func_get_args();
        return $this->__call('sRemove', [$args]);
    }


    public function sMove($srcKey, $dstKey, $member)
    {
        return $this->__call('sMove', [$srcKey, $dstKey, $member]);
    }

    public function sIsMember($key, $value)
    {
        return $this->__call('sIsMember', [$key, $value]);
    }

    public function sSize($key)
    {
        return $this->sCard($key);
    }

    public function sCard($key)
    {
        return $this->__call('sCard', [$key]);
    }

    public function sPop($key)
    {
        return $this->__call('sPop', [$key]);
    }

    public function sRandMember($key, $count = null)
    {
        return $this->__call('sRandMember', [$key, $count]);
    }

    public function sMembers($key)
    {
        return $this->__call('sMembers', [$key]);
    }

    public function sGetMembers($key)
    {
        return $this->__call('sGetMembers', [$key]);
    }

    public function zAdd($key, $args)
    {
        $args = func_get_args();
        return $this->__call('zAdd', [$args]);
    }

    public function zRange($key, $start, $end, $withscores = null)
    {
        return $this->__call('zRange', [$key, $start, $end, $withscores]);
    }

    public function zRevRange($key, $start, $end, $withscore = null)
    {
        return $this->__call('zRevRange', [$key, $start, $end, $withscore]);
    }

    public function zRem($key, $args)
    {
        $args = func_get_args();
        return $this->__call('zRem', [$args]);
    }

    public function zDelete($key, $args)
    {
        $args = func_get_args();
        return $this->__call('zDelete', [$args]);
    }

    public function zRangeByScore($key, $start, $end, array $options = [])
    {
        return $this->__call('zRangeByScore', [$key, $start, $end, $options]);
    }

    public function zCount($key, $start, $end)
    {
        return $this->__call('zCount', [$key, $start, $end]);
    }

    public function zDeleteRangeByScore($key, $start, $end)
    {
        return $this->__call('zDeleteRangeByScore', [$key, $start, $end]);
    }

    public function zDeleteRangeByRank($key, $start, $end)
    {
        return $this->__call('zDeleteRangeByRank', [$key, $start, $end]);
    }

    public function zCard($key)
    {
        return $this->__call('zCard', [$key]);
    }

    public function zScore($key, $member)
    {
        return $this->__call('zScore', [$key, $member]);
    }

    public function zRank($key, $member)
    {
        return $this->__call('zRank', [$key, $member]);
    }

    public function zRevRank($key, $member)
    {
        return $this->__call('zRevRank', [$key, $member]);
    }

    public function zIncrBy($key, $value, $member)
    {
        return $this->__call('zIncrBy', [$key, $value, $member]);
    }

    public function zInter($Output, $ZSetKeys, array $Weights = null, $aggregateFunction = 'SUM')
    {
        return $this->__call('zInter', [$Output, $ZSetKeys, $Weights, $aggregateFunction]);
    }

    public function object($string = '', $key = '')
    {
        return $this->__call('object', [$string, $key]);
    }

    public function setTimeout($key, $ttl)
    {
        return $this->__call('setTimeout', [$key, $ttl]);
    }

    public function expireAt($key, $timestamp)
    {
        return $this->__call('expireAt', [$key, $timestamp]);
    }

    public function hExists($key, $hashKey)
    {
        return $this->__call('hExists', [$key, $hashKey]);
    }

    public function sInter($args)
    {
        $args = func_get_args();
        return $this->__call('sInter', [$args]);
    }

    public function sInterStore($args)
    {
        $args = func_get_args();
        return $this->__call('sInterStore', [$args]);
    }

    public function sUnion($args)
    {
        $args = func_get_args();
        return $this->__call('sUnion', [$args]);
    }

    public function sUnionStore($dstKey, $args)
    {
        $args = func_get_args();
        return $this->__call('sUnionStore', [$args]);
    }

}