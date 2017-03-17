<?php

/**
 * 依赖 Redis 组件
 * 命令参考 https://github.com/nicolasff/phpredis
 */
class SmvcRedis
{
    /**
     * @var Redis
     */
    private static $instance = [];

    /**
     * @var SmvcRedisHelper
     */
    private $RedisHelper = null;

    private $pconnect = false;

    /**
     *
     */
    public function __destruct()
    {
        if (isset($this->RedisHelper) && !is_null($this->RedisHelper) && !$this->pconnect) { //长连接的话 不需要关闭
            $this->RedisHelper->close();
        }
    }

    /**
     * @param        $config
     *
     * @return mixed
     */
    public static function getInstance($config)
    {
        $instanceNameSet = hash('crc32', serialize($config));
        if (!isset(self::$instance[$instanceNameSet])) {
            $redisHelper                      = SmvcRedisHelper::getInstance();
            self::$instance[$instanceNameSet] = $redisHelper;
        }
        return self::$instance[$instanceNameSet];
    }


    /**
     * @author Jeff Liu
     *
     * @param $method
     * @param $params
     *
     * @return mixed
     */
    public function __call($method, $params)
    {
        return call_user_func_array([$this->RedisHelper, $method], $params);
    }

    /**
     * @param $index
     *
     * @return bool
     */
    public function select($index)
    {
        return $this->RedisHelper->select($index);
    }

    /**
     * @param $key
     *
     * @return int
     */
    public function type($key)
    {
        return $this->RedisHelper->type($key);
    }

    /**
     * @param        $key
     *
     * @return string
     */
    public function get($key)
    {
        return $this->RedisHelper->get($key);
    }

    /**
     * 仅限于key是字符串模式 //数组模式请使用 mset
     *
     * @param     $key
     * @param     $value
     * @param int $limit
     *
     * @return int
     */
    public function set($key, $value, $limit = 2592000)
    {
        if ($limit) {
            return $this->RedisHelper->setex($key, $limit, $value);
        } else {
            return $this->RedisHelper->set($key, $value);
        }
    }

    /**
     * @param $key
     * @param $value
     *
     * @return bool
     */
    public function setnx($key, $value)
    {
        return $this->RedisHelper->setnx($key, $value);
    }

    /**
     * msetnx
     *
     * @param $array
     *
     * @return bool
     */
    public function mset($array)
    {
        return $this->RedisHelper->mset($array);
    }

    /**
     * @param        $key
     */
    public function delete($key)
    {
        $this->RedisHelper->delete($key);
    }

    /**
     * @param        $key
     *
     * @return int
     */
    public function del($key)
    {
        return $this->RedisHelper->del($key);
    }

    /**
     * @param $key
     * @param $value
     *
     * @return int
     */
    public function append($key, $value)
    {
        return $this->RedisHelper->append($key, $value);
    }

    /**
     * @param $key
     * @param $keystart
     * @param $keyend
     *
     * @return string
     */
    public function getRange($key, $keystart, $keyend) //截获字符串
    {
        return $this->RedisHelper->getRange($key, $keystart, $keyend);
    }

    /**
     * @param $key
     * @param $offset
     * @param $value
     *
     * @return string
     */
    public function setRange($key, $offset, $value)
    {
        return $this->RedisHelper->setRange($key, $offset, $value);
    }

    /**
     *
     * @param $key
     *
     * @return mixed
     */
    public function strlen($key)
    {
        return $this->strlen($key);
    }

    /**
     * @param       $key
     * @param array $sortkey
     *
     * @return array
     */
    public function sort($key, $sortkey = [])
    {
        return $this->RedisHelper->sort($key, $sortkey);
    }

    /**
     * @param       $key
     * @param array $sortkey
     *
     * @return array
     */
    public function sortget($key, $sortkey = []) //建议使用这个，这个根据get的结果会进行重组数据
    {
        if (!isset($sortkey['by'])) {
            $sortkey['by'] = microtime(true);
        }
        $ret = $this->RedisHelper->sort($key, $sortkey);
        if (!is_array($ret)) {
            return [];
        }
        if (isset($sortkey['get']) && is_array($sortkey['get']) && count($sortkey['get']) > 1) {
            $tecount = 0;
            $outret  = $smarr = [];
            $ecount  = count($sortkey['get']);
            foreach ($ret as $eachret) {
                if (is_string($eachret) && substr($eachret, 0, 6) == 'SC:ARR') {
                    $eachret = unserialize(substr($eachret, 6));
                }
                $smarr[] = $eachret;
                if ($tecount < $ecount - 1) {
                    $tecount++;
                } else {
                    $tecount  = 0;
                    $outret[] = $smarr;
                    $smarr    = [];
                }
            }
            $smarr = null;
            return $outret;
        } else {
            foreach ($ret as $knum => $eachret) {
                if (is_string($eachret) && substr($eachret, 0, 6) == 'SC:ARR') {
                    $eachret = unserialize(substr($eachret, 6));
                }
                $ret[$knum] = $eachret;
            }
            return $ret;
        }
    }

