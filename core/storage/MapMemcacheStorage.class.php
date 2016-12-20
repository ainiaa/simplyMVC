<?php

class MapMemcacheStorage
{

    /**
     * @var Memcached
     */
    private static $mcResource;
    /**
     * @var Memcached
     */
    private static $mcHandle;
    /**
     * @var string
     */
    private static $mcKey;
    /**
     * @var int
     */
    private static $expire;
    /**
     * @var string
     */
    private static $mapPrefix;
    private static $clientServer;

    private static $mcCaches;
    private static $doSaveList;
    private static $mcCachesCount;

    public static function __callStatic($method, $params)
    {
        $param = $params[0];
        if (empty($param['key'])) {
            return Logger::getInstance()->error(
                    ['msg' => 'MMS004', 'no' => 'MMS004', 'param' => ['paramString' => 'MMS004']]
            );
        }

        self::mcHandle($param['key']);
        $param['key'] = self::$mcKey;

        if (!self::$mcHandle) {
            return Logger::getInstance()->error(
                    ['msg' => 'MMS005', 'no' => 'MMS005', 'param' => ['paramString' => 'MMS005']]
            );
        }

        $result = self::$method($param);
        return $result;
    }

    public static function unityWriteMc()
    {
        if (empty(self::$doSaveList)) {
            return true;
        }

        foreach (self::$doSaveList as $key => $value) {
            if (!isset(self::$mcCaches[$key]) || !isset($value['clientServer'])) {
                # No value or clientServer! log
                continue;
            }
            if (!isset(self::$mcResource[$value['clientServer']])) {
                # No mcHandle! log
                continue;
            }

            /**
             * @var Memcached
             */
            $mcHandle = self::$mcResource[$value['clientServer']];

            $result = self::setMc($mcHandle, $key, self::$mcCaches[$key], $value['expire']);
            if (empty($result)) {
                # 2nd save wrong! del it!
                $result = $mcHandle->delete($key);
            }
        }

        return true;
    }

    /**
     * @param Memcached $mcHandle
     * @param string    $key
     * @param string    $value
     * @param int       $expire
     *
     * @return mixed
     */
    private static function setMc($mcHandle, $key, $value, $expire)
    {
        if ('Memcached' == C('MEMCACHE_LIB_TYPE', 'Memcached')) {
            $result = $mcHandle->set($key, $value, $expire);
            if (false === $result) {
                $result = $mcHandle->set($key, $value, $expire);
            }
        } else {
            $result = $mcHandle->set($key, $value, false, $expire);
            if (false === $result) {
                $result = $mcHandle->set($key, $value, false, $expire);
            }
        }
        return $result;
    }

    private static function fetch($param)
    {
        $key = $param['key'];

        if (isset(self::$mcCaches[$key])) {
            return self::$mcCaches[$key];
        }

        $result = self::$mcHandle->get($key);
        if (false === $result) {
            $result = self::$mcHandle->get($key);
        }
        $result               = self::translate($result);
        self::$mcCaches[$key] = $result;

        return $result;
    }

    # NO the case!
    private static function fetchMany($param)
    {
        $key = $param['key'];

        $keyArray = $param['keys'];
        if (empty($keyArray)) {
            return null;
        }

        if (is_string($keyArray)) {
            $keyArray = explode(',', $keyArray);
        }
        foreach ($keyArray as &$keyId) {
            $keyId = self::$mapPrefix . '/' . $keyId;
        }

        if ('Memcached' == C('MEMCACHE_LIB_TYPE', 'Memcached')) {
            $result = self::$mcHandle->getMulti($keyArray);
        } else {
            $result = self::$mcHandle->get($keyArray);
        }

        if (is_array($result)) {
            $tmp = [];
            foreach ($result as $keyId => $info) {
                $splitKey          = SC::splitMapKey($keyId);
                $tmp[$splitKey[1]] = $info;
            }
            $result = $tmp;
        }

        return $result;
    }

    /**
     * 转换读取MC的数据格式
     *
     * @param $value
     *
     * @return array|bool|int|null|string
     */
    private static function translate($value)
    {
        if (!is_string($value) || strlen($value) >= 30) {
            return $value;
        }

        if ($value === 'J7PHPFCSxzpA2ucmu_array') {
            return [];
        } elseif ($value === 'J7PHPFCSxzpA2ucmu_string') {
            return '';
        } elseif ($value === 'J7PHPFCSxzpA2ucmu_int') {
            return 0;
        } elseif ($value === 'J7PHPFCSxzpA2ucmu_null') {
            return null;
        } elseif ($value === 'J7PHPFCSxzpA2ucmu_false') {
            return false;
        } else {
            return $value;
        }
    }

