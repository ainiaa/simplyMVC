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
    private $localCache;

    protected $uId;

    protected $storager;

    protected $defaultValue = [];

    /**
     * @var Redis
     */
    protected $redis;

    /**
     * 构造函数
     *
     * @param null $mode 抛弃
     * @param null $uid  抛弃
     */
    public function __construct($mode = null, $uid = null)
    {
        parent::__construct();
        $this->localCache = LocalCache::getData($this->tableName);
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

        if (empty($this->redis)) {
            $redisConf = C('redis', []);
            if (empty($redisConf)) {
                $redisConf = [
                        'host'     => '127.0.0.1',
                        'port'     => '3306',
                        'pconnect' => false,
                ];
            }
            $this->redis = new Redis();
            if (isset($redisConf['pconnect']) && $redisConf['pconnect']) {
                $this->redis->pconnect($redisConf['host'], $redisConf['port']);
            } else {
                $this->redis->connect($redisConf['host'], $redisConf['port']);
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
            $ret   = parent::add($datas);

            if ($ret > 0) {
                $key = $this->getPK();
                if ($this->isMulit()) {
                    $sk = $datas[$this->sk];
                    if (isset($this->localCache[$key])) {
                        $tem                    = $this->localCache[$key];
                        $tem[$sk]               = $datas;
                        $this->localCache[$key] = $tem;
                    }

                    if ($this->redis->exists($key)) {
                        $this->redis->hMset($key, [$sk => $datas]);
                    }
                } else {
                    $this->localCache[$key] = $datas;
                    $this->redis->hMset($key, $datas);
                }
            } else {
                $this->errorInfo(__METHOD__, func_get_args());
            }
            return $ret;
        } else {
            throw new Exception('pk empty');
        }
    }

    /**
     * 更新数据 Usage: $model = D('XXXModel'); $where = array( 'node_id' =>
     * $node_id, 'id' => $id, ); $model->updateByPk($data, $where);
     * //where只能包含pk和sk PS： 请不要使用 字符串形式的。 格式要统一
     *
     * @author Jeff Liu<liuwy@imageco.com.cn>
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

        $ret = parent::where($options)->save($data); // 保存数据到db
        if ($ret) { // 保存成功
            $key = $this->getStorageKey($pk);
            if ($this->isMulit()) {
                $tmp                    = $this->LocalCache[$key];
                $tmp[$sk]               = $data;
                $this->LocalCache[$key] = $tmp;
                $this->redis->hSet($key, $sk, $data);
                unset($sk, $tmp);
            } else {
                $this->LocalCache[$key][$pk] = $data;
                $this->redis->hSet($key, $pk, $data);
            }
            unset($data, $key);
        }

        return $ret;
    }

    /**
     * 按照主键获取数据
     *
     * @author Jeff Liu<liuwy@imageco.com.cn>
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
                $datas = $this->redis->hGetAll($key);
                if (!isset($datas[$pk])) { // 尝试从mysql中读取数据
                    $datas = parent::getByPk($pk); // 数据结构已经处理好了
                    if ($datas) { // 将mysql中的数据缓存到redis和本地缓存中
                        $this->LocalCache[$key] = $datas;
                        $this->redis->hMset($key, $datas);
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
            $ret = parent::deleteByPk($condition);

            if ($ret > 0) {
                //清除缓存
                $this->redis->hdel($key, $sk);

                if (isset($this->localCache[$key])) {   //清除本地缓存
                    $tem = $this->localCache[$key];
                    unset($tem[$sk]);
                    $this->localCache[$key] = $tem;
                }
            } else {
                $this->errorInfo(__METHOD__, func_get_args());
            }
        } else {
            $this->init($condition);
            $key = $this->getPK();
            $ret = parent::deleteByPk($condition);

            if ($ret > 0) {
                $this->redis->delete($key);
                unset($this->localCache[$key]);
            } else {
                $this->errorInfo(__METHOD__, func_get_args());
            }
        }

        return $ret;
    }

    /**
     * 错误信息记录
     *
     * @param $method
     * @param $params
     */
    private function errorInfo($method, $params)
    {
        $class = get_called_class();
        list(, $method) = explode('::', $method);
        $methodInfo = $class . '->' . $method . '(' . var_export($params, 1) . ')';
        echo $methodInfo;
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

            if (!isset($this->localCache[$key])) {
                $datas = $this->redis->hGetAll($key);

                if (!$datas) {
                    $datas = parent::getByPk($uid);
                    if ($datas) {   //fix local & redis cache
                        $this->redis->hMset($key, $datas);
                        $this->localCache[$key] = $datas;
                    }
                } else {   //fix local cache
                    $this->localCache[$key] = $datas;
                }

                return isset($datas[$sk]) ? $datas[$sk] : null;
            } else {
                return isset($this->localCache[$key][$sk]) ? $this->localCache[$key][$sk] : null;
            }
        } else {
            $this->init($infos);

            $key = $this->getPK();
            if (!isset($this->localCache[$key])) {
                $datas = $this->redis->hGetAll($key);

                if (!$datas) {
                    $datas = parent::getByPk($infos);
                    if ($datas) {    //fix local & redis cache
                        $this->redis->hMset($key, $datas);
                        $this->localCache[$key] = $datas;
                    }
                } else {    //fix local cache
                    $this->localCache[$key] = $datas;
                }
            } else {
                $datas = $this->localCache[$key];
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