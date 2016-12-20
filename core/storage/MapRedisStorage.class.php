<?php

class MapRedisStorage
{
    private static $redisLastSelectDb;
    private static $redisResource;

    /**
     * @var Redis
     */
    private static $redisHandle;
    private static $redisClient;
    private static $redisType;
    private static $redisKey;

    /**
     * fetch
     * save
     * remove
     * rank
     * fetchMany
     * saveMany
     * incrDecr
     * countLen
     */
    public static function __callStatic($method, $params)
    {
        $param = $params[0];
        if (empty($param['key'])) {
            return Logger::getInstance()->error(
                    array('msg' => 'MRS001', 'no' => 'MRS001', 'param' => array('paramString' => 'MRS001'))
            );
        }

        $methodMap = self::getKeyMethod($method);
        if (empty($methodMap)) {
            return Logger::getInstance()->error(
                    array('msg' => 'MRS007', 'no' => 'MRS007', 'param' => array('paramString' => 'MRS007'))
            );
        }

        $result = self::doRedis($methodMap, $param);
        return $result;
    }

    /**
     * @param $methodMap
     * @param $param
     *
     * @return mixed
     */
    private static function doRedis($methodMap, $param)
    {
        self::redisHandle($param['key']);
        $param['key'] = self::$redisKey;

        if (empty(self::$redisHandle)) {
            return Logger::getInstance()->error(
                    array('msg' => 'MRS008', 'no' => 'MRS008', 'param' => array('paramString' => 'MRS008'))
            );
        }

        if (empty(self::$redisType)) {
            self::$redisType = 'B';
        }

        if (empty($methodMap[self::$redisType])) {
            return Logger::getInstance()->error(
                    array('msg' => 'MRS005', 'no' => 'MRS005', 'param' => array('paramString' => 'MRS005'))
            );
        }

        #   $param['key']   =   self::$redisType. ':'. $param['key'];
        $result = self::$methodMap[self::$redisType]($param);
        return $result;
    }

    /**
     * @param $method
     *
     * @return bool
     */
    private static function getKeyMethod($method)
    {
        #   Key Map Method  
        $keyMapMethod = array(
                'fetch'     => array(
                        'B' => 'get',
                        'H' => 'hGet',
                        'L' => 'lPop',
                        'S' => 'sGet',
                        'Z' => 'zGet',
                ),
                'save'      => array(
                        'B' => 'set',
                        'H' => 'hSet',
                        'L' => 'rPush',
                        'S' => 'sAdd',
                        'Z' => 'zAdd',
                ),
                'remove'    => array(
                        'B' => 'del',
                        'H' => 'hDel',
                        'L' => 'lRem',
                        'S' => 'sRem',
                        'Z' => 'zRem',
                ),
                'rank'      => array(
                        'Z' => 'zRankTop',
                ),
                'incrDecr'  => array(
                        'B' => 'incrDecrs',
                        'H' => 'hIncrby',
                    #   'Z' =>  'zIncrby',
                ),
                'countLen'  => array(
                        'B' => 'length',
                        'H' => 'hLen',
                        'L' => 'lLen',
                        'S' => 'sLen',
                        'Z' => 'zLen',
                ),
                'fetchMany' => array(
                        'B' => 'mGet',
                        'H' => 'hmGet',
                        'L' => 'lRange',
                        'Z' => 'zmGet',
                ),
                'setMany'   => array(
                        'B' => 'mSet',
                        'H' => 'hmSet',
                ),
        );
        if (empty($keyMapMethod[$method])) {
            return false;
        }

        return $keyMapMethod[$method];
    }


