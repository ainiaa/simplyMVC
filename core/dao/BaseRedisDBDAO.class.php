<?php

/**
 * Class BaseRedisDBDAO Redis DB 数据存储类   (redis作为db的缓存)
 * @author Jeff.Liu<jeff.liu.guo@gmail.com>
 * @version 0.1
 */
abstract class BaseRedisDBDAO extends BaseDBDAO
{
    /**
     * @var LocalCache
     */
    private $LocalCache;

    protected $uId;

    protected $storager;
    // 默认数据
    protected $defaultValue = [];

    // 这个必须唯一 否则存储会有问题
    protected $_pk;
    protected $_pk_auto = false;//pk为自动增长

    // 主键（不同于db的pk，这个主要用来存储的时候 作为 key的一部分。)
    protected $_sk;
    // 外键(和$_pk组合 可以唯一定位一条数据)

    protected $useRedisCacheFlag = null;
    // 这个变量不能自己设置 会根据配置项 自己设定好的

    protected $useCache = 'redis'; // 1:redis 2:other

    /**
     * @var SmvcRedisHelper
     */
    protected $Redis;

    /**
     * 构造函数
     *
     * @param null $mode 抛弃
     * @param null $uid  抛弃
     */
    public function __construct($mode = null, $uid = null)
    {
        parent::__construct();
        $this->LocalCache = LocalCache::getData($this->tableName);
        $this->uId        = LocalCache::getData('uId');
    }

    /**
     * todo 这个需要处理
     * 初始化系统, 分表等
     *
     * @param number $uid
     *
     * @return mixed
     */
    protected function init($uid)
    {
        $this->uId = $uid;

        if (empty($this->Redis)) {
            $redisConf = C('redis', []);
            if (empty($redisConf)) {
                $redisConf = [
                        'host'     => '127.0.0.1',
                        'port'     => '3306',
                        'pconnect' => false,
                ];
            }
            $this->Redis = new Redis();
            if (isset($redisConf['pconnect']) && $redisConf['pconnect']) {
                $this->Redis->pconnect($redisConf['host'], $redisConf['port']);
            } else {
                $this->Redis->connect($redisConf['host'], $redisConf['port']);
            }
        }

        return $this->storager;
    }

    /**
     * @return Redis
     */
    public function getStorageInstance()
    {
        if (empty($this->storager)) {
            $redisConf = C('session.redis', []);
            if (empty($redisConf)) {
                $redisConf = [
                        'host'     => '127.0.0.1',
                        'port'     => '3306',
                        'pconnect' => false,
                ];
            }
            $this->storager = new Redis();
            if (isset($redisConf['pconnect']) && $redisConf['pconnect']) {
                $this->storager->pconnect($redisConf['host'], $redisConf['port']);
            } else {
                $this->storager->connect($redisConf['host'], $redisConf['port']);
            }
        }

        return $this->storager;
    }

    /**
     * 获得cache主键
     * @return string
     */
    public function getPK()
    {
        return $this->realTableName . ':' . $this->uId;
    }

    /**
     * 添加数据
     *
     * @param array $datas
     *
     * @param bool  $getLastInsertId
     *
     * @return mixed
     * @throws Exception
     */
    public function add($datas, $getLastInsertId = false)
    {
        return $this->addData($datas, $getLastInsertId);
    }

    /**
     * 添加数据
     *
     * @param array   $datas
     * @param boolean $getLastInsertId
     *
     * @throws Exception
     * @return int
     */
    public function addData($datas, $getLastInsertId = false)
    {
        if (isset($datas[$this->pk])) {
            $pkValue = $datas[$this->pk];
            $this->init($pkValue);

            $datas = array_merge($this->defaultValue, $datas);
            $ret   = BaseDBDAO::add($datas);

            if ($ret > 0) {
                $key = $this->getPK();
                if ($this->isMulit()) {
                    $sk = $datas[$this->sk];
                    if (isset($this->LocalCache[$key])) {
                        $tem                    = $this->LocalCache[$key];
                        $tem[$sk]               = $datas;
                        $this->LocalCache[$key] = $tem;
                    }

                    if ($this->Redis->exists($key)) {
                        $this->Redis->hMset($key, [$sk => $datas]);
                    }
                } else {
                    $this->LocalCache[$key] = $datas;
                    $this->Redis->hMset($key, $datas);
                }
            } else {
                $this->errorInfo(__METHOD__, func_get_args(), 'addData failure');
            }
            return $ret;
        } else {
            throw new Exception('pk empty');
        }
    }

