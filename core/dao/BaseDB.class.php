<?php

/**
 * 数据库相关 DAO
 * @author  Jeff Liu
 *
 * @version 0.1
 *
 * Class BaseDBDAO
 */
class BaseDBDAO extends SmvcObject
{

    protected static $writeKey = 'db.master';
    protected static $readKey = 'db.slave';

    protected $tableName = '';
    protected $realTableName = '';

    protected $defaultValue = array();

    /**
     * 读操作对应的storage
     * @var medoo
     */
    protected $readerStorage = null;

    /**
     * 写操作对应的storage
     * @var medoo
     */
    protected $writerStorage = null;

    /**
     * 最后一次使用的storage
     * @var String
     */
    protected $latestStorageType = null;
    const WRITE_STORAGE = 1;
    const READ_STORAGE = 2;
    protected $storageType = null;

    protected $pk = null;
    protected $sk = null;

    /**
     * 事务指令数
     * @var int
     */
    protected $transTimes = 0;

    /**
     * 数据库连接ID 支持多个连接
     * @var array
     */
    protected $storageList = array();

    /**
     * 当前数据库连接
     * @var mixed
     */
    protected $currentStorage = null;

    /**
     * 是否已经连接数据库
     * @var bool
     */
    protected $connected = false;

    public function __construct()
    {
        parent::__construct();

        if (empty(self::$writeKey) || !is_scalar(self::$writeKey)) {
            self::$writeKey = 'db.master';
        }

        if (empty(self::$readKey) || !is_scalar(self::$readKey)) {
            self::$readKey = 'db.slave';
        }

        //        $this->initDb(); //只在需要的时候 才初始化
    }


    /**
     * 是否为1：n的关系
     *
     * @return bool
     */
    public function isMulit()
    {
        return null !== $this->sk;
    }


    /**
     * 根据给定的主键值或由主键值组成的数组，删除相应的记录。
     *
     * @param mixed $condition 主键值或主键值数组。
     *
     * @return boolean
     */
    public function deleteByPk($condition)
    {
        //若当前表有辅助主键，且传进来的参数是数组，数组长度大于1

        $where = sprintf('%s=%s', $this->pk, $condition[$this->pk]);
        if ($this->isMulit() && isset($condition[$this->sk])) {
            $where .= sprintf('AND %s="%s"', $this->sk, $condition[$this->sk]);
        }
        $ret = $this->delete($where);

        return $ret;
    }


    /**
     * 通过主键获取数据。
     *
     * @param mixed $pk 主键信息。
     *
     * @return array
     */
    public function getByPk($pk)
    {
        $where      = sprintf('%s=%s', $this->pk, $pk);
        $originData = $this->getAll('*', $where);
        if ($this->isMulit()) {
            $originData = $this->format($originData);
        }

        return $originData;
    }

    /**
     * 格式化数据
     *
     * @param array $datas
     *
     * @return mixed
     */
    private function format($datas)
    {
        $ret = array();
        foreach ($datas as $data) {
            $sk       = $data[$this->sk];
            $ret[$sk] = $data;
        }

        return $ret;
    }


    /**
     * todo 数据库 原理上来说可以通用。可以提到一个公用的地方 统一处理
     *
     * @return medoo
     */
    public function initDb()
    {
        $this->initWriteStorage();
        $this->initReadStorage();
    }


    /**
     * 初始化写
     * @author Jeff Liu
     * @return medoo
     */
    public function initWriteStorage()
    {
        if (empty($this->writerStorage)) {
            $masterIndex         = $this->getDbMasterIndex();
            $dbMasterConfig      = C(self::$writeKey);
            $masterConifg        = $dbMasterConfig[$masterIndex];
            $this->writerStorage = $this->getDbInstance($masterConifg);
            $this->setTableName($masterConifg);
        }

        return $this->writerStorage;
    }

    /**
     * @var Medoo[]
     */
    private $dbInstance = array();