    private static function save($param)
    {
        $key = $param['key'];
        if (!isset($param['value'])) {
            return false;
        }
        self::$mcCaches[$key]   = $param['value'];
        self::$doSaveList[$key] = ['expire' => self::$expire, 'clientServer' => self::$clientServer];
        empty(self::$mcCachesCount[$key]) && self::$mcCachesCount[$key] = 0;
        self::$mcCachesCount[$key]++;

        # 本次请求对同一段数据有超过2次及以上保存操作，在第4次[没有特定含义，只是多次修改的时候尝试修复行为]保存的时候，先删除缓存一次
        # !empty($param['keyConfig']) &&  'MC-M-DB'   ==  $param['keyConfig'] &&  4   ==  self::$mcCachesCount[$key]  &&  self::$mcHandle->delete($key);
        if (self::$mcCachesCount[$key] > 5) {//todo 记录
        }

        return true;

    }

    # NO the case!
    private static function setMany($param)
    {
        $key = $param['key'];
        if (!isset($param['value'])) {
            return false;
        }
        $expire = self::$expire;
        $tmp    = [];
        foreach ($param['value'] as $keyId => $value) {
            $keyId       = self::$mapPrefix . '/' . $keyId;
            $tmp[$keyId] = $value;
        }
        $param['value'] = $tmp;

        if ('Memcached' == C('MEMCACHE_LIB_TYPE', 'Memcached')) {
            $result = self::$mcHandle->setMulti($param['value'], $expire);
        } else {
            $result = null;
            foreach ($param['value'] as $k => $v) {
                $result = self::$mcHandle->set($k, $v, $expire);
            }
        }
        return $result;
    }

    private static function add($param)
    {
        $key = $param['key'];
        if (!isset($param['value'])) {
            return false;
        }
        self::$mcCaches[$key]   = $param['value'];
        self::$doSaveList[$key] = ['expire' => self::$expire, 'clientServer' => self::$clientServer];
        return true;

    }

    private static function remove($param)
    {
        $key = $param['key'];

        $result = self::$mcHandle->delete($key);
        if (false === $result) {
            $result = self::$mcHandle->delete($key);
        }
        unset(self::$mcCaches[$key]);
        unset(self::$doSaveList[$key]);
        return $result;
    }

    # NO the case!
    private static function increment($param)
    {
        $key   = $param['key'];
        $value = $param['value'];

        if (!is_numeric($value) || $value < 1) {
            return false;
        }

        $result = self::$mcHandle->increment($key, $value);
        return $result;
    }

    # NO the case!
    private static function decrement($param)
    {
        $key   = $param['key'];
        $value = $param['value'];

        if (!is_numeric($value) || $value < 1) {
            return false;
        }

        $result = self::$mcHandle->decrement($key, $value);
        return $result;
    }

    private static function mcHandle($key)
    {
        if (C('STORAGE_CLOSE_MC', '3600')) {
            return null;
        }

        $configArray = SC::getMcConfig($key);

        if (empty($configArray['serverStatus'])) {
            #   exit ('Mc server is not work! ');
            return Logger::getInstance()->error(
                    ['msg' => 'MMS001', 'no' => 'MMS001', 'param' => ['paramString' => 'MMS001']]
            );
        }
        if (empty(SC::$mcHostConfig[$configArray['clientServer']])) {
            #   exit ('Mc server is not exist! ');
            return Logger::getInstance()->error(
                    ['msg' => 'MMS002', 'no' => 'MMS002', 'param' => ['paramString' => 'MMS002']]
            );
        }
        self::$mcKey        = $configArray['mcKey'];
        self::$mapPrefix    = $configArray['mapPrefix'];
        self::$expire       = $configArray['expire'];
        $clientServer       = $configArray['clientServer'];
        self::$clientServer = $clientServer;
        $customExpire       = C('customExpire', false);
        if (!empty($customExpire)) {
            self::$expire = $customExpire;
            SC('customExpire', null);
        }

        empty(self::$expire) && self::$expire = C('DEFAULT_CACHE_TIME', '3600');

        if (isset(self::$mcResource[$clientServer]) && is_resource(self::$mcResource[$clientServer])) {
            return self::$mcHandle = self::$mcResource[$clientServer];
        }

        $hostConfig = SC::$mcHostConfig[$configArray['clientServer']];

        extract($hostConfig);

        $host = isset($hostConfig['host']) ? $hostConfig['host'] : null;
        $port = isset($hostConfig['port']) ? $hostConfig['port'] : null;

        if ('Memcached' == C('MEMCACHE_LIB_TYPE', 'Memcached')) {
            $mcHandle = new Memcached();
            $mcHandle->addServer($host, $port, false);
        } elseif ('Memcache' == C('MEMCACHE_LIB_TYPE', 'Memcached')) {
            $mcHandle = new Memcache();
            $mcHandle->connect($host, $port);
        } else {
            return Logger::getInstance()->error(
                    ['msg' => 'MMS003', 'no' => 'MMS003', 'param' => ['paramString' => 'MMS003']]
            );
        }

        self::$mcResource[$clientServer] = $mcHandle;

        return self::$mcHandle = $mcHandle;
    }

}