    /**
     * @param $key
     *
     * @return Redis
     */
    private static function redisHandle($key)
    {
        $configArray = SC::getRedisConfig($key);

        if (empty($configArray['serverStatus'])) {
            #   exit ('Redis server is not work! ');
            return Logger::getInstance()->error(
                    array('msg' => 'MRS002', 'no' => 'MRS002', 'param' => array('paramString' => 'MRS002'))
            );
        }
        if (empty(SC::$redisHostConfig[$configArray['clientServer']])) {
            #   exit ('Redis server is not exist! ');
            return Logger::getInstance()->error(
                    array('msg' => 'MRS003', 'no' => 'MRS003', 'param' => array('paramString' => 'MRS003'))
            );
        }
        $clientServer      = $configArray['clientServer'];
        self::$redisClient = $clientServer;
        self::$redisType   = $configArray['dataType'];
        self::$redisKey    = $configArray['redisKey'];

        if (isset(self::$redisResource[$clientServer]) && is_resource(self::$redisResource[$clientServer])) {
            self::$redisHandle = self::$redisResource[$clientServer];

            if ($configArray['selectDb'] != self::$redisLastSelectDb) {
                self::$redisLastSelectDb = $configArray['selectDb'];
                self::$redisHandle->select($configArray['selectDb']);
            }
            return self::$redisHandle;
        }

        $hostConfig = SC::$redisHostConfig[$configArray['clientServer']];

        $host        = isset($hostConfig['host']) ? $hostConfig['host'] : '';
        $port        = isset($hostConfig['port']) ? $hostConfig['port'] : '';
        $redisHandle = new Redis();
        $redisLink   = $redisHandle->connect($host, $port);
        $selectDb    = $redisHandle->select($configArray['selectDb']);
        if (empty($redisHandle) || empty($redisLink) || empty($selectDb)) {
            return Logger::getInstance()->error(
                    array('msg' => 'MRS004', 'no' => 'MRS004', 'param' => array('paramString' => 'MRS004'))
            );
        }

        self::$redisLastSelectDb = $configArray['selectDb'];
        return self::$redisHandle = self::$redisResource[$clientServer] = $redisHandle;
    }

    # Base  
    /**
     * @param $param
     *
     * @return mixed
     */
    private static function get($param)
    {
        $result = self::$redisHandle->get($param['key']);

        self::sendErrorLog($result, $param);

        return $result;
    }


    /**
     * @param $param
     *
     * @return mixed
     */
    private static function set($param)
    {
        if (empty($param['value'])) {
            return Logger::getInstance()->error(
                    array('msg' => 'MRS009', 'no' => 'MRS009', 'param' => array('paramString' => 'MRS009'))
            );
        }

        $result = self::$redisHandle->set($param['key'], $param['value']);
        return $result;
    }

    /**
     * @param $param
     *
     * @return mixed
     */
    private static function del($param)
    {
        $result = self::$redisHandle->del($param['key']);
        return $result;
    }


    /**
     * @param $param
     *
     * @return mixed
     */
    private static function mGet($param)
    {
        $result = self::$redisHandle->mGet($param['keys']);

        self::sendErrorLog($result, $param);

        return $result;
    }

    /**
     * @param $param
     *
     * @return mixed
     */
    private static function mSet($param)
    {
        if (empty($param['value']) || !is_array($param['value'])) {
            return Logger::getInstance()->error(
                    array('msg' => 'MRS010', 'no' => 'MRS010', 'param' => array('paramString' => 'MRS010'))
            );
        }

        $result = self::$redisHandle->mSet($param['value']);
        return $result;
    }

    /**
     * @param $param
     *
     * @return mixed
     */
    private static function incrDecrs($param)
    {
        if (empty($param['order'])) {
            return Logger::getInstance()->error(
                    array('msg' => 'MRS024', 'no' => 'MRS024', 'param' => array('paramString' => 'MRS024'))
            );
        }

        if ('incr' == $param['order']) {
            if (empty($param['value'])) {
                $result = self::$redisHandle->incr($param['key']);
            } else {
                $result = self::$redisHandle->incrBy($param['key'], $param['value']);
            }
        } else {
            if (empty($param['value'])) {
                $result = self::$redisHandle->decr($param['key']);
            } else {
                $result = self::$redisHandle->decrBy($param['key'], $param['value']);
            }
        }

        return $result;
    }

    /**
     * @param $param
     *
     * @return mixed
     */
    private static function length($param)
    {
        $result = self::$redisHandle->strlen($param['key']);
        return $result;
    }


    # Hash  