    /**
     * 获得 db实例
     * @author Jeff Liu<jeff.liu.guo@gmail.com>
     *
     * @param $dbConfig
     *
     * @return Medoo
     */
    public function getDbInstance($dbConfig)
    {
        $configSha = md5(json_encode($dbConfig));
        if (!isset($this->dbInstance[$configSha])) {
            $this->dbInstance[$configSha] = new medoo(array(
                    'database_type' => $dbConfig['DB_TYPE'],
                    'database_name' => $dbConfig['DB_NAME'],
                    'server'        => $dbConfig['DB_HOST'],
                    'username'      => $dbConfig['DB_USER'],
                    'password'      => $dbConfig['DB_PASS'],
            ));
        }

        return $this->dbInstance[$configSha];
    }

    /**
     * @return int|mixed
     */
    public function getDbMasterIndex()
    {
        $dbMasterConfig = C(self::$writeKey);
        $userSplit      = LocalCache::getData('userSplit');
        if ($userSplit && C('useUserSplit', false)) {
            $masterIndex = isset($userSplit['db']) ? $userSplit['db'] : 0;
        } else {
            $masterIndex = array_rand($dbMasterConfig);
        }

        return $masterIndex;
    }

    /**
     * @return medoo
     */
    public function initReadStorage()
    {
        if (empty($this->readerStorage)) {
            if (C('open_rw')) {
                $masterIndex         = $this->getDbMasterIndex();
                $dbSlaveConfig       = C(self::$readKey);
                $slaveIndex          = array_rand($dbSlaveConfig[$masterIndex]);
                $slaveConifg         = $dbSlaveConfig[$masterIndex][$slaveIndex];
                $this->readerStorage = $this->getDbInstance($slaveConifg);
            } else {
                $this->readerStorage = $this->initWriteStorage();
            }
        }

        return $this->readerStorage;
    }

    /**
     * @param $dbConfig
     */
    private function setTableName($dbConfig)
    {
        if (empty($this->realTableName)) {
            $dbPrefix        = isset($dbConfig['DB_PREFIX']) ? $dbConfig['DB_PREFIX'] : '';
            $this->tableName = $dbPrefix . $this->tableName;
        } else {
            $this->tableName = $this->realTableName;
        }
    }

    /**
     * 设置最后一次 storage type
     *
     * @param $type
     */
    public function setLatestStorageType($type)
    {
        if ($type === self::WRITE_STORAGE || $type === self::READ_STORAGE) {
            $this->latestStorageType = $type;
        }
    }

    /**
     * 获得最后一次的storage type
     *
     * @return String
     */
    public function getLatestStorageType()
    {
        return $this->latestStorageType;
    }

    /**
     *
     * 创建一条新的记录
     * @author Jeff Liu
     * powered by jeff 2011-5-31
     * @see    http://medoo.in/api/insert
     *
     * @param array $data
     *
     * @return int the last insert id
     */
    public function add($data)
    {
        $this->setLatestStorageType(self::WRITE_STORAGE);

        return $this->getStorage()->insert($this->tableName, $data);
    }

    /**
     * @param $type
     *
     * @return medoo
     */
    public function getStorage($type = null)
    {
        if (is_null($type)) {
            $type = $this->latestStorageType ? $this->latestStorageType : self::WRITE_STORAGE;
        }

        if ($type == self::WRITE_STORAGE) {
            return $this->getWriteStorage();
        } else {
            return $this->getReadStorage();
        }
    }

    /**
     * @return medoo
     */
    public function getWriteStorage()
    {
        if (empty($this->writerStorage)) {
            $this->initWriteStorage();
        }

        return $this->writerStorage;
    }

    /**
     * @return medoo
     */
    public function getReadStorage()
    {
        if (empty($this->readerStorage)) {
            $this->initReadStorage();
        }

        return $this->readerStorage;
    }

    /**
     * 添加数据之前的动作
     * @author Jeff Liu
     */
    protected function preAdd()
    {

    }

    /**
     * 添加数据之后的动作
     * @author Jeff Liu
     */
    protected function postAdd()
    {

    }

    /**
     * @see    http://medoo.in/api/insert
     * @author Jeff Liu
     *
     * @param $data
     *
     * @return array
     */
    public function multiAdd($data)
    {
        $this->setLatestStorageType(self::WRITE_STORAGE);
        array_unshift($data, $this->tableName);

        return call_user_func_array(array($this->getStorage(), 'insert'), $data);
    }


