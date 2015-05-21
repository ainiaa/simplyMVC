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
    private static $instance = array();

    /**
     * @var Redis
     */
    private $redis = null;


    private $cachePrefix;

    private $pconnect = false;

    public function __destruct()
    {
        if (isset($this->redis) && !is_null($this->redis) && !$this->pconnect) { //长连接的话 不需要关闭
            $this->redis->close();
        }
    }

    public static function instance($config, $cachePrefix = '')
    {
        $instanceNameSet = hash('crc32', serialize($config));
        if (!isset(self::$instance[$instanceNameSet])) {
            $tmp              = new SmvcRedis();
            $tmp->redis       = new Redis();
            $tmp->cachePrefix = $cachePrefix;
            if (isset($config['pconnect']) && $config['pconnect']) {
                $d             = $tmp->redis->pconnect($config['host'], $config['port']);
                $tmp->pconnect = true;
            } else {
                $d = $tmp->redis->connect($config['host'], $config['port']);
            }
            if ($d) {
                self::$instance[$instanceNameSet] = $tmp;
                if (isset($config['database']) && is_int($config['database'])) {
                    self::$instance[$instanceNameSet]->select($config['database']);
                }
            }
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
        return call_user_func_array(array($this->redis, $method), $params);
    }


    public function select($int)
    {
        return $this->redis->select($int);
    }

    /**
     * @param $key
     *
     * @return int
     */
    public function type($key)
    {
        return $this->redis->type($this->cachePrefix . $key);
    }

    public function get($key, $perstring = '')
    {
        $perstring = $this->cachePrefix . $perstring;
        if (is_array($key)) {
            if ($perstring) {
                foreach ($key as $i_key => $i_value) {
                    $key[$i_key] = $perstring . $i_value;
                }
            }
            $ret = $this->redis->getMultiple($key);
            if ($ret) {
                array_walk($ret, 'checkArr');
            }
            return $ret;
        } else {
            $ret = $this->redis->get($perstring . $key);
            if (substr($ret, 0, 6) == 'SC:ARR') {
                return unserialize(substr($ret, 6));
            } else {
                return $ret;
            }
        }
    }

    //仅限于key是字符串模式 //数组模式请使用 mset
    public function set($key, $value, $limit = 2592000)
    {
        if (is_null($value)) {
            return $this->del($key);
        }
        if (is_array($value)) {
            $value = 'SC:ARR' . serialize($value);
        }
        if (is_string($value) || is_int($value)) {
            if ($limit) {
                return ($this->redis->setex($this->cachePrefix . $key, $limit, $value));
            } else {
                return ($this->redis->set($this->cachePrefix . $key, $value));
            }
        } else {
            return false;
        }
    }

    public function setnx($key, $value)
    {
        if (is_array($value)) {
            $value = 'SC:ARR' . serialize($value);
        }
        return $this->redis->setnx($this->cachePrefix . $key, $value);
    }

    //msetnx
    public function mset($keyvaluearray)
    {
        $newarr = array();
        foreach ($keyvaluearray as $key => $value) {
            if (is_array($value)) {
                $value = 'SC:ARR' . serialize($value);
            }
            $newarr[$this->cachePrefix . $key] = $value;
        }
        return $this->redis->mset($newarr);
    }

    public function delete($key, $perstring = '')
    {
        return $this->del($key, $perstring);
    }

    public function del($key, $perstring = '')
    {
        $perstring = $this->cachePrefix . $perstring;
        if (is_array($key)) {
            if ($perstring) {
                foreach ($key as $i_key => $i_value) {
                    $key[$i_key] = $perstring . $i_value;
                }
            }
        } else {
            $key = $this->cachePrefix . $key;
        }

        return ($this->redis->delete($key));
    }

    public function append($key, $value)
    {
        $key = $this->cachePrefix . $key;
        return $this->redis->append($key, $value);
    }

    public function getRange($key, $keystart, $keyend) //截获字符串
    {
        $key = $this->cachePrefix . $key;
        return $this->redis->getRange($key, $keystart, $keyend);
    }

    public function setRange($key, $offset, $value)
    {
        $key = $this->cachePrefix . $key;
        return $this->redis->setRange($key, $offset, $value);
    }

    public function strlen($key)
    {
        return $this->strlen($this->cachePrefix . $key);
    }

    public function sort($key, $sortkey = array())
    {
        $key = $this->cachePrefix . $key;
        return $this->redis->sort($key, $sortkey);
    }

    public function sortget($key, $sortkey = array()) //建议使用这个，这个根据get的结果会进行重组数据
    {
        if (!isset($sortkey['by'])) {
            $sortkey['by'] = microtime(true);
        }
        $ret = $this->redis->sort($key, $sortkey);
        if (!is_array($ret)) {
            return array();
        }
        if (isset($sortkey['get']) && is_array($sortkey['get']) && count($sortkey['get']) > 1) {
            $tecount = 0;
            $outret  = $smarr = array();
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
                    $smarr    = array();
                }
            }
            $ret = $smarr = null;
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

    public function sortget2($key, $sortkey = array()) //建议使用这个，这个根据get的结果会进行重组数据
    {
        if (!isset($sortkey['by'])) {
            $sortkey['by'] = microtime(true);
        }
        $ret = $this->redis->sort($key, $sortkey);
        if (!is_array($ret)) {
            return array();
        }
        if (isset($sortkey['get']) && is_array($sortkey['get']) && count($sortkey['get']) > 1) {
            $i       = 1;
            $tecount = 0;
            $outret  = $smarr = array();
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
            $ret = $smarr = null;
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


    public function exists($key)
    {
        return $this->redis->exists($this->cachePrefix . $key);
    }

    public function incr($key, $incrnum = 1) // incr, incrBy
    {
        return $this->redis->incrBy($this->cachePrefix . $key, $incrnum);
    }

    public function decr($key, $incrnum = 1) //decr, decrBy
    {
        return $this->redis->decrBy($this->cachePrefix . $key, $incrnum);
    }

    //list操作
    //左插入list
    public function lPush($key, $listvalue)
    {
        $key = $this->cachePrefix . $key;
        if (is_string($listvalue)) {
            return $this->redis->lPush($key, $listvalue);
        }
        foreach ($listvalue as $eachv) {
            $this->redis->lPush($key, $eachv);
        }
        return count($listvalue);
    }

    //右插入list
    public function rPush($key, $listvalue)
    {
        $key = $this->cachePrefix . $key;
        if (is_string($listvalue)) {
            return $this->redis->rPush($key, $listvalue);
        }
        foreach ($listvalue as $eachv) {
            $this->redis->rPush($key, $eachv);
        }
        return count($listvalue);
    }

    public function rpoplpush($list, $list2)
    {
        return $this->rpoplpush($this->cachePrefix . $list, $this->cachePrefix . $list2);
    }

    public function lGetRange($key, $start, $end)
    {
        return $this->lRange($key, $start, $end);
    }

    public function lRange($key, $start = 0, $end = -1)
    {
        $key = $this->cachePrefix . $key;
        return $this->redis->lRange($key, $start, $end);
    }

    //lPushx rPushx
    public function lPop($key)
    {
        $key = $this->cachePrefix . $key;
        return $this->redis->lPop($key);
    }

    public function rPop($key)
    {
        $key = $this->cachePrefix . $key;
        return $this->redis->rPop($key);
    }

    //blPop, brPop
    public function lSize($key)
    {
        $key = $this->cachePrefix . $key;
        return $this->redis->lSize($key);
    }

    public function lGet($key, $index)
    {
        return $this->lIndex($key, $index);
    }

    public function lIndex($key, $index)
    {
        $key = $this->cachePrefix . $key;
        return $this->redis->lIndex($key, $index);
    }

    public function lSet($key, $index, $value)
    {
        $key = $this->cachePrefix . $key;
        return $this->redis->lSet($key, $index, $value);
    }

    public function lTrim($key, $start, $stop)
    {
        $key = $this->cachePrefix . $key;
        return $this->redis->lTrim($key, $start, $stop);
    }

    public function lRemove($key, $value, $count = 0)
    {
        return $this->lRem($key, $value, $count);
    }

    public function lRem($key, $value, $count = 0)
    {
        $key = $this->cachePrefix . $key;
        return $this->redis->lRem($key, $value, $count);
    }

    public function lInsert($key, $pivot, $value, $before = true)
    {
        $key = $this->cachePrefix . $key;
        if ($before) {
            return $this->redis->lRem($key, Redis::BEFORE, $pivot, $value);
        } else {
            return $this->redis->lRem($key, Redis::AFTER, $pivot, $value);
        }
    }


    //Hash
    public function hMset($key, $valuearr) //array('name' => 'Joe', 'salary' => 2000) //key=>value
    {
        array_walk($valuearr, 'checkArrToStr');
        return $this->redis->hMset($this->cachePrefix . $key, $valuearr);
    }

    public function hMGet($key, $valuearr) //array('field1', 'field2')
    {
        $ret = $this->redis->hMGet($this->cachePrefix . $key, $valuearr);
        if ($ret) {
            array_walk($ret, 'checkArr');
        }
        return $ret;
    }

    public function hGetAll($key)
    {
        $ret = $this->redis->hGetAll($this->cachePrefix . $key);
        if ($ret) {
            array_walk($ret, 'checkArr');
        }
        return $ret;
    }

    public function hExists($key, $mkey)
    {
        return $this->redis->hExists($this->cachePrefix . $key, $mkey);
    }

    public function hIncrBy($key, $mkey, $value)
    {
        return $this->redis->hIncrBy($this->cachePrefix . $key, $mkey, $value);
    }

    public function hVals($key) //类似 PHP's array_values().
    {
        return $this->redis->hVals($this->cachePrefix . $key);
    }

    public function hKeys($key)
    {
        return $this->redis->hKeys($this->cachePrefix . $key);
    }

    public function hLen($key)
    {
        return $this->redis->hLen($this->cachePrefix . $key);
    }

    public function hDel($key, $mkey) //删除hash里面的子健
    {
        return $this->redis->hDel($this->cachePrefix . $key, $mkey);
    }

    public function hGet($key, $mkey) //get hash里面的子健
    {
        $value = $this->redis->hGet($this->cachePrefix . $key, $mkey);
        if (is_string($value) && substr($value, 0, 6) == 'SC:ARR') {
            $value = unserialize(substr($value, 6));
        }
        return $value;
    }

    public function hSet($key, $mkey, $value) //set hash里面的子健
    {
        if (is_array($value)) {
            $value = 'SC:ARR' . serialize($value);
        }
        return $this->redis->hSet($this->cachePrefix . $key, $mkey, $value);
    }

    public function hSetNx($key, $mkey, $value) //setxn hash里面的子健
    {
        if (is_array($value)) {
            $value = 'SC:ARR' . serialize($value);
        }
        return $this->redis->hSetNx($this->cachePrefix . $key, $mkey, $value);
    }


    //sAdd ...... stored set结构，无序stored
    public function sAdd($key, $value)
    {
        return $this->redis->sAdd($this->cachePrefix . $key, $value);
    }

    public function sRemove($key, $value)
    {
        return $this->sRem($key, $value);
    }

    public function sRem($key, $value)
    {
        return $this->redis->sRem($this->cachePrefix . $key, $value);
    }

    public function sMove($key, $key1, $value) //将$value从$key移到$key1
    {
        return $this->redis->sMove($this->cachePrefix . $key, $this->cachePrefix . $key1, $value);
    }

    public function sContains($key, $value)
    {
        return $this->sIsMember($key, $value);
    }

    public function sIsMember($key, $value) //将$value从$key移到$key1
    {
        return $this->redis->sIsMember($this->cachePrefix . $key, $value);
    }

    public function sCard($key)
    {
        return $this->sSize($key);
    }

    public function sSize($key)
    {
        return $this->redis->sSize($this->cachePrefix . $key);
    }

    public function sPop($key)
    {
        return $this->redis->sPop($this->cachePrefix . $key);
    }

    public function sRandMember($key)
    {
        return $this->redis->sRandMember($this->cachePrefix . $key);
    }

    //交集成员的列表
    public function sInter()
    {
        $params = func_get_args();
        $newp   = array();
        foreach ($params as $eachp) {
            $newp[] = $this->cachePrefix . $eachp;
        }
        return call_user_func_array(array($this->redis, 'sInter'), $newp);
    }

    public function sInterStore()
    {
        $params = func_get_args();
        $newp   = array();
        foreach ($params as $eachp) {
            $newp[] = $this->cachePrefix . $eachp;
        }
        return call_user_func_array(array($this->redis, 'sInterStore'), $newp);
    }

    //并集成员的列表
    public function sUnion()
    {
        $params = func_get_args();
        $newp   = array();
        foreach ($params as $eachp) {
            $newp[] = $this->cachePrefix . $eachp;
        }
        return call_user_func_array(array($this->redis, 'sUnion'), $newp);
    }

    public function sUnionStore()
    {
        $params = func_get_args();
        $newp   = array();
        foreach ($params as $eachp) {
            $newp[] = $this->cachePrefix . $eachp;
        }
        return call_user_func_array(array($this->redis, 'sUnionStore'), $newp);
    }

    //交集成员的列表  返回一个集合的全部成员，该集合是所有给定集合的差集
    public function sDiff()
    {
        $params = func_get_args();
        $newp   = array();
        foreach ($params as $eachp) {
            $newp[] = $this->cachePrefix . $eachp;
        }
        return call_user_func_array(array($this->redis, 'sDiff'), $newp);
    }

    public function sDiffStore()
    {
        $params = func_get_args();
        $newp   = array();
        foreach ($params as $eachp) {
            $newp[] = $this->cachePrefix . $eachp;
        }
        return call_user_func_array(array($this->redis, 'sDiffStore'), $newp);
    }

    public function sGetMembers($key)
    {
        return $this->sMembers($key);
    }

    public function sMembers($key)
    {
        return $this->redis->sMembers($this->cachePrefix . $key);
    }

    //有序集(Sorted Set)
    public function zAdd($key, $score, $value)
    {
        return $this->redis->zAdd($this->cachePrefix . $key, $score, $value);
    }

    public function zRange($key, $start = 0, $end = -1, $withscores = false)
    {
        return $this->redis->zRange($this->cachePrefix . $key, $start, $end, $withscores);
    }

    public function zRevRange($key, $start = 0, $end = -1, $withscores = false)
    {
        return $this->redis->zRevRange($this->cachePrefix . $key, $start, $end, $withscores);
    }

    public function zRem($key, $value)
    {
        return $this->redis->zRem($this->cachePrefix . $key, $value);
    }

    public function zDelete($key, $value)
    {
        return $this->redis->zDelete($this->cachePrefix . $key, $value);
    }

    public function zRangeByScore($key, $start, $end, $options = array())
    {
        return $this->redis->zRangeByScore($this->cachePrefix . $key, $start, $end, $options);
    }

    public function zRevRangeByScore($key, $start, $end, $options = array())
    {
        $ret = $this->redis->zRangeByScore($this->cachePrefix . $key, $start, $end, $options);
        return array_reverse($ret);
    }

    public function zCount($key, $scorestart, $scoreend)
    {
        return $this->redis->zCount($this->cachePrefix . $key, $scorestart, $scoreend);
    }

    public function zRemRangeByScore($key, $scorestart, $scoreend)
    {
        return $this->zDeleteRangeByScore($key, $scorestart, $scoreend);
    }

    public function zDeleteRangeByScore($key, $scorestart, $scoreend)
    {
        return $this->redis->zDeleteRangeByScore($this->cachePrefix . $key, $scorestart, $scoreend);
    }

    public function zRemRangeByRank($key, $start = 0, $end = -1)
    {
        return $this->zDeleteRangeByRank($key, $start, $end);
    }

    public function zDeleteRangeByRank($key, $start = 0, $end = -1)
    {
        return $this->redis->zDeleteRangeByRank($this->cachePrefix . $key, $start, $end);
    }

    public function zCard($key)
    {
        return $this->zSize($key);
    }

    public function zSize($key)
    {
        return $this->redis->zSize($this->cachePrefix . $key);
    }

    public function zScore($key, $value)
    {
        return $this->redis->zScore($this->cachePrefix . $key, $value);
    }

    public function zRank($key, $value)
    {
        return $this->redis->zRank($this->cachePrefix . $key, $value);
    }

    public function zRevRank($key, $value)
    {
        return $this->redis->zRevRank($this->cachePrefix . $key, $value);
    }

    public function zIncrBy($key, $addscore, $value)
    {
        return $this->redis->zIncrBy($this->cachePrefix . $key, $addscore, $value);
    }

    //复杂模式取并集，$Weights是乘法因子，个数为$keysarr,会把score相乘,赋予结果集,
    public function zUnion($outkey, $keysarr = array(), $Weights = array(), $aggregateFunction)
    {
        $newarr = array();
        foreach ($keysarr as $eachkey) {
            $newarr[] = $this->cachePrefix . $eachkey;
        }
        return $this->redis->zUnion($this->cachePrefix . $outkey, $newarr, $Weights, $aggregateFunction);
    }

    //交集
    public function zInter($outkey, $keysarr = array(), $Weights = array(), $aggregateFunction)
    {
        $newarr = array();
        foreach ($keysarr as $eachkey) {
            $newarr[] = $this->cachePrefix . $eachkey;
        }
        return $this->redis->zInter($this->cachePrefix . $outkey, $newarr, $Weights, $aggregateFunction);
    }

    public function object($type, $key)
    {
        return $this->redis->object($type, $this->cachePrefix . $key);
    }

    public function expire($key, $time)
    {
        return $this->redis->expire($this->cachePrefix . $key, $time);
    }

    public function setTimeout($key, $time)
    {
        return $this->redis->setTimeout($this->cachePrefix . $key, $time);
    }

    public function expireAt($key, $time)
    {
        return $this->redis->expireAt($this->cachePrefix . $key, $time);
    }
}

if (!function_exists('checkArr')) {
    function checkArr(&$value, $key)
    {
        if (is_string($value) && substr($value, 0, 6) == 'SC:ARR') {
            $value = unserialize(substr($value, 6));
        }
    }
}

if (!function_exists('checkArrToStr')) {
    function checkarrtostr(&$value, $key)
    {
        if (is_array($value)) {
            $value = 'SC:ARR' . serialize($value);
        }
    }
}
