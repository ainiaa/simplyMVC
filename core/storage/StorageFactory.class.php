<?php

class StorageFactory
{
    const MC_PREFIX = 'dev_299_';

    const DBNAME_GAME = 'fs_dev';
    const DBNAME_GIFT = 'fs_dev_interaction';
    const DBNAME_CONFIG = 'fs_configs_zh';

    /**
     * 获得数据库实例
     *
     * @param $key
     *
     * @return mixed
     */
    public static function getDbConfig($key)
    {
        $splitKey = self::splitMapKey($key);
        if (!isset($splitKey[1])) {
            return Logger::getInstance()->error(
                    array(
                            'msg'   => 'SConf001',
                            'no'    => 'SConf001',
                            'param' => array('paramString' => 'key  no split ' . $key)
                    )
            );
        }

        # db index list
        if (empty(self::$dbIndexList[$splitKey[0]])) {
            return Logger::getInstance()->error(
                    array('msg' => 'SConf002', 'no' => 'SConf002', 'param' => array('paramString' => 'SConf002'))
            );
        }

        # db info
        $config      = self::$dbIndexList[$splitKey[0]];
        $tableLength = strlen($config['indexList']);

        if (empty($config['indexMethod'])) {
            $db['tableName']    = $config['tableName'];
            $db['clientServer'] = $config['indexList'][0];
        } else {
            $tableSuffix        = self::$config['indexMethod']($splitKey[1], $tableLength);
            $db['tableSuffix']  = $tableSuffix;
            $db['tableName']    = $config['tableName'] . '_' . $tableSuffix;
            $db['clientServer'] = $config['indexList'][$tableSuffix];
        }

        $userSplitLib = self::getRealSplitLib();
        if (self::$splitLibMapSwitch && !empty($config['splitLib']) && isset($userSplitLib['db'])) {
            isset(self::$splitLibMapServer[$userSplitLib['db']]) && $db['clientServer'] = self::$splitLibMapServer[$userSplitLib['db']];
        }

        $db['mapPrefix'] = $splitKey[0];
        $db['uuidName']  = $config['primary'];     # 数据存储的主键Key
        $db['uuidValue'] = $splitKey[1];
        $db['dbName']    = $config['dbName'];
        $db['isPrimary'] = $config['isPrimary'];   # 是否自动参与条件匹配在组装SQL的时候
        $db['manyIndex'] = $config['manyIndex'];   # 获取多条数据的时候，每条纪录区分的key,如果上面主键Key不能区分数据纪录的时候

        return $db;
    }

    private static $userSplitLib;


    /**
     * 获得userSplitLib
     *
     * @param $uId
     *
     * @return mixed
     */
    public static function getUserSplitLib($uId)
    {
        if (!isset(self::$userSplitLib[$uId])) {
            self::$userSplitLib[$uId] = self::getSplitLib($uId);
        }
        return self::$userSplitLib[$uId];
    }


    /**
     * 获得最终的 split配置项
     * @return mixed
     */
    public static function getRealSplitLib()
    {
        $uId = 0;//这个需要处理
        return self::getUserSplitLib($uId);
    }


    /**
     * 获得memcache / memcached 实例
     *
     * @param $key
     *
     * @return mixed
     */
    public static function getMcConfig($key)
    {
        $splitKey = self::splitMapKey($key);
        if (!isset($splitKey[1])) {
            return Logger::getInstance()->error(
                    array(
                            'msg'   => 'SConf005',
                            'no'    => 'SConf005',
                            'param' => array('paramString' => 'key  no split ' . $key)
                    )
            );
        }

        # mc index list
        if (empty(self::$mcIndexList[$splitKey[0]])) {
            return Logger::getInstance()->error(
                    array('msg' => 'SConf006', 'no' => 'SConf006', 'param' => array('paramString' => 'SConf006'))
            );
        }

        # mc info
        $config   = self::$mcIndexList[$splitKey[0]];
        $mcLength = strlen($config['indexList']);

        if (empty($config['indexMethod'])) {
            $mcIndex = 0;
        } else {
            $mcIndex = self::$config['indexMethod']($splitKey[1], $mcLength);
        }

        $mc['serverStatus'] = true;
        $mc['mapPrefix']    = $splitKey[0];
        $mc['clientServer'] = $config['indexList'][$mcIndex];
        $mc['primary']      = $config['primary'];

        if (empty($config['expire'])) {
            $mc['expire'] = C('DEFAULT_CACHE_TIME', 3600);
        } else {
            $mc['expire'] = $config['expire'];
        }

        if (!empty($config['assemble']) && isset($splitKey[1])) {
            $mc['mcKey'] = self::MC_PREFIX . $config['keyPrefix'] . self::$config['assemble']($splitKey);
        } else {
            $mc['mcKey'] = self::MC_PREFIX . $config['keyPrefix'] . self::mergeKey($splitKey);
        }

        return $mc;
    }