    /**
     * 添加数据之前的动作
     * @author Jeff Liu
     */
    protected function preMultiAdd()
    {
    }

    /**
     * 添加数据之后的动作
     * @author Jeff Liu
     */
    protected function postMultiAdd()
    {
    }

    /**
     * 更新
     * powered by jeff 2011-5-31
     *
     * @param array        $data  The data that will be modified.
     * @param array|string $where The WHERE clause to filter records.
     *
     * @see http://medoo.in/api/update
     *
     * @return int  The number of rows affected.
     */
    public function update($data, $where = '')
    {
        $this->setLatestStorageType(self::WRITE_STORAGE);

        return $this->getStorage()->update($this->tableName, $data, $where);
    }

    /**
     * 更新数据之前的动作
     * @author Jeff Liu
     */
    protected function preUpdate()
    {

    }

    /**
     * 更新数据之后的动作
     * @author Jeff Liu
     */
    protected function postUpdate()
    {

    }

    /**
     * 获得数据
     * @author Jeff Liu
     * powered by jeff 2011-5-31
     *
     * @param array|string $columns
     * @param              array
     *
     * @return array Return the data of the column.
     */
    public function getOne($columns, $where = array())
    {
        $this->setLatestStorageType(self::READ_STORAGE);

        return $this->getStorage()->get($this->tableName, $columns, $where);
    }

    /**
     * 获得数据之前的动作
     * @author Jeff Liu
     */
    protected function preGetOne()
    {

    }

    /**
     * 获得数据之后的动作
     * @author Jeff Liu
     */
    protected function postGetOne()
    {

    }

    /**
     * @param   string|array $columns
     * @param array          $where
     *
     * @return array
     */
    public function getAll($columns = '*', $where = array())
    {
        $this->setLatestStorageType(self::READ_STORAGE);

        return $this->getStorage()->select($this->tableName, $columns, $where);
    }

    /**
     * 获得数据之前的动作
     * @author Jeff Liu
     */
    protected function preGetAll()
    {
    }

    /**
     * 获得数据之后的动作
     * @author Jeff Liu
     */
    protected function postGetAll()
    {
    }

    /**
     * @param       $join
     * @param       $columns
     * @param array $where
     *
     * @return array|bool
     */
    public function getAllWithJoin($join, $columns, $where = array())
    {
        $this->setLatestStorageType(self::READ_STORAGE);

        return $this->getStorage()->select($this->tableName, $join, $columns, $where);
    }


    /**
     * 删除数据
     * @author Jeff Liu
     * @see    http://medoo.in/api/delete
     * powered by jeff 2011-5-31
     *
     * @param array $where
     *
     * @return int The number of rows affected.
     */
    public function delete($where = array())
    {
        $this->setLatestStorageType(self::WRITE_STORAGE);

        return $this->getStorage()->delete($this->tableName, $where);
    }

    /**
     * 删除数据之前的动作
     * @author Jeff Liu
     */
    protected function preDelete()
    {
    }

    /**
     * 删除数据之后的动作
     * @author Jeff Liu
     */
    protected function postDelete()
    {
    }

    /**
     * @param array $where
     *
     * @return int The number of rows.
     */
    public function getCount($where)
    {
        $this->setLatestStorageType(self::READ_STORAGE);

        return $this->getStorage()->count($this->tableName, $where);
    }

    /**
     * @param string $column The target column will be calculated.
     * @param array  $where
     *
     * @return int  The maximum number of the column.
     */
    public function getMax($column, $where)
    {
        $this->setLatestStorageType(self::READ_STORAGE);

        return $this->getStorage()->max($this->tableName, $column, $where);
    }

    /**
     * @param string $column The target column will be calculated.
     * @param array  $where
     *
     * @return int  The minimum number of the column.
     */
    public function getMin($column, $where)
    {
        $this->setLatestStorageType(self::READ_STORAGE);

        return $this->getStorage()->min($this->tableName, $column, $where);
    }

    /**
     * @param string $column The target column will be calculated.
     * @param array  $where
     *
     * @return int  The average number of the column.
     */
    public function getAvg($column, $where)
    {
        $this->setLatestStorageType(self::READ_STORAGE);

        return $this->getStorage()->avg($this->tableName, $column, $where);
    }