    /**
     * @param       $key
     * @param array $sortkey
     *
     * @return array
     */
    public function sortget2($key, $sortkey = []) //建议使用这个，这个根据get的结果会进行重组数据
    {
        if (!isset($sortkey['by'])) {
            $sortkey['by'] = microtime(true);
        }
        $ret = $this->RedisHelper->sort($key, $sortkey);
        if (!is_array($ret)) {
            return [];
        }
        if (isset($sortkey['get']) && is_array($sortkey['get']) && count($sortkey['get']) > 1) {
            $i       = 1;
            $tecount = 0;
            $outret  = $smarr = [];
            $ecount  = count($sortkey['get']);

            foreach ($ret as $eachret) {
                if (is_string($eachret) && substr($eachret, 0, 6) == 'SC:ARR') {
                    $eachret = unserialize(substr($eachret, 6));
                }
                $smarr[] = $eachret;
                if ($i % $ecount == 0) {
                    $outret[$tecount][] = $eachret;
                    $tecount++;
                    $i = 1;
                } else {
                    $outret[$tecount][] = $eachret;
                    $i++;
                }
            }
            $smarr = null;
            return $outret;
        } else {
            foreach ($ret as $knum => $eachret) {
                if (is_string($eachret) && substr($eachret, 0, 6) == 'SC:ARR') {
                    $eachret = unserialize(substr($eachret, 6));
                }
                $ret[$knum] = $eachret;
            }
            return $ret;
        }
    }


    /**
     * @param $key
     *
     * @return bool
     */
    public function exists($key)
    {
        return $this->RedisHelper->exists($key);
    }

    /**
     * @param     $key
     * @param int $incrnum
     *
     * @return int
     */
    public function incr($key, $incrnum = 1) // incr, incrBy
    {
        return $this->RedisHelper->incrBy($key, $incrnum);
    }

    /**
     * @param     $key
     * @param int $incrnum
     *
     * @return int
     */
    public function decr($key, $incrnum = 1) //decr, decrBy
    {
        return $this->RedisHelper->decrBy($key, $incrnum);
    }

    /**
     * list操作
     * 左插入list
     *
     * @param $key
     * @param $listvalue
     *
     * @return int
     */
    public function lPush($key, $listvalue)
    {
        return $this->RedisHelper->lPush($key, $listvalue);
    }

    /**
     * 右插入list
     *
     * @param $key
     * @param $listvalue
     *
     * @return int
     */
    public function rPush($key, $listvalue)
    {
        return $this->RedisHelper->rPush($key, $listvalue);
    }

    /**
     * @param $list
     * @param $list2
     *
     * @return mixed
     */
    public function rpoplpush($list, $list2)
    {
        return $this->rpoplpush($list, $list2);
    }

    /**
     * @param $key
     * @param $start
     * @param $end
     *
     * @return array
     */
    public function lGetRange($key, $start, $end)
    {
        return $this->lRange($key, $start, $end);
    }

    /**
     * @param     $key
     * @param int $start
     * @param int $end
     *
     * @return array
     */
    public function lRange($key, $start = 0, $end = -1)
    {
        return $this->RedisHelper->lRange($key, $start, $end);
    }

    /**
     * lPushx rPushx
     *
     * @param $key
     *
     * @return string
     */
    public function lPop($key)
    {
        return $this->RedisHelper->lPop($key);
    }

    /**
     * @param $key
     *
     * @return string
     */
    public function rPop($key)
    {
        return $this->RedisHelper->rPop($key);
    }

    /**
     * blPop, brPop
     *
     * @param $key
     */
    public function lSize($key)
    {
        $this->RedisHelper->lSize($key);
    }

    /**
     * @param $key
     * @param $index
     *
     * @return String
     */
    public function lGet($key, $index)
    {
        return $this->lIndex($key, $index);
    }

    /**
     *
     * @param $key
     * @param $index
     *
     * @return String
     */
    public function lIndex($key, $index)
    {
        return $this->RedisHelper->lIndex($key, $index);
    }

    /**
     * @param $key
     * @param $index
     * @param $value
     *
     * @return bool
     */
    public function lSet($key, $index, $value)
    {
        return $this->RedisHelper->lSet($key, $index, $value);
    }