    /**
     * @param $param
     *
     * @return array
     */
    private static function hGet($param)
    {
        if (!isset($param['fields'])) {
            return Logger::getInstance()->error(
                    ['msg' => 'MRS011', 'no' => 'MRS011', 'param' => ['paramString' => 'MRS011']]
            );
        }

        $result = self::$redisHandle->hGet($param['key'], $param['fields']);

        self::sendErrorLog($result, $param);


        return self::formatHashResult($result);
    }

    /**
     * @param $param
     *
     * @return bool
     */
    private static function hSet($param)
    {
        if (!isset($param['fields']) || !isset($param['value'])) {
            return Logger::getInstance()->error(
                    ['msg' => 'MRS011', 'no' => 'MRS011', 'param' => ['paramString' => 'MRS011']]
            );
        }
        $values = self::formatHashValue($param['value']);
        $result = self::$redisHandle->hSet($param['key'], $param['fields'], $values);
        if (false === $result) {
            return false;
        }
        return true;
    }

    /**
     * @param $param
     *
     * @return mixed
     */
    private static function hDel($param)
    {
        if (!isset($param['fields'])) {
            return self::del($param);
        }

        #TODO redis  删除批量field 暂无好方法
        if (is_array($param['fields'])) {
            $result = null;
            foreach ($param['fields'] as $field) {
                $result = self::$redisHandle->hDel($param['key'], $field);
            }
        } else {
            $result = self::$redisHandle->hDel($param['key'], $param['fields']);
        }

        return $result;
    }

    /**
     * @param $param
     *
     * @return array
     */
    private static function hmGet($param)
    {
        if (empty($param['fields'])) {
            return Logger::getInstance()->error(
                    ['msg' => 'MRS014', 'no' => 'MRS014', 'param' => ['paramString' => 'MRS014']]
            );
        }

        if ('*' == $param['fields']) {
            $result = self::$redisHandle->hGetAll($param['key']);
        } else {
            $fields = null;
            foreach ($param['fields'] as $field) {
                if (is_null($field) || false === $field) {
                    continue;
                }
                $fields[] = $field;
            }
            if (empty($fields)) {
                return [];
            }
            $result = self::$redisHandle->hmGet($param['key'], $fields);
        }

        self::sendErrorLog($result, $param);

        return self::formatHashResult($result);
    }

    /**
     * @param $param
     *
     * @return mixed
     */
    private static function hGetAll($param)
    {
        if (empty($param['key'])) {
            return Logger::getInstance()->error(
                    ['msg' => 'MRS023', 'no' => 'MRS023', 'param' => ['paramString' => 'MRS023']]
            );
        }

        $result = self::$redisHandle->hGetAll($param['key']);

        self::sendErrorLog($result, $param);

        return $result;
    }

    /**
     * @param $param
     *
     * @return mixed
     */
    private static function hmSet($param)
    {
        if (empty($param['value'])) {
            return Logger::getInstance()->error(
                    ['msg' => 'MRS015', 'no' => 'MRS015', 'param' => ['paramString' => 'MRS015']]
            );
        }
        $value  = self::formatHashMValue($param['value']);
        $result = self::$redisHandle->hmSet($param['key'], $value);
        return $result;
    }


    /**
     * @param $param
     *
     * @return mixed
     */
    private static function hIncrBy($param)
    {
        if (!isset($param['fields']) || empty($param['order'])) {
            return Logger::getInstance()->error(
                    ['msg' => 'MRS013', 'no' => 'MRS013', 'param' => ['paramString' => 'MRS013']]
            );
        }

        if ('incr' == $param['order']) {
            if (empty($param['value'])) {
                $param['value'] = 1;
            }
        } else {
            if (empty($param['value'])) {
                $param['value'] = -1;
            }
            if ($param['value'] > 0) {
                $param['value'] = 0 - $param['value'];
            }
        }

        $result = self::$redisHandle->hIncrBy($param['key'], $param['fields'], $param['value']);

        return $result;
    }


    /**
     * @param $param
     *
     * @return mixed
     */
    private static function hLen($param)
    {
        $result = self::$redisHandle->hLen($param['key']);
        return $result;
    }