    /**
     * @param string $column The target column will be calculated.
     * @param array  $where
     *
     * @return int  The total number of the column.
     */
    public function getSum($column, $where)
    {
        $this->setLatestStorageType(self::READ_STORAGE);

        return $this->getStorage()->sum($this->tableName, $column, $where);
    }

    /**
     * @param array $where
     *
     * @return int  The total number of the column.
     */
    public function has($where)
    {
        $this->setLatestStorageType(self::READ_STORAGE);

        return $this->getStorage()->has($this->tableName, $where);
    }

    /**
     * @param string $join Table relativity for table joining.
     * @param array  $where
     *
     * @return int  The total number of the column.
     */
    public function hasWithJoin($join, $where)
    {
        $this->setLatestStorageType(self::READ_STORAGE);

        return $this->getStorage()->has($this->tableName, $join, $where);
    }

    /**
     * @param int $query 字符串
     *
     * @param int $type
     *
     * @return object The PDOStatement object.
     */
    public function query($query, $type = null)
    {
        if ($this->transTimes > 0) { //事务
            $type = self::WRITE_STORAGE;
        } else if (is_null($type)) {
            if ('SELECT' === strtoupper(substr($query, 0, 6))) {
                $type = self::READ_STORAGE;
            } else {
                $type = self::WRITE_STORAGE;
            }
        }

        return $this->getStorage($type)->query($query);
    }

    /**
     * @param $string
     *
     * @return string
     */
    public function quote($string)
    {
        return $this->getStorage(self::READ_STORAGE)->quote($string);
    }

    /**
     * @return array an array of error information about the last operation performed
     */
    public function error()
    {
        return $this->getStorage()->error();
    }

    /**
     * @return mixed Return the last query performed.
     */
    public function lastQuery()
    {
        return $this->getStorage()->last_query();
    }

    /**
     * @return array
     */
    public function getDatabaseInfo()
    {
        return $this->getStorage()->info();
    }

    /**
     * 初始化数据库连接
     * @access protected
     *
     * @param boolean $master 主服务器
     *
     * @return void
     */
    protected function initConnect($master = true)
    {
        if (1 == C('DB_DEPLOY_TYPE')) { // 采用分布式数据库
            $this->currentStorage = $this->multiConnect($master);
        } else // 默认单数据库
            if (!$this->connected) {
                $this->currentStorage = $this->connect($master);
            }
    }

    /**
     * 连接分布式服务器
     * @access protected
     *
     * @param boolean $master 主服务器
     *
     * @return mixed
     */
    protected function multiConnect($master = false)
    {
        return $this->connect($master);
    }

    /**
     * 连接数据库方法
     * @access public
     *
     * @param $master
     *
     * @return mixed
     */
    public function connect($master)
    {
        if ($master) {
            $this->setLatestStorageType(self::WRITE_STORAGE);

        } else {
            $this->setLatestStorageType(self::READ_STORAGE);
        }
        $this->currentStorage = $this->getStorage();
        $this->connected      = true;

        return $this->currentStorage;
    }

    /**
     * 启动事务
     * @access public
     * @return mixed
     */
    public function startTrans()
    {
        $this->initConnect(true);
        if (!$this->currentStorage) {
            return false;
        }
        //数据rollback 支持
        if ($this->transTimes == 0) {
            $this->currentStorage->query('START TRANSACTION');
        }
        $this->transTimes++;

        return true;
    }

    /**
     * 用于非自动提交状态下面的查询提交
     * @access public
     * @return boolean
     */
    public function commit()
    {
        $this->initConnect(true);
        if ($this->transTimes > 0) {
            $result           = $this->currentStorage->query('COMMIT');
            $this->transTimes = 0;
            if (!$result) {
                $this->error();

                return false;
            }
        }

        return true;
    }

    /**
     * 事务回滚
     * @access public
     * @return boolean
     */
    public function rollback()
    {
        $this->initConnect(true);
        if ($this->transTimes > 0) {
            $result           = $this->currentStorage->query('ROLLBACK');
            $this->transTimes = 0;
            if (!$result) {
                $this->error();

                return false;
            }
        }

        return true;
    }

}