    /**
     * @param $key
     * @param $start
     * @param $stop
     *
     * @return array
     */
    public function lTrim($key, $start, $stop)
    {
        return $this->RedisHelper->lTrim($key, $start, $stop);
    }

    /**
     * @param     $key
     * @param     $value
     * @param int $count
     *
     * @return int
     */
    public function lRemove($key, $value, $count = 0)
    {
        return $this->lRem($key, $value, $count);
    }

    /**
     * @param     $key
     * @param     $value
     * @param int $count
     *
     * @return int
     */
    public function lRem($key, $value, $count = 0)
    {
        return $this->RedisHelper->lRem($key, $value, $count);
    }

    /**
     * @param $key
     * @param $position
     * @param $pivot
     * @param $value
     *
     * @return int
     */
    public function lInsert($key, $position, $pivot, $value)
    {
        return $this->RedisHelper->lInsert($key, $position, $pivot, $value);
    }


    /**
     *
     * @param $key
     * @param $valuearr
     *
     * @return bool
     */
    public function hMset($key, $valuearr) //array('name' => 'Joe', 'salary' => 2000) //key=>value
    {
        array_walk($valuearr, 'checkArrToStr');
        return $this->RedisHelper->hMset($key, $valuearr);
    }

    /**
     * @param $key
     * @param $valuearr
     *
     * @return array
     */
    public function hMGet($key, $valuearr) //array('field1', 'field2')
    {
        $ret = $this->RedisHelper->hMGet($key, $valuearr);
        if ($ret) {
            array_walk($ret, 'checkArr');
        }
        return $ret;
    }

    /**
     * @param $key
     *
     * @return array
     */
    public function hGetAll($key)
    {
        $ret = $this->RedisHelper->hGetAll($key);
        if ($ret) {
            array_walk($ret, 'checkArr');
        }
        return $ret;
    }

    /**
     * @param $key
     * @param $mkey
     *
     * @return bool
     */
    public function hExists($key, $mkey)
    {
        return $this->RedisHelper->hExists($key, $mkey);
    }

    /**
     * @param $key
     * @param $mkey
     * @param $value
     *
     * @return int
     */
    public function hIncrBy($key, $mkey, $value)
    {
        return $this->RedisHelper->hIncrBy($key, $mkey, $value);
    }

    /**
     * @param $key
     *
     * @return array
     */
    public function hVals($key) //类似 PHP's array_values().
    {
        return $this->RedisHelper->hVals($key);
    }

    /**
     * @param $key
     *
     * @return array
     */
    public function hKeys($key)
    {
        return $this->RedisHelper->hKeys($key);
    }

    /**
     * @param $key
     *
     * @return int
     */
    public function hLen($key)
    {
        return $this->RedisHelper->hLen($key);
    }

    /**
     * 删除hash里面的子健
     *
     * @param $key
     * @param $mkey
     *
     * @return int
     */
    public function hDel($key, $mkey)
    {
        return $this->RedisHelper->hDel($key, $mkey);
    }

    /**
     * get hash里面的子健
     *
     * @param $key
     * @param $mkey
     *
     * @return mixed|string
     */
    public function hGet($key, $mkey)
    {
        $value = $this->RedisHelper->hGet($key, $mkey);
        if (is_string($value) && substr($value, 0, 6) == 'SC:ARR') {
            $value = unserialize(substr($value, 6));
        }
        return $value;
    }

    /**
     *
     * set hash里面的子健
     *
     * @param $key
     * @param $mkey
     * @param $value
     *
     * @return int
     */
    public function hSet($key, $mkey, $value)
    {
        if (is_array($value)) {
            $value = 'SC:ARR' . serialize($value);
        }
        return $this->RedisHelper->hSet($key, $mkey, $value);
    }

    /**
     * setxn hash里面的子健
     *
     * @param $key
     * @param $mkey
     * @param $value
     *
     * @return bool
     */
    public function hSetNx($key, $mkey, $value)
    {
        return $this->RedisHelper->hSetNx($key, $mkey, $value);
    }


    /**
     * sAdd ...... stored set结构，无序stored
     *
     * @param $key
     * @param $value
     *
     * @return int
     */
    public function sAdd($key, $value)
    {
        return $this->RedisHelper->sAdd($key, $value);
    }

    /**
     * @param $key
     * @param $value
     *
     * @return int
     */
    public function sRemove($key, $value)
    {
        return $this->sRem($key, $value);
    }

    /**
     *
     * @param $key
     * @param $value
     *
     * @return int
     */
    public function sRem($key, $value)
    {
        return $this->RedisHelper->sRem($key, $value);
    }