    # List  
    /**
     * @param $param
     *
     * @return mixed
     */
    private static function lPop($param)
    {
        $result = self::$redisHandle->lPop($param['key']);
        return $result;
    }

    /**
     * @param $param
     *
     * @return mixed
     */
    private static function rPush($param)
    {
        if (empty($param['value'])) {
            return Logger::getInstance()->error(
                    ['msg' => 'MRS016', 'no' => 'MRS016', 'param' => ['paramString' => 'MRS016']]
            );
        }

        $result = self::$redisHandle->rPush($param['key'], $param['value']);
        return $result;
    }


    /**
     * @param $param
     *
     * @return mixed
     */
    private static function lRem($param)
    {
        if (empty($param['value'])) {
            return Logger::getInstance()->error(
                    ['msg' => 'MRS023', 'no' => 'MRS023', 'param' => ['paramString' => 'MRS023']]
            );
        }
        if (empty($param['count'])) {
            $param['count'] = 0;
        }
        if (empty($param['delAll'])) {
            $result = self::$redisHandle->lRem($param['key'], $param['value'], $param['count']);
        } else {
            $result = self::$redisHandle->del($param['key']);
        }
        return $result;
    }

    /**
     * @param $param
     *
     * @return mixed
     */
    private static function lRange($param)
    {
        empty($param['start']) && $param['start'] = 0;
        empty($param['end']) && $param['end'] = 0;

        $result = self::$redisHandle->lRange($param['key'], $param['start'], $param['end']);
        return $result;
    }

    /**
     * @param $param
     *
     * @return mixed
     */
    private static function lLen($param)
    {
        $result = self::$redisHandle->lSize($param['key']);
        return $result;
    }


    # Sets  
    /**
     * @param $param
     *
     * @return mixed
     */
    private static function sGet($param)
    {
        if (empty($param['fields'])) {
            return Logger::getInstance()->error(
                    ['msg' => 'MRS017', 'no' => 'MRS017', 'param' => ['paramString' => 'MRS017']]
            );
        }

        $result = self::$redisHandle->sIsMember($param['key'], $param['fields']);
        return $result;
    }

    /**
     * @param $param
     *
     * @return mixed
     */
    private static function sAdd($param)
    {
        if (empty($param['fields'])) {
            return Logger::getInstance()->error(
                    ['msg' => 'MRS025', 'no' => 'MRS025', 'param' => ['paramString' => 'MRS025']]
            );
        }

        $result = self::$redisHandle->sAdd($param['key'], $param['fields']);
        return $result;
    }


    /**
     * @param $param
     *
     * @return mixed
     */
    private static function sRem($param)
    {
        if (empty($param['fields'])) {
            return Logger::getInstance()->error(
                    ['msg' => 'MRS026', 'no' => 'MRS026', 'param' => ['paramString' => 'MRS026']]
            );
        }

        $result = self::$redisHandle->sAdd($param['key'], $param['fields']);
        return $result;
    }


    /**
     * @param $param
     *
     * @return mixed
     */
    private static function sLen($param)
    {
        # sCard
        $result = self::$redisHandle->sCard($param['key']);
        return $result;
    }


    # Zset  
    /**
     * @param $param
     *
     * @return bool
     */
    private static function zAdd($param)
    {
        if (empty($param['fields']) || !isset($param['value'])) {
            return Logger::getInstance()->error(
                    ['msg' => 'MRS018', 'no' => 'MRS018', 'param' => ['paramString' => 'MRS018']]
            );
        }

        $rankDefaultLen = C('rankDefaultLen', false);
        if (empty($rankDefaultLen)) {
            return self::$redisHandle->zAdd($param['key'], $param['value'], $param['fields']);
        }

        # 1. zLen
        # 2. if zLen <  AC::$rankDefaultLen return zAdd
        # 3. score = zRevRange(key, AC::$rankDefaultLen - 1, AC::$rankDefaultLen, TRUE)
        # 4. if value <= score  return TRUE
        # 5. zAdd zRem
        # 6. return TRUE
        $zLen = self::zLen($param);
        if ($zLen < $rankDefaultLen) {
            return self::$redisHandle->zAdd($param['key'], $param['value'], $param['fields']);
        }

        $lastIndex = $rankDefaultLen - 1;
        $lastScore = self::$redisHandle->zRevRange($param['key'], $lastIndex, $lastIndex, true);
        if (empty($lastScore)) {
            return self::$redisHandle->zAdd($param['key'], $param['value'], $param['fields']);
        }

        list($user, $score) = each($lastScore);
        if ($param['value'] <= $score) {
            return true;
        }

        self::$redisHandle->zAdd($param['key'], $param['value'], $param['fields']);
        self::$redisHandle->zRem($param['key'], $user);

        return true;
    }

