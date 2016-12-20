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
     * @var Redis
     */
    private $redis = null;


    private $cachePrefix;

    private $pconnect = false;

    /**
     *
     */
    public function __destruct()
    {
        if (isset($this->redis) && !is_null($this->redis) && !$this->pconnect) { //长连接的话 不需要关闭
            $this->redis->close();
        }
    }

    /**
     * @param        $config
     * @param string $cachePrefix
     *
     * @return mixed
     */
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
                    self::$instance[$instanceNameSet]->redis->select($config['database']);
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
        return call_user_func_array([$this->redis, $method], $params);
    }

    /**
     * @param $int
     *
     * @return bool
     */
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

    /**
     * @param        $key
     * @param string $perstring
     *
     * @return array|bool|mixed|string
     */
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

    /**
     * 仅限于key是字符串模式 //数组模式请使用 mset
     * @param     $key
     * @param     $value
     * @param int $limit
     *
     * @return bool|int
     */
    public function set($key, $value, $limit = 2592000)
    {
        if (is_null($value)) {
            return $this->redis->del($key);
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

    /**
     * @param $key
     * @param $value
     *
     * @return bool
     */
    public function setnx($key, $value)
    {
        if (is_array($value)) {
            $value = 'SC:ARR' . serialize($value);
        }
        return $this->redis->setnx($this->cachePrefix . $key, $value);
    }

    /**
     * msetnx
     * @param $keyvaluearray
     *
     * @return bool
     */
    public function mset($keyvaluearray)
    {
        $newarr = [];
        foreach ($keyvaluearray as $key => $value) {
            if (is_array($value)) {
                $value = 'SC:ARR' . serialize($value);
            }
            $newarr[$this->cachePrefix . $key] = $value;
        }
        return $this->redis->mset($newarr);
    }

    /**
     * @param        $key
     * @param string $perstring
     */
    public function delete($key, $perstring = '')
    {
        $this->redis->delete($key, $perstring);
    }

    /**
     * @param        $key
     * @param string $perstring
     *
     * @return int
     */
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

        return $this->redis->del($key);
    }

    /**
     * @param $key
     * @param $value
     *
     * @return int
     */
    public function append($key, $value)
    {
        $key = $this->cachePrefix . $key;
        return $this->redis->append($key, $value);
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
        $key = $this->cachePrefix . $key;
        return $this->redis->getRange($key, $keystart, $keyend);
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
        $key = $this->cachePrefix . $key;
        return $this->redis->setRange($key, $offset, $value);
    }

    /**
     *
     * @param $key
     *
     * @return mixed
     */
    public function strlen($key)
    {
        return $this->strlen($this->cachePrefix . $key);
    }

    /**
     * @param       $key
     * @param array $sortkey
     *
     * @return array
     */
    public function sort($key, $sortkey = [])
    {
        $key = $this->cachePrefix . $key;
        return $this->redis->sort($key, $sortkey);
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
        $ret = $this->redis->sort($key, $sortkey);
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
        $ret = $this->redis->sort($key, $sortkey);
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
        return $this->redis->exists($this->cachePrefix . $key);
    }

    /**
     * @param     $key
     * @param int $incrnum
     *
     * @return int
     */
    public function incr($key, $incrnum = 1) // incr, incrBy
    {
        return $this->redis->incrBy($this->cachePrefix . $key, $incrnum);
    }

    /**
     * @param     $key
     * @param int $incrnum
     *
     * @return int
     */
    public function decr($key, $incrnum = 1) //decr, decrBy
    {
        return $this->redis->decrBy($this->cachePrefix . $key, $incrnum);
    }

    /**
     * list操作
     * 左插入list
     * @param $key
     * @param $listvalue
     *
     * @return int
     */
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

    /**
     * 右插入list
     * @param $key
     * @param $listvalue
     *
     * @return int
     */
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

    /**
     * @param $list
     * @param $list2
     *
     * @return mixed
     */
    public function rpoplpush($list, $list2)
    {
        return $this->rpoplpush($this->cachePrefix . $list, $this->cachePrefix . $list2);
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
        $key = $this->cachePrefix . $key;
        return $this->redis->lRange($key, $start, $end);
    }

    /**
     * lPushx rPushx
     * @param $key
     *
     * @return string
     */
    public function lPop($key)
    {
        $key = $this->cachePrefix . $key;
        return $this->redis->lPop($key);
    }

    /**
     * @param $key
     *
     * @return string
     */
    public function rPop($key)
    {
        $key = $this->cachePrefix . $key;
        return $this->redis->rPop($key);
    }

    /**
     * blPop, brPop
     * @param $key
     */
    public function lSize($key)
    {
        $key = $this->cachePrefix . $key;
        $this->redis->lSize($key);
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
        $key = $this->cachePrefix . $key;
        return $this->redis->lIndex($key, $index);
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
        $key = $this->cachePrefix . $key;
        return $this->redis->lSet($key, $index, $value);
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
        $key = $this->cachePrefix . $key;
        return $this->redis->lTrim($key, $start, $stop);
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
        $key = $this->cachePrefix . $key;
        return $this->redis->lRem($key, $value, $count);
    }

    /**
     * @param      $key
     * @param      $pivot
     * @param      $value
     * @param bool $before
     *
     * @return int
     */
    public function lInsert($key, $pivot, $value, $before = true)
    {
        $key = $this->cachePrefix . $key;
        if ($before) {
            return $this->redis->lRem($key, Redis::BEFORE, $pivot, $value);
        } else {
            return $this->redis->lRem($key, Redis::AFTER, $pivot, $value);
        }
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
        return $this->redis->hMset($this->cachePrefix . $key, $valuearr);
    }

    /**
     * @param $key
     * @param $valuearr
     *
     * @return array
     */
    public function hMGet($key, $valuearr) //array('field1', 'field2')
    {
        $ret = $this->redis->hMGet($this->cachePrefix . $key, $valuearr);
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
        $ret = $this->redis->hGetAll($this->cachePrefix . $key);
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
        return $this->redis->hExists($this->cachePrefix . $key, $mkey);
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
        return $this->redis->hIncrBy($this->cachePrefix . $key, $mkey, $value);
    }

    /**
     * @param $key
     *
     * @return array
     */
    public function hVals($key) //类似 PHP's array_values().
    {
        return $this->redis->hVals($this->cachePrefix . $key);
    }

    /**
     * @param $key
     *
     * @return array
     */
    public function hKeys($key)
    {
        return $this->redis->hKeys($this->cachePrefix . $key);
    }

    /**
     * @param $key
     *
     * @return int
     */
    public function hLen($key)
    {
        return $this->redis->hLen($this->cachePrefix . $key);
    }

    /**
     * 删除hash里面的子健
     * @param $key
     * @param $mkey
     *
     * @return int
     */
    public function hDel($key, $mkey)
    {
        return $this->redis->hDel($this->cachePrefix . $key, $mkey);
    }

    /**
     * get hash里面的子健
     * @param $key
     * @param $mkey
     *
     * @return mixed|string
     */
    public function hGet($key, $mkey)
    {
        $value = $this->redis->hGet($this->cachePrefix . $key, $mkey);
        if (is_string($value) && substr($value, 0, 6) == 'SC:ARR') {
            $value = unserialize(substr($value, 6));
        }
        return $value;
    }

    /**
     *
     * set hash里面的子健
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
        return $this->redis->hSet($this->cachePrefix . $key, $mkey, $value);
    }

    /**
     * setxn hash里面的子健
     * @param $key
     * @param $mkey
     * @param $value
     *
     * @return bool
     */
    public function hSetNx($key, $mkey, $value)
    {
        if (is_array($value)) {
            $value = 'SC:ARR' . serialize($value);
        }
        return $this->redis->hSetNx($this->cachePrefix . $key, $mkey, $value);
    }


    /**
     * sAdd ...... stored set结构，无序stored
     * @param $key
     * @param $value
     *
     * @return int
     */
    public function sAdd($key, $value)
    {
        return $this->redis->sAdd($this->cachePrefix . $key, $value);
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
        return $this->redis->sRem($this->cachePrefix . $key, $value);
    }

    /**
     * 将$value从$key移到$key1
     * @param $key
     * @param $key1
     * @param $value
     *
     * @return bool
     */
    public function sMove($key, $key1, $value)
    {
        return $this->redis->sMove($this->cachePrefix . $key, $this->cachePrefix . $key1, $value);
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
     * @param $key
     * @param $value
     *
     * @return bool
     */
    public function sIsMember($key, $value)
    {
        return $this->redis->sIsMember($this->cachePrefix . $key, $value);
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
        return $this->redis->sSize($this->cachePrefix . $key);
    }

    /**
     * @param $key
     *
     * @return string
     */
    public function sPop($key)
    {
        return $this->redis->sPop($this->cachePrefix . $key);
    }

    /**
     * @param $key
     *
     * @return string
     */
    public function sRandMember($key)
    {
        return $this->redis->sRandMember($this->cachePrefix . $key);
    }

    /**
     * 交集成员的列表
     * @return mixed
     */
    public function sInter()
    {
        $params = func_get_args();
        $newp   = [];
        foreach ($params as $eachp) {
            $newp[] = $this->cachePrefix . $eachp;
        }
        return call_user_func_array([$this->redis, 'sInter'], $newp);
    }

    public function sInterStore()
    {
        $params = func_get_args();
        $newp   = [];
        foreach ($params as $eachp) {
            $newp[] = $this->cachePrefix . $eachp;
        }
        return call_user_func_array([$this->redis, 'sInterStore'], $newp);
    }

    /**
     * 并集成员的列表
     * @return mixed
     */
    public function sUnion()
    {
        $params = func_get_args();
        $newp   = [];
        foreach ($params as $eachp) {
            $newp[] = $this->cachePrefix . $eachp;
        }
        return call_user_func_array([$this->redis, 'sUnion'], $newp);
    }

    /**
     * @return mixed
     */
    public function sUnionStore()
    {
        $params = func_get_args();
        $newp   = [];
        foreach ($params as $eachp) {
            $newp[] = $this->cachePrefix . $eachp;
        }
        return call_user_func_array([$this->redis, 'sUnionStore'], $newp);
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
            $newp[] = $this->cachePrefix . $eachp;
        }
        return call_user_func_array([$this->redis, 'sDiff'], $newp);
    }

    /**
     * @return mixed
     */
    public function sDiffStore()
    {
        $params = func_get_args();
        $newp   = [];
        foreach ($params as $eachp) {
            $newp[] = $this->cachePrefix . $eachp;
        }
        return call_user_func_array([$this->redis, 'sDiffStore'], $newp);
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
        return $this->redis->sMembers($this->cachePrefix . $key);
    }

    /**
     * 有序集(Sorted Set)
     * @param $key
     * @param $score
     * @param $value
     *
     * @return int
     */
    public function zAdd($key, $score, $value)
    {
        return $this->redis->zAdd($this->cachePrefix . $key, $score, $value);
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
        return $this->redis->zRange($this->cachePrefix . $key, $start, $end, $withscores);
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
        return $this->redis->zRevRange($this->cachePrefix . $key, $start, $end, $withscores);
    }

    /**
     * @param $key
     * @param $value
     *
     * @return int
     */
    public function zRem($key, $value)
    {
        return $this->redis->zRem($this->cachePrefix . $key, $value);
    }

    /**
     * @param $key
     * @param $value
     *
     * @return int
     */
    public function zDelete($key, $value)
    {
        return $this->redis->zDelete($this->cachePrefix . $key, $value);
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
        return $this->redis->zRangeByScore($this->cachePrefix . $key, $start, $end, $options);
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
        $ret = $this->redis->zRangeByScore($this->cachePrefix . $key, $start, $end, $options);
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
        return $this->redis->zCount($this->cachePrefix . $key, $scorestart, $scoreend);
    }

    /**
     * @param $key
     * @param $scorestart
     * @param $scoreend
     */
    public function zRemRangeByScore($key, $scorestart, $scoreend)
    {
        $this->redis->zDeleteRangeByScore($key, $scorestart, $scoreend);
    }

    /**
     * @param $key
     * @param $scorestart
     * @param $scoreend
     */
    public function zDeleteRangeByScore($key, $scorestart, $scoreend)
    {
        $this->redis->zDeleteRangeByScore($this->cachePrefix . $key, $scorestart, $scoreend);
    }

    /**
     * @param     $key
     * @param int $start
     * @param int $end
     */
    public function zRemRangeByRank($key, $start = 0, $end = -1)
    {
         $this->redis->zDeleteRangeByRank($key, $start, $end);
    }

    /**
     * @param     $key
     * @param int $start
     * @param int $end
     */
    public function zDeleteRangeByRank($key, $start = 0, $end = -1)
    {
         $this->redis->zDeleteRangeByRank($this->cachePrefix . $key, $start, $end);
    }

    /**
     * @param $key
     */
    public function zCard($key)
    {
        $this->redis->zSize($key);
    }

    /**
     * @param $key
     */
    public function zSize($key)
    {
        $this->redis->zSize($this->cachePrefix . $key);
    }

    /**
     * @param $key
     * @param $value
     *
     * @return float
     */
    public function zScore($key, $value)
    {
        return $this->redis->zScore($this->cachePrefix . $key, $value);
    }

    /**
     * @param $key
     * @param $value
     *
     * @return int
     */
    public function zRank($key, $value)
    {
        return $this->redis->zRank($this->cachePrefix . $key, $value);
    }

    /**
     * @param $key
     * @param $value
     *
     * @return int
     */
    public function zRevRank($key, $value)
    {
        return $this->redis->zRevRank($this->cachePrefix . $key, $value);
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
        return $this->redis->zIncrBy($this->cachePrefix . $key, $addscore, $value);
    }

    /**
     * 复杂模式取并集，$Weights是乘法因子，个数为$keysarr,会把score相乘,赋予结果集
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
            $newarr[] = $this->cachePrefix . $eachkey;
        }
        return $this->redis->zUnion($this->cachePrefix . $outkey, $newarr, $Weights, $aggregateFunction);
    }

    /**
     * 交集
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
            $newarr[] = $this->cachePrefix . $eachkey;
        }
        return $this->redis->zInter($this->cachePrefix . $outkey, $newarr, $Weights, $aggregateFunction);
    }

    /**
     * @param $type
     * @param $key
     *
     * @return string
     */
    public function object($type, $key)
    {
        return $this->redis->object($type, $this->cachePrefix . $key);
    }

    /**
     * @param $key
     * @param $time
     *
     * @return bool
     */
    public function expire($key, $time)
    {
        return $this->redis->expire($this->cachePrefix . $key, $time);
    }

    /**
     * @param $key
     * @param $time
     */
    public function setTimeout($key, $time)
    {
        $this->redis->setTimeout($this->cachePrefix . $key, $time);
    }

    /**
     * @param $key
     * @param $time
     *
     * @return bool
     */
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