    /**
     * 获得 pk sk的值
     *
     * @author Jeff Liu<jeff.liu.guo@gmail.com>
     *
     * @param array $data
     * @param array $options
     *
     * @return array
     */
    private function getPkAndSk($data = array(), $options = array())
    {
        $pk = null;
        if (isset($data[$this->_pk])) {
            $pk = $data[$this->_pk];
        } else if (isset($options[$this->_pk])) {
            $pk = $options[$this->_pk];
        }
        $sk = null;
        if (isset($data[$this->_sk])) {
            $sk = $data[$this->_sk];
        } else if (isset($options[$this->_sk])) {
            $sk = $options[$this->_sk];
        }

        if (is_null($pk)) {
            $this->errorInfo(__METHOD__, func_get_args(), 'pk is null');
        }
        if ($this->isMulit() && is_null($sk)) {
            $this->errorInfo(__METHOD__, func_get_args(), 'sk is null');
        }

        return ['pk' => $pk, 'sk' => $sk];

    }

    /**
     * 更新数据 Usage: $model = D('XXXModel'); $where = array( 'node_id' =>
     * $node_id, 'id' => $id, ); $model->updateByPk($data, $where);
     * //where只能包含pk和sk PS： 请不要使用 字符串形式的。 格式要统一
     *
     * @author Jeff Liu<jeff.liu.guo@gmail.com>
     *
     * @param array $data
     * @param array $options
     *
     * @return int
     */
    public function updateByPk($data, $options = array())
    {
        $pkAndSk = $this->getPkAndSk($data, $options);
        $pk      = $pkAndSk['pk'];
        $sk      = $pkAndSk['sk'];
        $this->init($pk);

        if (count($this->defaultValue) !== count($data)) { // 不完整完整字段, 合并更新
            if ($this->isMulit()) {
                $odatas = $this->get($pk, $sk);
            } else {
                $odatas = $this->getByPk($pk);
            }
            if (empty($odatas)) {
                $data = array_merge($this->defaultValue, $data);
            } else {
                $data = array_merge($this->defaultValue, $odatas, $data);
                unset($odatas);
            }
        }

        BaseDBDAO::where($options);
        $ret = BaseDBDAO::save($data); // 保存数据到db
        if ($ret) { // 保存成功
            $key = $this->getStorageKey($pk);
            if ($this->isMulit()) {
                $tmp                    = $this->LocalCache[$key];
                $tmp[$sk]               = $data;
                $this->LocalCache[$key] = $tmp;
                $this->Redis->hSet($key, $sk, $data);
                unset($sk, $tmp);
            } else {
                $this->LocalCache[$key][$pk] = $data;
                $this->Redis->hSet($key, $pk, $data);
            }
            unset($data, $key);
        }

        return $ret;
    }
    /**
     * 获得cache主键
     *
     * @param $pk
     *
     * @return string
     */
    public function getStorageKey($pk)
    {
        return $this->getTableName(). ':' . $pk;
    }

    /**
     * 是否使用redis缓存
     *
     * @author Jeff Liu<jeff.liu.guo@gmail.com>
     * @return bool
     */
    public function needUseCache()
    {
        if (is_null($this->useRedisCacheFlag)) {
            $useRedisCache           = C('useRedisCache');
            $this->useRedisCacheFlag = false;
            if ($useRedisCache) {
                $useRedisCacheControl = C('useRedisCacheControl');
                if ($useRedisCacheControl == 'config') {
                    $tableName           = $this->getTableName();
                    $redisCacheTableList = C('redisCacheTableList');
                    if (isset($redisCacheTableList[$tableName]) && $redisCacheTableList[$tableName]) {
                        $this->useRedisCacheFlag = true;
                    } else {
                        $this->useRedisCacheFlag = false;
                    }
                } else if ($useRedisCacheControl == 'model') {
                    if ($this->useCache == 'redis') {
                        $this->useRedisCacheFlag = true;
                    }
                }
            }
        }
        return $this->useRedisCacheFlag;
    }

    /**
     * 按照主键获取数据
     *
     * @author Jeff Liu<jeff.liu.guo@gmail.com>
     *
     * @param string $pk
     *
     * @return array|mixed
     */
    public function getByPk($pk)
    {
        $key = $this->getStorageKey($pk);
        if (isset($this->LocalCache[$key])) { // 本地缓存数据
            return $this->LocalCache[$key];
        } else { // 尝试从redis 或者 db 中读取数据
            if ($this->needUseCache()) {
                $this->init($pk);
                $datas = $this->Redis->hGetAll($key);
                if (!isset($datas[$pk])) { // 尝试从mysql中读取数据
                    $datas = parent::getByPk($pk); // 数据结构已经处理好了
                    if ($datas) { // 将mysql中的数据缓存到redis和本地缓存中
                        $this->LocalCache[$key] = $datas;
                        $this->Redis->hMset($key, $datas);
                    }
                }
                return $datas;
            } else {
                return $this->getOne([$this->_pk => $pk]);
            }
        }
    }