    /**
     * 获得redis 实例
     *
     * @param $key
     *
     * @return mixed
     */
    public static function getRedisConfig($key)
    {
        $splitKey = self::splitMapKey($key);
        if (!isset($splitKey[1])) {
            return Logger::getInstance()->error(
                    array(
                            'msg'   => 'SConf003',
                            'no'    => 'SConf003',
                            'param' => array('paramString' => 'key  no split ' . $key)
                    )
            );
        }

        # redis index list
        if (empty(self::$redisIndexList[$splitKey[0]])) {
            return Logger::getInstance()->error(
                    array('msg' => 'SConf004', 'no' => 'SConf004', 'param' => array('paramString' => 'SConf004'))
            );
        }

        # redis info
        $config      = self::$redisIndexList[$splitKey[0]];
        $redisLength = strlen($config['indexList']);

        if (empty($config['indexMethod'])) {
            $redisIndex = 0;
        } else {
            $redisIndex = self::$config['indexMethod']($splitKey[1], $redisLength);
        }

        $redis['serverStatus'] = true;
        $redis['mapPrefix']    = $splitKey[0];
        $redis['dataType']     = $config['dataType'];
        $redis['selectDb']     = $config['select'];
        $redis['clientServer'] = $config['indexList'][$redisIndex];

        $userSplitLib = self::getRealSplitLib();
        if (self::$splitLibMapSwitch && !empty($config['splitLib']) && isset($userSplitLib['redis'])) {
            isset(self::$splitLibMapServer[$userSplitLib['redis']]) && $redis['clientServer'] = self::$splitLibMapServer[$userSplitLib['redis']];
        }

        if (!empty($config['assemble']) && isset($splitKey[1])) {
            $redis['redisKey'] = $config['keyPrefix'] . self::$config['assemble']($splitKey);
        } else {
            $redis['redisKey'] = $config['keyPrefix'] . self::mergeKey($splitKey);
        }

        return $redis;
    }

    /**
     * @param $code
     *
     * @return mixed
     */
    public static function getKeyMapStorage($code)
    {
        if (empty(self::$keyMapStorage[$code])) {
            return Logger::getInstance()->error(
                    array(
                            'msg'   => 'SConf003',
                            'no'    => 'SConf003',
                            'param' => array('paramString' => 'code no Map storage : ' . $code)
                    )
            );
        }

        return self::$keyMapStorage[$code];
    }


    /**
     * @return array
     */
    public static function getAllKeyMapStorage()
    {
        return self::$keyMapStorage;
    }


    /**
     * @param        $key
     * @param string $separtor
     *
     * @return array
     */
    public static function splitMapKey($key, $separtor = '/')
    {
        $result = explode($separtor, $key);
        return $result;
    }

    /**
     * @param        $splitKey
     * @param string $separtor
     *
     * @return string
     */
    public static function mergeKey($splitKey, $separtor = '')
    {
        if (count($splitKey) > 2) {
            unset($splitKey[0]);
            $result = implode($splitKey, $separtor);
        } else {
            $result = $splitKey[1];
        }
        return $result;
    }

    /**
     * @param $key
     *
     * @return array|null
     */
    public static function getSplitLib($key)
    {
        if (empty(self::$splitLibMapSwitch)) {
            return null;
        }

        $libNo = self::getIdByHash($key, count(self::$splitLibMapServer));
        return array('db' => $libNo, 'redis' => $libNo);
    }

    # split Lib Map Switch
    # lib server 此处DB,Redis参与Split的配置，都必须同步设置，包括数量一直和key从a,b 开始一致，不参与的server 从z,y,x 等倒排序使用
    public static $splitLibMapSwitch = true;
    public static $splitLibMapServer = array(0 => 'a', 1 => 'b');
    public static $splitLibMapRedis = array(0 => 'a', 1 => 'a', '2' => 'b');

    # DB Host Config
    public static $dbHostConfig = array(
            'a' => array('host' => '127.0.0.1', 'port' => '3306', 'user' => 'php', 'passwd' => '123456'),
            'b' => array('host' => '127.0.0.1', 'port' => '3306', 'user' => 'php', 'passwd' => '123456'),
    );
    # Mc Host Config
    public static $mcHostConfig = array(
            'a' => array('host' => '127.0.0.1', 'port' => '11210'),
            'b' => array('host' => '127.0.0.1', 'port' => '11210'),
    );/*}}}*/
    # Redis Host Config
    public static $redisHostConfig = array(
            'a' => array('host' => '127.0.0.1', 'port' => '6370'),
            'b' => array('host' => '127.0.0.1', 'port' => '6370'),
    );

    # Log Server Config 
    public static $logServerConfig = array(
            'host'          => '127.0.0.1',
            'port'          => '6001',
            'connTimeOut'   => 5,
            'sendTimeOut'   => 5,
            'isLogCompress' => true,
    );

    private static function getIdByUid($key, $num)
    {
        $key    = $key % 100000;  # 取uId 后5位
        $result = $key % $num;
        return $result;
    }

    private static function getIdByHash($key, $num)
    {
        $md5Id  = md5($key);
        $decNum = hexdec(substr($md5Id, -2));
        $result = $decNum % $num;
        return $result;
    }

    private static function antitoneAssembleTriadeKey($keyArray)
    {
        if (!is_array($keyArray)) {
            return $keyArray;
        } elseif (isset($keyArray[2])) {
            return $keyArray[2] . $keyArray[1];
        } elseif (isset($keyArray[1])) {
            return $keyArray[1];
        } else {
            return null;
        }
    }

    # KEY => Storage Type   
    #   'DB'            #   只使用DB存储
    #   'MC'            #   只使用MC存储
    #   'DB-MC'         #   MC 缓存，DB后备
    #   'REDIS'         #   REDIS 缓存数据
    #   'MC-M-DB'       #   MC 缓存，一KEY 对应DB 一批数据, 主要是getData的时候使用，其他时候和DB-MC 相同
    #   #   'DB-REDIS'      #   REDIS 缓存，DB后备
    #   
    # Key => Storage    
    private static $keyMapStorage = array();

    # DB Index List
    private static $dbIndexList = array();

    # Mc Index List     
    private static $mcIndexList = array();

    # Redis Index List  
    private static $redisIndexList = array();

}


final class SC extends StorageFactory
{
}