    /**
     * @param $param
     *
     * @return mixed
     */
    private static function zGet($param)
    {
        if (empty($param['fields'])) {
            return Logger::getInstance()->error(
                    ['msg' => 'MRS020', 'no' => 'MRS020', 'param' => ['paramString' => 'MRS020']]
            );
        }

        $result = self::$redisHandle->zScore($param['key'], $param['fields']);
        return $result;
    }

    /**
     * @param $param
     *
     * @return mixed
     */
    private static function zmGet($param)
    {
        $result = self::$redisHandle->zRevRange($param['key'], 0, -1, true);
        return $result;
    }


    /**
     * @param $param
     *
     * @return mixed
     */
    private static function zIncrby($param)
    {
        if (empty($param['fields']) || !isset($param['value'])) {
            return Logger::getInstance()->error(
                    ['msg' => 'MRS019', 'no' => 'MRS019', 'param' => ['paramString' => 'MRS019']]
            );
        }

        $result = self::$redisHandle->zAdd($param['key'], $param['value'], $param['fields']);
        return $result;
    }

    /**
     * @param $param
     *
     * @return mixed
     */
    private static function zLen($param)
    {
        # zCard /zSize
        $result = self::$redisHandle->zCard($param['key']);
        return $result;
    }


    /**
     * @param $param
     *
     * @return mixed
     */
    private static function zRem($param)
    {
        if (empty($param['fields'])) {
            return Logger::getInstance()->error(
                    ['msg' => 'MRS021', 'no' => 'MRS021', 'param' => ['paramString' => 'MRS021']]
            );
        }

        $result = self::$redisHandle->zDelete($param['key'], $param['fields']);
        return $result;
    }

    /**
     * @param $param
     *
     * @return int|string]
     */
    private static function zRankTop($param)
    {
        if (empty($param['fields'])) {
            return Logger::getInstance()->error(
                    ['msg' => 'MRS022', 'no' => 'MRS022', 'param' => ['paramString' => 'MRS022']]
            );
        }
        if (empty($param['order'])) {
            $param['order'] = 'desc';
        }

        if ('desc' == $param['order']) {
            $result = self::$redisHandle->zRevRank($param['key'], $param['fields']);
        } else {
            $result = self::$redisHandle->zRank($param['key'], $param['fields']);
        }

        # rankIndex start 0, so need + 1
        is_numeric($result) && $result++;
        return $result;
    }


    /**
     * @param $result
     *
     * @return array
     */
    private static function formatHashResult($result)
    {

        if (is_string($result) && substr($result, 0, 8) == 'J7:ARRAY') {
            return unserialize(substr($result, 8));
        } elseif (is_array($result)) {
            foreach ($result as $key => $value) {
                if (is_string($value) && substr($value, 0, 8) == 'J7:ARRAY') {
                    $result[$key] = unserialize(substr($value, 8));
                }
            }

            return $result;
        }

        return $result;
    }

    /**
     * @param $values
     *
     * @return array
     */
    private static function formatHashMValue($values)
    {
        foreach ($values as $k => $v) {
            is_array($v) && $values[$k] = 'J7:ARRAY' . serialize($v);
        }

        return $values;
    }

    /**
     * @param $value
     *
     * @return string
     */
    private static function formatHashValue($value)
    {
        is_array($value) && $value = 'J7:ARRAY' . serialize($value);

        return $value;
    }

    /**
     * @param $result
     * @param $param
     */
    private static function sendErrorLog($result, $param)
    {
    }
}