    /**
     * 删除数据，直接调用bypk
     *
     * @param       $ids
     * @param array $pinfo
     *
     * @return bool
     */
    public function deleteBySk($ids, $pinfo = [])
    {
        return $this->deleteByPk($ids, $pinfo);
    }

    /**
     * 根据给定的主键值或由主键值组成的数组，删除相应的记录。
     *
     * @param mixed $condition 主键值或主键值数组。
     * @param array $pinfo
     *
     * @return boolean
     */
    public function deleteByPk($condition, $pinfo = [])
    {
        if (is_array($condition)) {
            $pk = $condition[$this->pk];
            $this->init($pk);
            $sk  = $condition[$this->sk];
            $key = $this->getPK();
            $ret = BaseDBDAO::deleteByPk($condition);

            if ($ret > 0) {
                //清除缓存
                $this->Redis->hdel($key, $sk);

                if (isset($this->LocalCache[$key])) {   //清除本地缓存
                    $tem = $this->LocalCache[$key];
                    unset($tem[$sk]);
                    $this->LocalCache[$key] = $tem;
                }
            } else {
                $this->errorInfo(__METHOD__, func_get_args(), 'delete failure');
            }
        } else {
            $this->init($condition);
            $key = $this->getPK();
            $ret = BaseDBDAO::deleteByPk($condition);

            if ($ret > 0) {
                $this->Redis->delete($key);
                unset($this->LocalCache[$key]);
            } else {
                $this->errorInfo(__METHOD__, func_get_args(), 'delete failure');
            }
        }

        return $ret;
    }

    /**
     * todo 错误信息记录
     *
     * @author Jeff Liu<jeff.liu.guo@gmail.com>
     *
     * @param $method
     * @param $params
     * @param $msg
     */
    private function errorInfo($method, $params, $msg)
    {
        $class = get_called_class();
        list (, $method) = explode('::', $method);
        $methodInfo = $class . '->' . $method . '(' . var_export($params, 1) . '); msg:' . $msg;
        if (defined('PHP_OS') && strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            error_log($methodInfo . PHP_EOL, 3, '/tmp/redis.op.log');
        } else {
            error_log($methodInfo . PHP_EOL, 3, 'd:/redis.op.log');
        }
    }

    /**
     * 根据联合主键查询数据
     *
     * @param number $pkid
     * @param number $skid
     *
     * @return array
     */
    public function get($pkid, $skid)
    {
        $infos = [$this->pk => $pkid, $this->sk => $skid];

        return $this->getBySk($infos);
    }

    /**
     * 读取单个用户或者联合主键数据
     *
     * @param number|array $infos
     *
     * @return array
     */
    public function getBySk($infos)
    {
        if (is_array($infos)) {
            $uid = isset($infos[$this->pk]) ? $infos[$this->pk] : null;
            $sk  = isset($infos[$this->sk]) ? $infos[$this->sk] : null;
            $this->init($uid);

            $key = $this->getPK();

            if (!isset($this->LocalCache[$key])) {
                $datas = $this->Redis->hGetAll($key);

                if (!$datas) {
                    $datas = BaseDBDAO::getByPk($uid);
                    if ($datas) {   //fix local & redis cache
                        $this->Redis->hMset($key, $datas);
                        $this->LocalCache[$key] = $datas;
                    }
                } else {   //fix local cache
                    $this->LocalCache[$key] = $datas;
                }

                return isset($datas[$sk]) ? $datas[$sk] : null;
            } else {
                return isset($this->LocalCache[$key][$sk]) ? $this->LocalCache[$key][$sk] : null;
            }
        } else {
            $this->init($infos);

            $key = $this->getPK();
            if (!isset($this->LocalCache[$key])) {
                $datas = $this->Redis->hGetAll($key);

                if (!$datas) {
                    $datas = BaseDBDAO::getByPk($infos);
                    if ($datas) {    //fix local & redis cache
                        $this->Redis->hMset($key, $datas);
                        $this->LocalCache[$key] = $datas;
                    }
                } else {    //fix local cache
                    $this->LocalCache[$key] = $datas;
                }
            } else {
                $datas = $this->LocalCache[$key];
            }
        }
        unset($infos);
        return $datas;
    }

    /**
     * @param number $pk
     *
     * @return int
     */
    protected function getCountBySk($pk)
    {
        return count($this->getBySk($pk));
    }

    /**
     * 设置主ID
     *
     * @param number $uid
     */
    protected function setMainId($uid)
    {
        $this->uId = $uid;
    }
}