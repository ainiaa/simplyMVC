<?php

class StorageFactory
{
    private static $processMcKeyCache;
    private static $processRedisKeyCache;
    const MC_PREFIX = 'dev_299_';

    const DBNAME_GAME = 'fs_dev';
    const DBNAME_GIFT = 'fs_dev_interaction';
    const DBNAME_CONFIG = 'fs_configs_zh';

    public static function getDbClient($key)
    {
        $splitKey = self::splitMapKey($key);
        if (!isset($splitKey[1])) {
            return Logger::getInstance()->error(
                    array('msg'   => 'SConf001',
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

        if (self::$splitLibMapSwitch && !empty($config['splitLib']) && isset(AC::$userSplitLib['db'])) {
            isset(self::$splitLibMapServer[AC::$userSplitLib['db']]) && $db['clientServer'] = self::$splitLibMapServer[AC::$userSplitLib['db']];
        }

        $db['mapPrefix'] = $splitKey[0];
        $db['uuidName']  = $config['primary'];     # 数据存储的主键Key
        $db['uuidValue'] = $splitKey[1];
        $db['dbName']    = $config['dbName'];
        $db['isPrimary'] = $config['isPrimary'];   # 是否自动参与条件匹配在组装SQL的时候
        $db['manyIndex'] = $config['manyIndex'];   # 获取多条数据的时候，每条纪录区分的key,如果上面主键Key不能区分数据纪录的时候

        return $db;
    }

    public static function getMcClient($key)
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
            $mc['expire'] = C('DEFAULT_CACHE_TIME', '3600');
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

    public static function getRedisClient($key)
    {
        $splitKey = self::splitMapKey($key);
        if (!isset($splitKey[1])) {
            return Logger::getInstance()->error(
                    array('msg'   => 'SConf003',
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

        if (self::$splitLibMapSwitch && !empty($config['splitLib']) && isset(AC::$userSplitLib['redis'])) {
            isset(self::$splitLibMapServer[AC::$userSplitLib['redis']]) && $redis['clientServer'] = self::$splitLibMapServer[AC::$userSplitLib['redis']];
        }

        if (!empty($config['assemble']) && isset($splitKey[1])) {
            $redis['redisKey'] = $config['keyPrefix'] . self::$config['assemble']($splitKey);
        } else {
            $redis['redisKey'] = $config['keyPrefix'] . self::mergeKey($splitKey);
        }

        return $redis;
    }

    public static function getKeyMapStorage($code)
    {
        if (empty(self::$keyMapStorage[$code])) {
            return Logger::getInstance()->error(
                    array('msg'   => 'SConf003',
                          'no'    => 'SConf003',
                          'param' => array('paramString' => 'code no Map storage : ' . $code)
                    )
            );
        }

        return self::$keyMapStorage[$code];
    }

    public static function getAllKeyMapStorage()
    {
        return self::$keyMapStorage;
    }

    public static function splitMapKey($key, $separtor = '/')
    {
        $result = explode($separtor, $key);
        #   if (count($result) > 2)
        #   {
        #       $tmp[0] =   $result[0];
        #       unset($result[0]);
        #       $tmp[1] =   implode($separtor, $result);
        #       $result =   $tmp;
        #   }
        return $result;
    }

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
    public static $splitLibMapSwitch = false;
    public static $splitLibMapServer = null;

    # DB Host Config    
    public static $dbHostConfig = array(
            'a' => array('host' => '172.17.0.24', 'port' => '3306', 'user' => 'php', 'passwd' => 'shinezone2008'),
            'b' => array('host' => '172.17.0.24', 'port' => '3306', 'user' => 'php', 'passwd' => 'shinezone2008'),
    );
    # Mc Host Config    
    public static $mcHostConfig = array(
            'a' => array('host' => '172.17.0.80', 'port' => '11210'),
            'b' => array('host' => '172.17.0.80', 'port' => '11210'),
    );
    # Redis Host Config     
    public static $redisHostConfig = array(
            'a' => array('host' => '172.17.0.80', 'port' => '6370'),
            'b' => array('host' => '172.17.0.80', 'port' => '6370'),
    );

    # Log Server Config 
    public static $logServerConfig = array(
            'host'          => '172.17.0.80',
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
    private static $keyMapStorage = array(
            'ACA'      => 'REDIS',        # model/ActivityContinuousAccelerationModel
            'AGW'      => 'REDIS',        # model/ActivityGoodWillModel
            'AL'       => 'REDIS',        # model/ActivityLeaderboardModel;ActivityCollectLbModel
            'ALC'      => 'MC',           # model/ActivityLeaderboardModel;ActivityCollectLbModel
            'ALS'      => 'REDIS',        # model/ActivityLeaderboardModel;ActivityCollectLbModel
            'ALT'      => 'REDIS',        # model/ActivityLeaderboardModel;ActivityCollectLbModel
            'ALM'      => 'REDIS',        # model/SpecialItemLabelModel
            'ALI'      => 'DB',           # model/SpecialItemLabelModel
            'ALUC'     => 'REDIS',        # model/ActivityLuckyCatModel
            'ALUR'     => 'REDIS',        # model/ActivityLuckyCatModel
            'ALUT'     => 'REDIS',        # model/ActivityLuckyCatModel
            'ALUN'     => 'REDIS',        # model/ActivityLuckyCatModel
            'ALUI'     => 'MC',           # model/ActivityLuckyCatModel
            'AM'       => 'REDIS',        # model/ActivityManagerHelpModel
            'AMS'      => 'REDIS',        # model/ActivityManagerHelpModel
            'AMT'      => 'REDIS',        # model/ActivityManagerHelpModel
            'AMGS'     => 'REDIS',        # model/ActivityManagerHelpModel
            'AMGI'     => 'MC',           # model/ActivityManagerHelpModel
            'ARR'      => 'REDIS',        # model/ActivityRechargeRebateModel
            'ASL'      => 'REDIS',        # model/ActivitySapphireLotteryModel
            'AWR'      => 'REDIS',        # model/ActivityWeekendRebateModel
            'AT'       => 'REDIS',        # model/ActivityTreasureModel
            'DMDS'     => 'REDIS',        # model/DailyMissionModel
            'DMCD'     => 'REDIS',        # model/DailyMissionModel
            'DMSD'     => 'REDIS',        # model/DailyMissionModel
            'FFI'      => 'DB-MC',        # model/PackageModel
            'FI'       => 'REDIS',        # model/FloricultureStorehouseModel
            'FIM'      => 'DB',           # model/FeedInfoModel
            'FLM'      => 'DB',           # model/FeedLogModel
            'FSM'      => 'MC',           # model/FeedLogModel
            'FM'       => 'DB-MC',        # model/FarmMarketModel
            'FMR'      => 'REDIS',        # model/FarmMarketModel #记录解锁花艺台需要Request好友帮助的数据
            'FS'       => 'MC-M-DB',      # model/FloricultureStorehouseModel
            'FRT'      => 'MC',           # model/FriendModel
            'FPFC'     => 'MC',           # model/FriendModel
            'FUPF'     => 'REDIS',        # model/FriendModel
            'FPCT'     => 'MC',           # model/FriendModel
            'FPM'      => 'MC-M-DB',      # model/FriendModel
            'FCUR'     => 'REDIS',        # model/SimplyTaskModel     #花艺品解锁活动，已解锁的花艺品数据
            'GBA'      => 'REDIS',        # model/GiftBagModel
            'GBPS'     => 'REDIS',        # model/GiftBagModel
            'GM-A'     => 'MC',           # model/GameMapModel:umIdMapArea
            'GMGR'     => 'MC-M-DB',      # model/GameMapModel:greenHouseRoom
            'GM-B'     => 'MC-M-DB',      # model/GameMapModel:backGarden
            'GM-F'     => 'MC-M-DB',      # model/GameMapModel:fairground
            'GM-G'     => 'MC-M-DB',      # model/GameMapModel:garden
            'GM-H'     => 'MC-M-DB',      # model/GameMapModel:greenHouse
            'GM-M'     => 'MC-M-DB',      # model/GameMapModel:farmMarket
            'GM-R'     => 'MC-M-DB',      # model/GameMapModel:room
            'GEL'      => 'REDIS',        # model/UserExtendModel
            'LT'       => 'MC-M-DB',      # model/LimitedTaskModel
            'LTR'      => 'REDIS',        # model/LimitedTaskModel
            'LUR'      => 'REDIS',        # model/LevelUpRewardModel
            'HMR'      => 'REDIS',        # model/LimitedTaskModel
            'MI'       => 'MC-M-DB',      # model/MachineModel
            'MTM'      => 'MC-M-DB',      # model/MainTaskModel
            'OM'       => 'DB',           # model/OrderModel
            'OPA'      => 'MC',           # model/OrderModel
            'P'        => 'MC-M-DB',      # model/PackageModel
            'P2068'    => 'MC',           # model/PackageModel
            'RG'       => 'MC',           # model/RequestModel
            'RI'       => 'MC',           # model/RequestModel
            'RFI'      => 'MC',           # model/RequestModel
            'RDL'      => 'DB',           # model/RequestModel
            'RSG'      => 'MC',           # model/RequestModel
            'TAD'      => 'REDIS',        # model/TaskActivityModel
            'TAF'      => 'REDIS',        # model/TaskActivityModel
            'TAS'      => 'REDIS',        # model/TaskActivityModel
            'TO'       => 'MC',           # model/TotemModel
            'TL'       => 'REDIS',        # model/TaskLeaderboardModel
            'TLG'      => 'REDIS',        # model/TaskLeaderboardModel
            'TLS'      => 'REDIS',        # model/TaskLeaderboardModel
            'TLOG'     => 'REDIS',        # model/TaskLeaderboardModel
            'TLOS'     => 'REDIS',        # model/TaskLeaderboardModel
            'TS'       => 'MC-M-DB',      # model/TaskStoryModel
            'TSL'      => 'REDIS',        # model/TaskStoryModel
            'TASK_L_S' => 'REDIS',        # model/TaskLoginModel
            'UI'       => 'DB-MC',        # model/UserInfoModel
            'USD'      => 'DB',           # model/UserInfoModel
            'USM'      => 'MC',           # model/UserInfoModel
            'USR'      => 'REDIS',        # model/UserInfoModel
            'NP'       => 'MC-M-DB',      # model/NpcModel
            'NW'       => 'REDIS',        # model/NutritionWaterModel
            'UA'       => 'DB-MC',        # model/UserInfoModel
            'UAC'      => 'MC-M-DB',      # model/NpcModel
            'UE'       => 'REDIS',        # model/UserExtendModel
            'UFC'      => 'REDIS',        # model/FloricultureModel
            'UL'       => 'REDIS',        # model/UserExtendModel
            'ULT'      => 'MC',           # model/UserInfoModel
            'UP'       => 'DB-MC',        # model/UserExpandModel
            'URR'      => 'REDIS',        # model/UpgradeRebateModel
            'FIL'      => 'MC-M-DB',      # model/FriendModel
            'FRS'      => 'MC-M-DB',      # model/FriendModel
            'FSTAT'    => 'REDIS',        # model/FriendModel
            'PAY-R'    => 'REDIS',        # model/PayRebateModel
            'ESD'      => 'REDIS',        # model/ExpressModel
            'EP'       => 'MC-M-DB',      # model/ExpressModel
            'EPN'      => 'MC-M-DB',      # model/ExpressNpcModel
            'CUDL'     => 'MC',           # model/CacheStatusModel    #   User Daily Login
            'CFCT'     => 'MC',           # model/CacheStatusModel    #   Feed Click Times
            'CSPS'     => 'MC',           # model/CacheStatusModel    #   User Daily Login
            'CMTC'     => 'MC',           # model/CacheStatusModel    #   User Main Task clicked
            'CLTI'     => 'MC',           # model/CacheStatusModel    #   Limited Time task Icon tips
            'SSSS'     => 'MC',           # model/CacheStatusModel    #   supersonic Ad get gift status
            'CPMR'     => 'MC',           # model/CacheStatusModel    #   Payment Result
            'PPOS'     => 'MC',           # model/CacheStatusModel    #   Post Photo
            'WBFF'     => 'MC',           # model/CacheStatusModel    #   Send birthday feed
            'WFLG'     => 'MC',           # model/CacheStatusModel    #   Sent birthday feed log
            'P-FC'     => 'MC-M-DB',      # parameter/FlowerCraftParameter
            'P-FB'     => 'MC-M-DB',      # parameter/FlowerCraftParameter
            'P-I'      => 'DB-MC',        # parameter/ItemParameter
            'P-IE'     => 'MC-M-DB',      # parameter/ItemExtraParameter
            'P-SN'     => 'MC-M-DB',      # parameter/NpcParameter
            'P-T'      => 'MC-M-DB',      # parameter/TotemParameter
            'P-UI'     => 'MC-M-DB',      # parameter/UpgradeParameter
            'P-ULI'    => 'MC-M-DB',      # parameter/UpgradeParameter
            'P-UV'     => 'MC-M-DB',      # parameter/UpgradeParameter
            'IGP'      => 'MC-M-DB',      # parameter/IndividualGlobalParameter
            'ITP'      => 'MC-M-DB',      # parameter/IndividualTaskParameter
            'I-H'      => 'REDIS',        # parameter/IndividualModel
            'O-I-H'    => 'REDIS',        # parameter/IndividualModel
            'ST'       => 'MC-M-DB',      # model/SimplyTaskModel
            'STR'      => 'REDIS',        # model/SimplyTaskModel
            'FSR'      => 'REDIS',        # model/FloralSculptureModel
            'USH'      => 'REDIS',        # model/ExpressUpgradeModel
            'UPH'      => 'REDIS',        # model/ExpressUpgradeModel
            'UDSR'     => 'REDIS',        # model/DailySignModel
            'UACR'     => 'REDIS',        # model/DailySignModel
            'NCT'      => 'REDIS',        # model/AdminModel
            'ARM'      => 'MC',           # model/AdminModel
            'PWR'      => 'REDIS',        # model/PopWindowModel
            'SPS'      => 'REDIS',        # model/SplicePicture 拼图状态存放
            'SPN'      => 'REDIS',        # model/SplicePicture 碎片数量
            'HSG'      => 'MC',           # model/TaskActivityModel 热卖限购
            'COM'      => 'REDIS',        # model/Compensate 补偿
            'GE-S'     => 'REDIS',        # model/GardenExploreModel
            'GE-M'     => 'REDIS',        # model/GardenExploreModel
            'GE-T'     => 'REDIS',        # model/GardenExploreModel
            'UFCS'     => 'REDIS',
            'FGBASE'   => 'REDIS',
            'FGSTAT'   => 'REDIS',
            'B-C'      => 'REDIS',        # model/BirthdayCollectModel
            'ASLOT'    => 'REDIS',        # model/ActivitySlotModel
    );

    /**
     * 'f_user_maps'=>array('ct'=>range(0,4)),
     * 'f_user_tasks'=>array('ct'=>range(0,2)),
     * 'f_user_storages' => array('ct' => range(0)),
     * 'f_user_interaction' => array('ct' => range(0, 99)),
     * 'f_user_floralstorage' => array('ct' => range(0, 9)),
     * 'f_users_friends' => array('ct' => range(0, 99)),
     * 'f_users_relation' => array('ct' => range(0, 99)),
     * 'f_user_express' => array('ct' => range(0, 99)),
     * 'f_user_npc' => array('ct' => range(0, 99)),
     */
    # DB Index List     
    private static $dbIndexList = array(
            'FFI'   => array(
                    'dbName'      => self::DBNAME_GAME,
                    'tableName'   => 'f_users',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'uId',
                    'isPrimary'   => true,
                    'manyIndex'   => 'uId'
            ),
            'FS'    => array(
                    'dbName'      => self::DBNAME_GAME,
                    'tableName'   => 'f_user_floralstorage',
                    'indexMethod' => 'getIdByUid',
                    'indexList'   => 'aaaaaaaaaa',
                    'primary'     => 'uId',
                    'isPrimary'   => true,
                    'manyIndex'   => 'usValue'
            ),
            'FIM'   => array(
                    'dbName'      => self::DBNAME_GAME,
                    'tableName'   => 'f_user_feeds',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'uId',
                    'isPrimary'   => true,
                    'manyIndex'   => 'feedId'
            ),
            'FLM'   => array(
                    'dbName'      => self::DBNAME_GAME,
                    'tableName'   => 'f_user_feeds_log',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'feedId',
                    'isPrimary'   => true,
                    'manyIndex'   => 'fuId'
            ),
            'FPM'   => array(
                    'dbName'      => self::DBNAME_GAME,
                    'tableName'   => 'f_users_relation',
                    'indexMethod' => 'getIdByUid',
                    'indexList'   => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                    'primary'     => 'uid',
                    'isPrimary'   => true,
                    'manyIndex'   => 'fuid'
            ),
            'FIL'   => array(
                    'dbName'      => self::DBNAME_GAME,
                    'tableName'   => 'f_user_friends',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'ufId',
                    'isPrimary'   => true,
                    'manyIndex'   => 'fId'
            ),
            'FRS'   => array(
                    'dbName'      => self::DBNAME_GAME,
                    'tableName'   => 'f_users_relation',
                    'indexMethod' => 'getIdByUid',
                    'indexList'   => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                    'primary'     => 'uid',
                    'isPrimary'   => true,
                    'manyIndex'   => 'fuid'
            ),
            'GMGR'  => array(
                    'dbName'      => self::DBNAME_GAME,
                    'tableName'   => 'f_user_ghmaps',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'uId',
                    'isPrimary'   => true,
                    'manyIndex'   => 'umId'
            ),
            'GM-B'  => array(
                    'dbName'      => self::DBNAME_GAME,
                    'tableName'   => 'f_user_maps',
                    'indexMethod' => 'getIdByUid',
                    'indexList'   => 'aaaaa',
                    'primary'     => 'uId',
                    'isPrimary'   => true,
                    'manyIndex'   => 'umId'
            ),
            'GM-F'  => array(
                    'dbName'      => self::DBNAME_GAME,
                    'tableName'   => 'f_user_maps',
                    'indexMethod' => 'getIdByUid',
                    'indexList'   => 'aaaaa',
                    'primary'     => 'uId',
                    'isPrimary'   => true,
                    'manyIndex'   => 'umId'
            ),
            'GM-G'  => array(
                    'dbName'      => self::DBNAME_GAME,
                    'tableName'   => 'f_user_maps',
                    'indexMethod' => 'getIdByUid',
                    'indexList'   => 'aaaaa',
                    'primary'     => 'uId',
                    'isPrimary'   => true,
                    'manyIndex'   => 'umId'
            ),
            'GM-H'  => array(
                    'dbName'      => self::DBNAME_GAME,
                    'tableName'   => 'f_user_maps',
                    'indexMethod' => 'getIdByUid',
                    'indexList'   => 'aaaaa',
                    'primary'     => 'uId',
                    'isPrimary'   => true,
                    'manyIndex'   => 'umId'
            ),
            'GM-M'  => array(
                    'dbName'      => self::DBNAME_GAME,
                    'tableName'   => 'f_user_maps',
                    'indexMethod' => 'getIdByUid',
                    'indexList'   => 'aaaaa',
                    'primary'     => 'uId',
                    'isPrimary'   => true,
                    'manyIndex'   => 'umId'
            ),
            'GM-R'  => array(
                    'dbName'      => self::DBNAME_GAME,
                    'tableName'   => 'f_user_maps',
                    'indexMethod' => 'getIdByUid',
                    'indexList'   => 'aaaaa',
                    'primary'     => 'uId',
                    'isPrimary'   => true,
                    'manyIndex'   => 'umId'
            ),
            'MI'    => array(
                    'dbName'      => self::DBNAME_GAME,
                    'tableName'   => 'f_user_machine_actions',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'uId',
                    'isPrimary'   => true,
                    'manyIndex'   => 'umaId'
            ),
            'MTM'   => array(
                    'dbName'      => self::DBNAME_GAME,
                    'tableName'   => 'f_user_tasks',
                    'indexMethod' => 'getIdByUid',
                    'indexList'   => 'aaa',
                    'primary'     => 'uId',
                    'isPrimary'   => true,
                    'manyIndex'   => 'utId'
            ),
            'OM'    => array(
                    'dbName'      => self::DBNAME_GAME,
                    'tableName'   => 'f_orders',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'uId',
                    'isPrimary'   => true,
                    'manyIndex'   => 'oId'
            ),
            'P'     => array(
                    'dbName'      => self::DBNAME_GAME,
                    'tableName'   => 'f_user_storages',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'uId',
                    'isPrimary'   => true,
                    'manyIndex'   => 'usValue'
            ),
            'EP'    => array(
                    'dbName'      => self::DBNAME_GAME,
                    'tableName'   => 'f_user_express',
                    'indexMethod' => 'getIdByUid',
                    'indexList'   => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                    'primary'     => 'uId',
                    'isPrimary'   => true,
                    'manyIndex'   => 'Id'
            ),
            'EPN'   => array(
                    'dbName'      => self::DBNAME_GAME,
                    'tableName'   => 'f_user_npc',
                    'indexMethod' => 'getIdByUid',
                    'indexList'   => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                    'primary'     => 'uId',
                    'isPrimary'   => true,
                    'manyIndex'   => 'Id'
            ),
            'RDL'   => array(
                    'dbName'      => self::DBNAME_GAME,
                    'tableName'   => 'f_user_interactions',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'ruId',
                    'isPrimary'   => true,
                    'manyIndex'   => 'uId'
            ),
            'TS'    => array(
                    'dbName'      => self::DBNAME_GAME,
                    'tableName'   => 'f_tasks',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'uId',
                    'isPrimary'   => true,
                    'manyIndex'   => 'tId'
            ),
            'UI'    => array(
                    'dbName'      => self::DBNAME_GAME,
                    'tableName'   => 'f_users',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'uId',
                    'isPrimary'   => true,
                    'manyIndex'   => 'uId'
            ),
            'USD'   => array(
                    'dbName'      => self::DBNAME_GAME,
                    'tableName'   => 'f_usersplit',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'uId',
                    'isPrimary'   => true,
                    'manyIndex'   => 'uId'
            ),
            'NP'    => array(
                    'dbName'      => self::DBNAME_GAME,
                    'tableName'   => 'f_user_players',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'uId',
                    'isPrimary'   => true,
                    'manyIndex'   => 'upId'
            ),
            'UA'    => array(
                    'dbName'      => self::DBNAME_GAME,
                    'tableName'   => 'f_users',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'uId',
                    'isPrimary'   => true,
                    'manyIndex'   => 'uId'
            ),
            'UAC'   => array(
                    'dbName'      => self::DBNAME_GAME,
                    'tableName'   => 'f_user_actions',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'uId',
                    'isPrimary'   => true,
                    'manyIndex'   => 'uaId'
            ),
            'UP'    => array(
                    'dbName'      => self::DBNAME_GAME,
                    'tableName'   => 'f_user_expand',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'uId',
                    'isPrimary'   => true,
                    'manyIndex'   => 'uId'
            ),
            'LT'    => array(
                    'dbName'      => self::DBNAME_GAME,
                    'tableName'   => 'f_limited_task',
                    'indexMethod' => 'getIdByUid',
                    'indexList'   => 'aaaaaaaaaaaaaaaa',
                    'primary'     => 'uId',
                    'isPrimary'   => true,
                    'manyIndex'   => 'topic_id'
            ),
            'ST'    => array(
                    'dbName'      => self::DBNAME_GAME,
                    'tableName'   => 'f_simply_task',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'uId',
                    'isPrimary'   => true,
                    'manyIndex'   => 'tId'
            ),
            'P-FC'  => array(
                    'dbName'      => self::DBNAME_CONFIG,
                    'tableName'   => 'flower_craft_info_config',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'version_tag',
                    'isPrimary'   => true,
                    'manyIndex'   => 'iId'
            ),
            'P-FB'  => array(
                    'dbName'      => self::DBNAME_CONFIG,
                    'tableName'   => 'floral_bench_config',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'version_tag',
                    'isPrimary'   => true,
                    'manyIndex'   => 'iId'
            ),
            'P-I'   => array(
                    'dbName'      => self::DBNAME_CONFIG,
                    'tableName'   => 'item_config',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'version_tag',
                    'isPrimary'   => false,
                    'manyIndex'   => 'iId'
            ),
            'P-IE'  => array(
                    'dbName'      => self::DBNAME_CONFIG,
                    'tableName'   => 'item_extra_config',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'version_tag',
                    'isPrimary'   => true,
                    'manyIndex'   => 'iId'
            ),
            'P-SN'  => array(
                    'dbName'      => self::DBNAME_CONFIG,
                    'tableName'   => 'players_config',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'version_tag',
                    'isPrimary'   => true,
                    'manyIndex'   => 'pId'
            ),
            'P-T'   => array(
                    'dbName'      => self::DBNAME_CONFIG,
                    'tableName'   => 'totem_config',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'version_tag',
                    'isPrimary'   => false,
                    'manyIndex'   => 'iId'
            ),
            'P-UI'  => array(
                    'dbName'      => self::DBNAME_CONFIG,
                    'tableName'   => 'upgrade_items_config',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'version_tag',
                    'isPrimary'   => true,
                    'manyIndex'   => 'iId'
            ),
            'P-ULI' => array(
                    'dbName'      => self::DBNAME_CONFIG,
                    'tableName'   => 'unlock_item_config',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'version_tag',
                    'isPrimary'   => true,
                    'manyIndex'   => 'iId'
            ),
            'P-UV'  => array(
                    'dbName'      => self::DBNAME_CONFIG,
                    'tableName'   => 'upgrade_virtual_config',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'version_tag',
                    'isPrimary'   => true,
                    'manyIndex'   => 'iId'
            ),
            'IGP'   => array(
                    'dbName'      => self::DBNAME_CONFIG,
                    'tableName'   => 'individual_global_config',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'version_tag',
                    'isPrimary'   => true,
                    'manyIndex'   => 'term_id'
            ),
            'ITP'   => array(
                    'dbName'      => self::DBNAME_CONFIG,
                    'tableName'   => 'individual_task_config',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'version_tag',
                    'isPrimary'   => true,
                    'manyIndex'   => 'id'
            ),
            'ALI'   => array(
                    'dbName'      => self::DBNAME_GAME,
                    'tableName'   => 'f_activity_leaderboard',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'uId',
                    'isPrimary'   => true,
                    'manyIndex'   => 'iId'
            ),
    );

    # Mc Index List     
    private static $mcIndexList = array(
            'ALC'   => array(
                    'keyPrefix'   => 'ALC_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => null,
                    'expire'      => AC::S30,
                    'assemble'    => ''
            ),
            'ALUI'  => array(
                    'keyPrefix'   => 'ALUI_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => null,
                    'expire'      => AC::S30,
                    'assemble'    => ''
            ),
            'AMGI'  => array(
                    'keyPrefix'   => 'AMGI_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => null,
                    'expire'      => AC::M1,
                    'assemble'    => ''
            ),
            'ARM'   => array(
                    'keyPrefix'   => 'ARM_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => null,
                    'expire'      => AC::M5,
                    'assemble'    => ''
            ),
            'FFI'   => array(
                    'keyPrefix'   => 'USER_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => null,
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'FS'    => array(
                    'keyPrefix'   => 'Floral_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'usValue',
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'FIL'   => array(
                    'keyPrefix'   => 'USER_FRIEND_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => null,
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'FSM'   => array(
                    'keyPrefix'   => '',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => null,
                    'expire'      => AC::D1,
                    'assemble'    => 'antitoneAssembleTriadeKey'
            ),
            'FRS'   => array(
                    'keyPrefix'   => 'FRIEND_RELATION_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => null,
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'FRT'   => array(
                    'keyPrefix'   => 'u:push:ref',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => null,
                    'expire'      => AC::D1,
                    'assemble'    => ''
            ),
            'FPM'   => array(
                    'keyPrefix'   => 'us:pf:',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'fuid',
                    'expire'      => AC::D1,
                    'assemble'    => ''
            ),
            'FPFC'  => array(
                    'keyPrefix'   => 'u:pf:ids:',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => null,
                    'expire'      => AC::D1,
                    'assemble'    => ''
            ),
            'FPCT'  => array(
                    'keyPrefix'   => 'u:req:t:',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => null,
                    'expire'      => AC::D1,
                    'assemble'    => ''
            ),
            'CUDL'  => array(
                    'keyPrefix'   => 'USER_DAILY_LOGIN_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'NULL',
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'CFCT'  => array(
                    'keyPrefix'   => 'fcbt_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => null,
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'CMTC'  => array(
                    'keyPrefix'   => 'UserTaskClicked:',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => null,
                    'expire'      => AC::D1,
                    'assemble'    => ''
            ),
            'CSPS'  => array(
                    'keyPrefix'   => 'PanelStatus_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => null,
                    'expire'      => AC::D1,
                    'assemble'    => 'antitoneAssembleTriadeKey'
            ),
            'CLTI'  => array(
                    'keyPrefix'   => 'TimedMissionIcon_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => null,
                    'expire'      => AC::D1,
                    'assemble'    => ''
            ),
            'SSSS'  => array(
                    'keyPrefix'   => 'sn_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => null,
                    'expire'      => AC::D1,
                    'assemble'    => 'antitoneAssembleTriadeKey'
            ),
            'WBFF'  => array(
                    'keyPrefix'   => 'userBirthdayPanelTodaySend_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => null,
                    'expire'      => AC::D1,
                    'assemble'    => ''
            ),
            'WFLG'  => array(
                    'keyPrefix'   => 'userBirthdayPanelTotalSent_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => null,
                    'expire'      => AC::D1,
                    'assemble'    => ''
            ),
            'PPOS'  => array(
                    'keyPrefix'   => 'mem_code_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => null,
                    'expire'      => AC::D1,
                    'assemble'    => ''
            ),
            'CPMR'  => array(
                    'keyPrefix'   => 'PaymentResult_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => null,
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'GM-A'  => array(
                    'keyPrefix'   => 'GM-A/',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => null,
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'GMGR'  => array(
                    'keyPrefix'   => 'USER_GH_MAPS_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'umId',
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'GM-B'  => array(
                    'keyPrefix'   => 'u:m:',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'umId',
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'GM-F'  => array(
                    'keyPrefix'   => 'u:m:',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'umId',
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'GM-G'  => array(
                    'keyPrefix'   => 'u:m:',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'umId',
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'GM-H'  => array(
                    'keyPrefix'   => 'u:m:',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'umId',
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'GM-M'  => array(
                    'keyPrefix'   => 'u:m:',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'umId',
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'GM-R'  => array(
                    'keyPrefix'   => 'u:m:',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'umId',
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'LT'    => array(
                    'keyPrefix'   => 'LimitedTask:',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'topic_id',
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'ST'    => array(
                    'keyPrefix'   => 'SimplyTask:',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'tId',
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'MI'    => array(
                    'keyPrefix'   => 'uma_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'umaId',
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'MTM'   => array(
                    'keyPrefix'   => 'USER_TASKS_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'utId',
                    'expire'      => AC::D1,
                    'assemble'    => ''
            ),
            'OPA'   => array(
                    'keyPrefix'   => 'logPayActions:',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => null,
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'P'     => array(
                    'keyPrefix'   => 'USER_STORAGES_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'usValue',
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'EP'    => array(
                    'keyPrefix'   => 'USER_EXPRESS_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'Id',
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'EPN'   => array(
                    'keyPrefix'   => 'USER_EXPRESS_NPC_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'Id',
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'P2068' => array(
                    'keyPrefix'   => '',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => null,
                    'expire'      => AC::D1,
                    'assemble'    => ''
            ),
            'RG'    => array(
                    'keyPrefix'   => '',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => null,
                    'expire'      => AC::D1,
                    'assemble'    => 'antitoneAssembleTriadeKey'
            ),
            'RSG'   => array(
                    'keyPrefix'   => '',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => null,
                    'expire'      => AC::D1,
                    'assemble'    => 'antitoneAssembleTriadeKey'
            ),
            'RI'    => array(
                    'keyPrefix'   => 'uia:list:',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'uiId',
                    'expire'      => AC::W1,
                    'assemble'    => ''
            ),
            'RFI'   => array(
                    'keyPrefix'   => 'uia:finished:',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'uiId',
                    'expire'      => AC::D1,
                    'assemble'    => ''
            ),
            'TO'    => array(
                    'keyPrefix'   => 'TOTEM_ORNAMENT_MEM_KEY_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => null,
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'TS'    => array(
                    'keyPrefix'   => 'TASK_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'tId',
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'UI'    => array(
                    'keyPrefix'   => 'USER_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => null,
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'USM'   => array(
                    'keyPrefix'   => 'SplitLib_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => null,
                    'expire'      => AC::W1,
                    'assemble'    => ''
            ),
            'ULT'   => array(
                    'keyPrefix'   => 'USER_CURRENT_DAY_LOGIN_TIMES_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => null,
                    'expire'      => AC::D1,
                    'assemble'    => ''
            ),
            'NP'    => array(
                    'keyPrefix'   => 'USER_PLAYERS_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'upId',
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'UA'    => array(
                    'keyPrefix'   => 'USER_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'uId',
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'UAC'   => array(
                    'keyPrefix'   => 'USER_ACTIONS_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'uaId',
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'UP'    => array(
                    'keyPrefix'   => 'USER_EXPAND_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => null,
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'P-FC'  => array(
                    'keyPrefix'   => 'P_FC_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'iId',
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'P-FB'  => array(
                    'keyPrefix'   => 'P_FB_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'iId',
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'P-I'   => array(
                    'keyPrefix'   => 'P_I_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => null,
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'P-IE'  => array(
                    'keyPrefix'   => 'P_IE_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'iId',
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'P-SN'  => array(
                    'keyPrefix'   => 'P_SN_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'pId',
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'P-T'   => array(
                    'keyPrefix'   => 'P_T_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'iId',
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'P-UI'  => array(
                    'keyPrefix'   => 'P_UI_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'iId',
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'P-ULI' => array(
                    'keyPrefix'   => 'P_ULI_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'iId',
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'P-UV'  => array(
                    'keyPrefix'   => 'P_UV_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'iId',
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'IGP'   => array(
                    'keyPrefix'   => 'IGP_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'item_id',
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'ITP'   => array(
                    'keyPrefix'   => 'ITP_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'id',
                    'expire'      => AC::H10,
                    'assemble'    => ''
            ),
            'HSG'   => array(
                    'keyPrefix'   => 'HSG_',
                    'indexMethod' => '',
                    'indexList'   => 'a',
                    'primary'     => 'id',
                    'expire'      => AC::D1,
                    'assemble'    => ''
            ),
    );

    # Redis Index List  
    private static $redisIndexList = array(
            'ACA'      => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'ACA:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'AGW'      => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'AGW:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'AL'       => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'AL:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'ALM'      => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'ALM:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'ALS'      => array(
                    'dataType'    => 'Z',
                    'keyPrefix'   => 'ALS:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'ALT'      => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'ALT:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'ALUC'     => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'ALUC:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'ALUT'     => array(
                    'dataType'    => 'Z',
                    'keyPrefix'   => 'ALUT:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'ALUR'     => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'ALUR:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'ALUN'     => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'ALUN:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'AM'       => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'AM:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'AMS'      => array(
                    'dataType'    => 'Z',
                    'keyPrefix'   => 'AMS:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'AMT'      => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'AMT:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'AMGS'     => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'AMGS:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'ARR'      => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'ARR:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'ASL'      => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'ASL:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'AWR'      => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'AWR:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'AT'       => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'AT:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'DMDS'     => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'dm:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'DMCD'     => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'dma:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'DMSD'     => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'dmr:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'ESD'      => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'hes:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'FI'       => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'User_ExtendInfo_',
                    'select'      => '8',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'FUPF'     => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'u:push',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),    #   TODO
            'FMR'      => array(
                    'dataType'    => 'L',
                    'keyPrefix'   => 'user_FarmerMarketLog_',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'GBA'      => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'GBA:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'GBPS'     => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'GBPS:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'UE'       => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'User_ExtendInfo_',
                    'select'      => '8',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'UFC'      => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'fc:uk:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'UL'       => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'ulk:uk:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'NW'       => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'User_ExtendInfo_',
                    'select'      => '8',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'TAD'      => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'ACT_DVT_',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'TAF'      => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'fls:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'TAS'      => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'ACT_SLOT_',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'TSL'      => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'nt:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'GEL'      => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'EP:GL:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'FSTAT'    => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'h:fs:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'PAY-R'    => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'pr:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'I-H'      => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'individual:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'O-I-H'    => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'Operation:Conditions:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'LTR'      => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'ltr:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'LUR'      => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'LUR:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'HMR'      => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'H:Mission:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'URR'      => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'Operation:Conditions:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),#暂时指向老的个人目标数据
            'STR'      => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'st:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'TL'       => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'Operation:Conditions:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'TLG'      => array(
                    'dataType'    => 'Z',
                    'keyPrefix'   => 'TLG:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'TLS'      => array(
                    'dataType'    => 'Z',
                    'keyPrefix'   => 'TLS:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'TLOG'     => array(
                    'dataType'    => 'S',
                    'keyPrefix'   => 'LBG:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'TLOS'     => array(
                    'dataType'    => 'S',
                    'keyPrefix'   => 'LBS:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'FCUR'     => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'fc:uk:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'FSR'      => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'bo:uk:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'USH'      => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'eb:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'UPH'      => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'ebh:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'UDSR'     => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'u:dsi:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'UACR'     => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'u:casi:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'NCT'      => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'noticeConnect',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'PWR'      => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'User_ExtendInfo_',
                    'select'      => '8',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'SPS'      => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'p_u_s_',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'SPN'      => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'p_p_n_',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'COM'      => array(
                    'dataType'    => 'S',
                    'keyPrefix'   => 'compensate_',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'b'
            ),
            'TASK_L_S' => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 't:l:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'GE-S'     => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'g:e:s:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'GE-M'     => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'g:e:m:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'GE-T'     => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'g:e:t:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'UFCS'     => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'u:f:c:s:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'FGBASE'   => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'f:g:b:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'FGSTAT'   => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'f:g:s:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'B-C'      => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'b:c:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'ASLOT'    => array(
                    'dataType'    => 'H',
                    'keyPrefix'   => 'a:s:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
            'USR'      => array(
                    'dataType'    => 'B',
                    'keyPrefix'   => 'usr:',
                    'select'      => '0',
                    'indexMethod' => '',
                    'indexList'   => 'a'
            ),
    );

}


final class SC extends StorageFactory
{
}