    /**
     * 将$value从$key移到$key1
     *
     * @param $srcKey
     * @param $dstKey
     * @param $member
     *
     * @return bool
     */
    public function sMove($srcKey, $dstKey, $member)
    {
        return $this->RedisHelper->sMove($srcKey, $dstKey, $member);
    }

    /**
     * @param $key
     * @param $value
     *
     * @return bool
     */
    public function sContains($key, $value)
    {
        return $this->sIsMember($key, $value);
    }

    /**
     * 将$value从$key移到$key1
     *
     * @param $key
     * @param $value
     *
     * @return bool
     */
    public function sIsMember($key, $value)
    {
        return $this->RedisHelper->sIsMember($key, $value);
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function sCard($key)
    {
        return $this->sSize($key);
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function sSize($key)
    {
        return $this->RedisHelper->sSize($key);
    }

    /**
     * @param $key
     *
     * @return string
     */
    public function sPop($key)
    {
        return $this->RedisHelper->sPop($key);
    }

    /**
     * @param      $key
     *
     * @param null $count
     *
     * @return string
     */
    public function sRandMember($key, $count = null)
    {
        return $this->RedisHelper->sRandMember($key, $count);
    }

    /**
     * 交集成员的列表
     * @return mixed
     */
    public function sInter($args)
    {
        $args = func_get_args();
        return $this->RedisHelper->sInter($args);
    }

    public function sInterStore()
    {
        $args = func_get_args();
        return $this->RedisHelper->sInterStore($args);
    }

    /**
     * 并集成员的列表
     * @return mixed
     */
    public function sUnion()
    {
        $args = func_get_args();
        return $this->RedisHelper->sUnion($args);
    }

    /**
     * @return mixed
     */
    public function sUnionStore($dstKey, $args)
    {
        $args = func_get_args();
        return $this->RedisHelper->sUnion($dstKey, $args);
    }

    /**
     * 交集成员的列表  返回一个集合的全部成员，该集合是所有给定集合的差集
     * @return mixed
     */
    public function sDiff()
    {
        $params = func_get_args();
        $newp   = [];
        foreach ($params as $eachp) {
            $newp[] = $eachp;
        }
        return call_user_func_array([$this->RedisHelper, 'sDiff'], $newp);
    }

    /**
     * @return mixed
     */
    public function sDiffStore()
    {
        $params = func_get_args();
        $newp   = [];
        foreach ($params as $eachp) {
            $newp[] = $eachp;
        }
        return call_user_func_array([$this->RedisHelper, 'sDiffStore'], $newp);
    }

    /**
     * @param $key
     *
     * @return array
     */
    public function sGetMembers($key)
    {
        return $this->sMembers($key);
    }

    /**
     * @param $key
     *
     * @return array
     */
    public function sMembers($key)
    {
        return $this->RedisHelper->sMembers($key);
    }

    /**
     * 有序集(Sorted Set)
     *
     * @param $key
     * @param $score
     * @param $value
     *
     * @return int
     */
    public function zAdd($key, $score, $value)
    {
        return $this->RedisHelper->zAdd($key, $score, $value);
    }

    /**
     * @param      $key
     * @param int  $start
     * @param int  $end
     * @param bool $withscores
     *
     * @return array
     */
    public function zRange($key, $start = 0, $end = -1, $withscores = false)
    {
        return $this->RedisHelper->zRange($key, $start, $end, $withscores);
    }

    /**
     * @param      $key
     * @param int  $start
     * @param int  $end
     * @param bool $withscores
     *
     * @return array
     */
    public function zRevRange($key, $start = 0, $end = -1, $withscores = false)
    {
        return $this->RedisHelper->zRevRange($key, $start, $end, $withscores);
    }

    /**
     * @param $key
     * @param $args
     *
     * @return int
     *
     */
    public function zRem($key, $args)
    {
        return $this->RedisHelper->zRem($key, $args);
    }

    /**
     * @param $key
     * @param $args
     *
     * @return int
     *
     */
    public function zDelete($key, $args)
    {
        return $this->RedisHelper->zDelete($key, $args);
    }

    /**
     * @param       $key
     * @param       $start
     * @param       $end
     * @param array $options
     *
     * @return array
     */
    public function zRangeByScore($key, $start, $end, $options = [])
    {
        return $this->RedisHelper->zRangeByScore($key, $start, $end, $options);
    }

    /**
     * @param       $key
     * @param       $start
     * @param       $end
     * @param array $options
     *
     * @return array
     */
    public function zRevRangeByScore($key, $start, $end, $options = [])
    {
        $ret = $this->RedisHelper->zRangeByScore($key, $start, $end, $options);
        return array_reverse($ret);
    }

    /**
     * @param $key
     * @param $scorestart
     * @param $scoreend
     *
     * @return int
     */
    public function zCount($key, $scorestart, $scoreend)
    {
        return $this->RedisHelper->zCount($key, $scorestart, $scoreend);
    }

    /**
     * @param $key
     * @param $scorestart
     * @param $scoreend
     */
    public function zRemRangeByScore($key, $scorestart, $scoreend)
    {
        $this->RedisHelper->zDeleteRangeByScore($key, $scorestart, $scoreend);
    }

    /**
     * @param $key
     * @param $scorestart
     * @param $scoreend
     */
    public function zDeleteRangeByScore($key, $scorestart, $scoreend)
    {
        $this->RedisHelper->zDeleteRangeByScore($key, $scorestart, $scoreend);
    }

    /**
     * @param     $key
     * @param int $start
     * @param int $end
     */
    public function zRemRangeByRank($key, $start = 0, $end = -1)
    {
        $this->RedisHelper->zDeleteRangeByRank($key, $start, $end);
    }

    /**
     * @param     $key
     * @param int $start
     * @param int $end
     */
    public function zDeleteRangeByRank($key, $start = 0, $end = -1)
    {
        $this->RedisHelper->zDeleteRangeByRank($key, $start, $end);
    }

    /**
     * @param $key
     */
    public function zCard($key)
    {
        $this->RedisHelper->zCard($key);
    }

    /**
     * @param $key
     */
    public function zSize($key)
    {
        $this->zCard($key);
    }

    /**
     * @param $key
     * @param $value
     *
     * @return float
     */
    public function zScore($key, $value)
    {
        return $this->RedisHelper->zScore($key, $value);
    }

    /**
     * @param $key
     * @param $value
     *
     * @return int
     */
    public function zRank($key, $value)
    {
        return $this->RedisHelper->zRank($key, $value);
    }

    /**
     * @param $key
     * @param $value
     *
     * @return int
     */
    public function zRevRank($key, $value)
    {
        return $this->RedisHelper->zRevRank($key, $value);
    }

    /**
     * @param $key
     * @param $addscore
     * @param $value
     *
     * @return float
     */
    public function zIncrBy($key, $addscore, $value)
    {
        return $this->RedisHelper->zIncrBy($key, $addscore, $value);
    }

    /**
     * 复杂模式取并集，$Weights是乘法因子，个数为$keysarr,会把score相乘,赋予结果集
     *
     * @param       $outkey
     * @param array $keysarr
     * @param array $Weights
     * @param       $aggregateFunction
     *
     * @return int
     */
    public function zUnion($outkey, $keysarr = [], $Weights = [], $aggregateFunction)
    {
        $newarr = [];
        foreach ($keysarr as $eachkey) {
            $newarr[] = $eachkey;
        }
        return $this->RedisHelper->zUnion($outkey, $newarr, $Weights, $aggregateFunction);
    }

    /**
     * 交集
     *
     * @param       $outkey
     * @param array $keysarr
     * @param array $Weights
     * @param       $aggregateFunction
     *
     * @return int
     */
    public function zInter($outkey, $keysarr = [], $Weights = [], $aggregateFunction)
    {
        $newarr = [];
        foreach ($keysarr as $eachkey) {
            $newarr[] = $eachkey;
        }
        return $this->RedisHelper->zInter($outkey, $newarr, $Weights, $aggregateFunction);
    }

    /**
     * @param $type
     * @param $key
     *
     * @return string
     */
    public function object($type, $key)
    {
        return $this->RedisHelper->object($type, $key);
    }

    /**
     * @param $key
     * @param $time
     *
     * @return bool
     */
    public function expire($key, $time)
    {
        return $this->RedisHelper->expire($key, $time);
    }

    /**
     * @param $key
     * @param $time
     */
    public function setTimeout($key, $time)
    {
        $this->RedisHelper->setTimeout($key, $time);
    }

    /**
     * @param $key
     * @param $time
     *
     * @return bool
     */
    public function expireAt($key, $time)
    {
        return $this->RedisHelper->expireAt($key, $time);
    }
}

if (!function_exists('checkArr')) {
    function checkArr(&$value)
    {
        if (is_string($value) && substr($value, 0, 6) == 'SC:ARR') {
            $value = unserialize(substr($value, 6));
        }
    }
}

if (!function_exists('checkArrToStr')) {
    function checkarrtostr(&$value)
    {
        if (is_array($value)) {
            $value = 'SC:ARR' . serialize($value);
        }
    }
}
