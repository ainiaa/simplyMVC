<?php

/**
 * Class BaseMemcacheDBDAO Memcache DB 数据存储类   (Memcache作为db的缓存)
 * @author  Jeff Liu
 * @version 0.1
 */
abstract class BaseMemcacheDBDAO extends BaseDBDAO
{
    /**
     * @var LocalCache
     */
    private $localCache;

    protected $uId;

    protected $storager;

    /**
     * @var CoreMemcache
     */
    protected $memcached;

    /**
     * 构造函数
     *
     * @param null $mode 抛弃
     * @param null $uid  抛弃
     */
    public function __construct($mode = null, $uid = null)
    {
        $this->localCache = LocalCache::getData($this->tableName);
        $this->uId        = LocalCache::getData('uId');
        parent::__construct();
    }

    /**
     * todo 这个需要处理
     * 初始化系统, 分表等
     *
     * @param number $uid
     *
     * @return mixed
     * @throws Exception
     */
    protected function init($uid)
    {
        $this->uId = $uid;

        if (empty($this->memcached)) {
            $memcacheConf = C('memcached', array());
            if (empty($memcacheConf)) {
                $memcacheConf = array(
                        'server'   => C('MC_HOST', 'localhost'),
                        'username' => C('MC_PORT', 11211),
                        'password' => C('MC_WEIGHT', 100),
                );
            }
            if (!class_exists('Memcached')) {
                throw new Exception(
                        'Memcached sessions are configured, but your PHP installation doesn\'t have the Memcached extension loaded.'
                );
            }

            $this->memcached = new Memcached();

            $this->memcached->addServers($memcacheConf);

            if ($this->memcached->getVersion() === false) {
                throw new Exception(
                        'Memcached sessions are configured, but there is no connection possible. Check your configuration.'
                );
            }
        }

        return $this->storager;
    }

    /**
     * 获得cache主键
     * @return string
     */
    private function getPK()
    {
        return $this->tableName . ':' . $this->uId;
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

                    $originData = $this->memcached->get($key);

                    $originData[$sk] = $datas;
                    $this->memcached->set($key, $originData);
                } else {
                    $this->localCache[$key] = $datas;
                    $this->memcached->set($key, $datas);
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
     * 更新数据(必须包含Pk字段)
     *
     * @param array $currentData
     * @param array $pinfo
     *
     * @return int
     */
    public function updateByPk($currentData, $pinfo = array())
    {
        $pk = $currentData[$this->pk];
        if (count($this->defaultValue) !== count($currentData)) {    //不完整完整字段, 合并更新

            $where = sprintf('%s=%s', $this->pk, $currentData[$this->pk]);

            if ($this->isMulit()) {
                $originData = $this->get($currentData[$this->pk], $currentData[$this->sk]);
                $where      = sprintf(' AND %s="%s"', $this->sk, $currentData[$this->sk]);
                unset($currentData[$this->pk], $currentData[$this->sk]);
            } else {
                $originData = $this->getByPk($pk);
                unset($currentData[$this->pk]);
            }

            $ret = parent::update($currentData, $where);

            if ($originData) {
                $currentData = array_merge($originData, $currentData);
                unset($originData);
            }
        } else {
            $ret = parent::update($currentData, $pinfo);
        }

        $key = $this->getPK();

        if ($this->isMulit()) {
            $sk = $currentData[$this->sk];

            $tmp                    = $this->localCache[$key];
            $tmp[$sk]               = $currentData;
            $this->localCache[$key] = $tmp;

            $originData = $this->memcached->get($key);

            $originData[$sk] = $currentData;
            $this->memcached->set($key, $originData);

            unset($sk, $tmp);
        } else {
            $this->localCache[$key] = $currentData;
            $this->memcached->set($key, $currentData);
        }

        unset($currentData, $key);

        return $ret;
    }


    /**
     * 按照主键获取数据
     *
     * @param mixed $pk
     * @param array $pinfo
     *
     * @return array|mixed
     */
    public function getByPk($pk, $pinfo = array())
    {
        if (is_array($pk)) {
            unset($pinfo);

            return $this->getBySk($pk);
        } else {
            $this->uId = $pk;
            $key       = $this->getPK();
            if (!isset($this->localCache[$key])) {
                $this->init($pk);
                $datas = $this->memcached->get($key);
                if (!$datas) {
                    $datas = parent::getByPk($pk);
                    if ($datas) {
                        $this->memcached->set($key, $datas);
                    }

                    return $datas;
                } else {
                    $this->localCache[$key] = $datas;

                    return $datas;
                }
            } else {
                return $this->localCache[$key];
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
    public function deleteBySk($ids, $pinfo = array())
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
    public function deleteByPk($condition, $pinfo = array())
    {
        if (is_array($condition)) {
            $pk = $condition[$this->pk];
            $this->init($pk);
            $sk  = $condition[$this->sk];
            $key = $this->getPK();
            $ret = parent::deleteByPk($condition);

            if ($ret > 0) {
                //清除缓存
                $originData = $this->memcached->get($key);
                unset($originData[$sk]);
                if ($originData) {
                    $this->memcached->set($key, $originData);
                } else {
                    $this->memcached->del($key);
                }

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
                $this->memcached->del($key);
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
        $infos = array(
                $this->pk => $pkid,
                $this->sk => $skid
        );

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
                $datas = $this->memcached->get($key);

                if (!$datas) {
                    $datas = parent::getByPk($uid);
                    if ($datas) {   //fix local & redis cache
                        $this->memcached->set($key, $datas);
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
                $datas = $this->memcached->get($key);

                if (!$datas) {
                    $datas = parent::getByPk($infos);
                    if ($datas) {    //fix local & redis cache
                        $this->memcached->set($key, $datas);
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