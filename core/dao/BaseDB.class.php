<?php

use Medoo\Medoo;
/**
 * 数据库相关 DAO
 * @author  Jeff.Liu<jeff.liu.guo@gmail.com>
 *
 * @version 0.5
 *
 * Class BaseDBDAO
 */
class BaseDBDAO extends SmvcObject
{

    const SELECT_TYPE_ALL = 0; // 0：获得所有满足条件的记录
    const SELECT_TYPE_ONE = 1; // 1：获得一条满足条件的记录
    const SELECT_TYPE_FIELD = 2; // 2:获得一条满足条件的记录中的某些字段

    protected static $writeKey = 'db.master';
    protected static $readKey = 'db.slave';
    protected $name = '';// 模型名称
    protected $dbName = '';// 数据库名称
    protected $connection = '';//数据库配置
    protected $tableName = '';// 数据表名（不包含表前缀）
    protected $realTableName = '';// 实际数据表名（包含表前缀）
    protected $defaultValue = [];
    protected $tablePrefix = '';// 数据表前缀

    /**
     * 读操作对应的storage
     * @var Medoo
     **/
    protected $readerStorage = null;

    /**
     * 写操作对应的storage
     * @var Medoo
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

    protected $fields = [];// 字段信息

    /**
     * 事务指令数
     * @var int
     */
    protected $transTimes = 0;

    /**
     * 数据库连接ID 支持多个连接
     * @var array
     */
    protected $storageList = [];

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

    protected $data = [];

    protected $methods = [
            'table',
            'order',
            'alias',
            'having',
            'group',
            'lock',
            'distinct',
            'auto',
            'filter',
            'validate',
            'result',
            'bind',
            'token'
    ];
    protected $options = [];

    /**
     *
     *
     * @param string $name
     * @param string $tablePrefix
     * @param string $connection
     */
    public function __construct($name = '', $tablePrefix = '', $connection = '')
    {
        parent::__construct();

        if (empty(self::$writeKey) || !is_scalar(self::$writeKey)) {
            self::$writeKey = 'db.master';
        }

        if (empty(self::$readKey) || !is_scalar(self::$readKey)) {
            self::$readKey = 'db.slave';
        }

        // 获取模型名称
        if (!empty($name)) {
            if (strpos($name, '.')) { // 支持 数据库名.模型名的 定义
                list($this->dbName, $this->name) = explode('.', $name);
            } else {
                $this->name = $name;
            }
        } elseif (empty($this->name)) {
            $this->name = $this->getModelName();
        }
        // 设置表前缀
        if (is_null($tablePrefix)) {// 前缀为Null表示没有前缀
            $this->tablePrefix = '';
        } elseif ('' != $tablePrefix) {
            $this->tablePrefix = $tablePrefix;
        } else {
            $this->tablePrefix = $this->tablePrefix ? $this->tablePrefix : C('DB_PREFIX');
        }

        $this->connect(false); //先初始化一个读取db实例  todo 真的有这个必要吗？？
    }

    /**
     * 得到当前的数据对象名称
     * @access public
     * @return string
     */
    public function getModelName()
    {
        if (empty($this->name)) {
            $this->name = substr(get_class($this), 0, -5);
        }
        return $this->name;
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
        if ($this->pk) {
            if (is_scalar($condition)) {
                $where = sprintf('%s=%s', $this->pk, $condition);
            } else {
                $where = sprintf('%s=%s', $this->pk, $condition[$this->pk]);
            }

            if ($this->isMulit() && isset($condition[$this->sk])) {
                $where .= sprintf('AND %s="%s"', $this->sk, $condition[$this->sk]);
            }
            $ret = $this->delete($where);

            return $ret;
        }
        return null;
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
        $ret = [];
        foreach ($datas as $data) {
            $sk       = $data[$this->sk];
            $ret[$sk] = $data;
        }

        return $ret;
    }


    /**
     * todo 数据库 原理上来说可以通用。可以提到一个公用的地方 统一处理
     */
    public function initDb()
    {
        $this->initWriteStorage();
        $this->initReadStorage();
    }


    /**
     * 初始化写
     * @author Jeff Liu
     * @return Medoo
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
    private $dbInstance = [];

    /**
     * 获得 db实例
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     *
     * @param $dbConfig
     *
     * @return Medoo
     */
    public function getDbInstance($dbConfig)
    {
        $configSha = md5(json_encode($dbConfig));
        if (!isset($this->dbInstance[$configSha])) {
            $this->dbInstance[$configSha] = new Medoo(
                    [
                            'database_type' => $dbConfig['DB_TYPE'],
                            'database_name' => $dbConfig['DB_NAME'],
                            'server'        => $dbConfig['DB_HOST'],
                            'username'      => $dbConfig['DB_USER'],
                            'password'      => $dbConfig['DB_PASS'],
                    ]
            );
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
     * @return Medoo
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
                $this->setTableName($slaveConifg);
            } else {
                $this->readerStorage = $this->initWriteStorage();
            }
        }

        return $this->readerStorage;
    }

    /**
     * @param $dbConfig
     *
     * @return $this
     */
    private function setTableName($dbConfig)
    {
        if (empty($this->realTableName)) {
            $dbPrefix            = isset($dbConfig['DB_PREFIX']) ? $dbConfig['DB_PREFIX'] : '';
            $this->realTableName = $dbPrefix . $this->tableName;
        }
        return $this;
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
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
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
        $tableName = $this->getTableName();
        return $this->getStorage()->insert($tableName, $data);
    }

    /**
     * @param $type
     *
     * @return Medoo
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
     * @return Medoo
     */
    public function getWriteStorage()
    {
        if (empty($this->writerStorage)) {
            $this->initWriteStorage();
        }

        return $this->writerStorage;
    }

    /**
     * @return Medoo
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
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     */
    protected function preAdd()
    {

    }

    /**
     * 添加数据之后的动作
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     */
    protected function postAdd()
    {

    }

    /**
     * @see    http://medoo.in/api/insert
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     *
     * @param $data
     *
     * @return array
     */
    public function multiAdd($data)
    {
        $this->setLatestStorageType(self::WRITE_STORAGE);
        array_unshift($data, $this->getTableName());

        return call_user_func_array([$this->getStorage(), 'insert'], $data);
    }


    /**
     * 添加数据之前的动作
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     */
    protected function preMultiAdd()
    {
    }

    /**
     * 添加数据之后的动作
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     */
    protected function postMultiAdd()
    {
    }

    /**
     * 更新
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     *
     * @param array        $data  The data that will be modified.
     * @param array|string $where The WHERE clause to filter records.
     *
     * @see    http://medoo.in/api/update
     *
     * @return int  The number of rows affected.
     */
    public function update($data, $where = '')
    {
        $this->setLatestStorageType(self::WRITE_STORAGE);
        $where     = $this->_parseOptions($where);
        $tableName = isset($where['table']) ? $where['table'] : $this->getTableName();
        return $this->getStorage()->update($tableName, $data, $where);
    }

    /**
     * 更新数据之前的动作
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     */
    protected function preUpdate()
    {

    }

    /**
     * 更新数据之后的动作
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     */
    protected function postUpdate()
    {

    }

    /**
     * 获得数据
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     *
     * @param              array
     * @param array|string $columns
     *
     * @return array Return the data of the column.
     */
    public function getOne($where = [], $columns = '*')
    {
        $this->setLatestStorageType(self::READ_STORAGE);
        $where     = $this->_parseOptions($where);
        $tableName = isset($where['table']) ? $where['table'] : $this->getTableName();
        unset($where['table']);
        unset($where['model']);
        return $this->getStorage()->get($tableName, $columns, $where);
    }

    /**
     * 获得数据之前的动作
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     */
    protected function preGetOne()
    {

    }

    /**
     * 获得数据之后的动作
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
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
    public function getAll($columns = '*', $where = [])
    {
        $this->setLatestStorageType(self::READ_STORAGE);
        return $this->select($columns, null, $where);
    }

    /**
     * 获得数据之前的动作
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     */
    protected function preGetAll()
    {
    }

    /**
     * 获得数据之后的动作
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
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
    public function getAllWithJoin($join, $columns, $where = [])
    {
        $this->setLatestStorageType(self::READ_STORAGE);
        return $this->select($columns, $join, $where);
    }

    protected function select($columns, $join, $where)
    {
        $where     = $this->_parseOptions($where);
        $tableName = isset($where['table']) ? $where['table'] : $this->getTableName();
        unset($where['table']);
        unset($where['model']);
        if (is_null($join)) {
            return $this->getStorage()->select($tableName, $columns, $where);
        } else {
            return $this->getStorage()->select($tableName, $join, $columns, $where);
        }
    }


    /**
     * 删除数据
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     * @see    http://medoo.in/api/delete
     *
     * @param array $where
     *
     * @return int The number of rows affected.
     */
    public function delete($where = [])
    {
        $this->setLatestStorageType(self::WRITE_STORAGE);

        return $this->getStorage()->delete($this->getTableName(), $where);
    }

    /**
     * 删除数据之前的动作
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     */
    protected function preDelete()
    {
    }

    /**
     * 删除数据之后的动作
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
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

        return $this->getStorage()->count($this->getTableName(), $where);
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

        return $this->getStorage()->max($this->getTableName(), $column, $where);
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

        return $this->getStorage()->min($this->getTableName(), $column, $where);
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

        return $this->getStorage()->avg($this->getTableName(), $column, $where);
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

        return $this->getStorage()->sum($this->getTableName(), $column, $where);
    }

    /**
     * @param array $where
     *
     * @return int  The total number of the column.
     */
    public function has($where)
    {
        $this->setLatestStorageType(self::READ_STORAGE);

        return $this->getStorage()->has($this->getTableName(), $where);
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

        return $this->getStorage()->has($this->getTableName(), $join, $where);
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
        $return = $this->getStorage($type)->query($query);
        return $return;
    }

    /**
     * 获得所有执行的sql （包含sql语句和执行该sql所用时间）
     * @return array
     */
    public function getSqlList()
    {
        return $this->getStorage()->getSqlList();
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

    public function execute($query, $type = 1)
    {
        return $this->exec($query, $type);
    }

    /**
     * @param     $query
     * @param int $type
     *
     * @return mixed
     */
    public function exec($query, $type = 1)
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
        return $this->getStorage($type)->exec($query);
    }

    /**
     * @author Jeff Liu<liuwy@imageco.com.cn>
     *
     * @param $data
     * @param $where
     *
     * @return bool
     */
    public function saveData($data, $where)
    {
        return $this->update($data, $where);
    }

    public function save($data, $where = [])
    {
        $this->update($data, $where);
    }

    public function getList($where, $field = '')
    {
        return $this->getData('', $where, self::SELECT_TYPE_ALL, $field);
    }


    public function getListWithLimit($where, $limit, $field = '')
    {
        return $this->getData('', $where, self::SELECT_TYPE_ALL, $field, '', $limit);
    }

    /**
     * @author Jeff.Liu<liuwy@imageco.com.cn>
     *
     * @param $tableName
     *
     * @return bool
     */
    public function needGetFields($tableName)
    {
        $tableNameTmp = strtoupper($tableName);
        if (in_array(
                $tableNameTmp,
                ['SELECT', 'INSERT', 'UPDATE', 'DELETE', 'SHOW', 'CREATE', 'CALL']
        )) {
            return false;
        }
        return true;
    }

    /**
     * 字段和表名处理添加`
     * @access protected
     *
     * @param string $key
     *
     * @return string
     */
    protected function parseKey(&$key)
    {
        $key = trim($key);
        if (!preg_match('/[,\'\"\*\(\)`.\s]/', $key)) {
            $key = '`' . $key . '`';
        }
        return $key;
    }

    /**
     * @param $field
     *
     * @return bool
     */
    protected function needChangeCase($field)
    {
        $pattern = '|^[A-Z_]+$|';
        preg_match_all($pattern, $field, $matches);
        if (isset($matches[0]) && $matches[0]) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 取得数据表的字段信息
     * @access public
     *
     * @param $tableName
     *
     * @return array
     */
    public function getFields($tableName)
    {
        $info = [];
        if (stripos($tableName, '__none__') === false) {
            $tableName = $this->parseKey($tableName);
            preg_match('|\w+|', $tableName, $tableNameList); //去掉表别名
            if (isset($tableNameList[0])) {
                $tableName = $tableNameList[0];
            }
            $result = $this->getStorage()->query('SHOW COLUMNS FROM ' . $tableName);
            if ($result) {
                foreach ($result as $key => $val) {
                    $needChangeCase = $this->needChangeCase($val['Field']);
                    if ($needChangeCase) {//首字母大写转成小写 因为mycat会将field转为大写，这里转成小写
                        $field = strtolower($val['Field']);
                    } else {
                        $field = $val['Field'];
                    }

                    $info[$field] = [
                            'name'    => $field,
                            'type'    => $val['Type'],
                            'notnull' => (bool)($val['Null'] === ''), // not null is empty, null is yes
                            'default' => $val['Default'],
                            'primary' => (strtolower($val['Key']) == 'pri'),
                            'autoinc' => (strtolower($val['Extra']) == 'auto_increment'),
                    ];
                }
            }
        }

        return $info;
    }


    /**
     * 获取数据表字段信息
     * @access public
     * @return array
     */
    public function getDbFields()
    {
        if (isset($this->options['table'])) {// 动态指定表名
            $table         = $this->options['table'];
            $needGetFields = $this->needGetFields($table);
            $fields        = [];
            if ($needGetFields) {
                $fields = $this->getFields($table);
            }
            return $fields ? array_keys($fields) : [];
        }
        if ($this->fields) {
            $fields = $this->fields;
            unset($fields['_autoinc'], $fields['_pk'], $fields['_type'], $fields['_version']);
            return $fields;
        }
        return [];
    }


    /**
     * 指定查询字段 支持字段排除
     * @access public
     *
     * @param mixed   $field
     * @param boolean $except 是否排除
     *
     * @return BaseDBDAO
     */
    public function field($field, $except = false)
    {
        if (true === $field) {// 获取全部字段
            $fields = $this->getDbFields();
            $field  = $fields ? $fields : '*';
        } elseif ($except) {// 字段排除
            if (is_string($field)) {
                $field = explode(',', $field);
            }
            $fields = $this->getDbFields();
            $field  = $fields ? array_diff($fields, $field) : $field;
        }
        $this->options['field'] = $field;
        return $this;
    }

    protected function _parseTable($options)
    {
        if (isset($options['table'])) { // 自动获取表名
            $table = $options['table'];
        } else if (isset($this->options['table'])) {
            $table = $this->options['table'];
        } else {
            $table = $this->getTableName();
        }

        if (!empty($options['alias'])) {
            $table .= ' ' . $options['alias'];
        }

        return $table;
    }

    /**
     *
     * todo 这个还需要进行处理
     * 通用获得数据的方法
     *
     * @author Jeff Liu<liuwy@imageco.com.cn>
     *
     * @param string $table
     * @param array  $where
     * @param int    $selectType
     * @param string $field
     * @param string $order
     * @param string $limit
     *
     * @param bool   $lock
     *
     * @return mixed
     */
    public function getData(
            $table = '',
            $where = [],
            $selectType = self::SELECT_TYPE_ALL,
            $field = '',
            $order = '',
            $limit = '',
            $lock = false
    ) {
        if (empty($table)) {
            $table = $this->_parseTable($where);
        }
        $this->table($table);
        if ($field) {
            $this->field($field);
        }
        if ($order) {
            $this->order($order);
        }
        if ($limit) {
            $this->limit($limit);
        }
        if ($lock) {
            $this->lock();
        }
        $where = $this->_parseOptions($where);

        if ($selectType === self::SELECT_TYPE_ALL) {
            return $this->getAll($field, $where);
        } elseif ($selectType === self::SELECT_TYPE_ONE) {
            return $this->getOne($where, $field);
        } else {
            return $this->getField($field, $where);
        }
    }



    public function token($token)
    {
        return $this->__call(__FUNCTION__, [$token,]);
    }

    public function bind($bind)
    {
        return $this->__call(__FUNCTION__, [$bind,]);
    }

    public function result($result)
    {
        return $this->__call(__FUNCTION__, [$result,]);
    }

    public function validate($validate)
    {
        return $this->__call(__FUNCTION__, [$validate,]);
    }

    /**
     *
     * @param $filter
     *
     * @return $this
     */
    public function filter($filter)
    {
        return $this->__call(__FUNCTION__, [$filter,]);
    }

    /**
     *
     * @param $auto
     *
     * @return $this
     */
    public function auto($auto)
    {
        return $this->__call(__FUNCTION__, [$auto,]);
    }

    /**
     *
     * @param $distinct
     *
     * @return $this
     */
    public function distinct($distinct)
    {
        return $this->__call(__FUNCTION__, [$distinct,]);
    }

    /**
     *
     * @param $table
     *
     * @return $this
     */
    public function table($table)
    {
        return $this->__call(__FUNCTION__, [$table,]);
    }

    /**
     *
     * @param $orderBy
     *
     * @return $this
     */
    public function order($orderBy)
    {
        return $this->__call(__FUNCTION__, [$orderBy,]);
    }

    public function limit($limit)
    {
        return $this->__call(__FUNCTION__, [$limit,]);
    }

    /**
     *
     * @param $alias
     *
     * @return $this
     */
    public function alias($alias)
    {
        return $this->__call(__FUNCTION__, [$alias,]);
    }

    /**
     *
     * @param $having
     *
     * @return $this
     */
    public function having($having)
    {
        return $this->__call(__FUNCTION__, [$having,]);
    }

    /**
     *
     * @param $lock
     *
     * @return $this
     */
    public function lock($lock = true)
    {
        return $this->__call(__FUNCTION__, [$lock,]);
    }

    /**
     *
     * @param $group
     *
     * @return $this
     */
    public function group($group)
    {
        return $this->__call(__FUNCTION__, [$group,]);
    }

    /**
     * @return mixed
     */
    public function count()
    {
        return $this->__call(__FUNCTION__, []);
    }


    /**
     * 利用__call方法实现一些特殊的Model方法
     *
     * @access public
     *
     * @param string $method 方法名称
     * @param array  $args   调用参数
     *
     * @return mixed $this
     */
    public function __call($method, $args)
    {
        if (in_array(strtolower($method), $this->methods, true)) {
            // 连贯操作的实现
            $this->options[strtolower($method)] = $args[0];
            return $this;
        } elseif (in_array(strtolower($method), ['count', 'sum', 'min', 'max', 'avg'], true)) {
            // 统计查询的实现
            $field = isset($args[0]) ? $args[0] : '*';
            return $this->getField(strtoupper($method) . '(' . $field . ') AS tp_' . $method);
        } elseif (strtolower(substr($method, 0, 5)) == 'getby') {
            // 根据某个字段获取记录
            $field         = parse_name(substr($method, 5));
            $where[$field] = $args[0];
            return $this->where($where)->find();
        } elseif (strtolower(substr($method, 0, 10)) == 'getfieldby') {
            // 根据某个字段获取记录的某个值
            $name         = parse_name(substr($method, 10));
            $where[$name] = $args[0];
            return $this->where($where)->getField($args[1]);
        } else {
            throw_exception(__CLASS__ . ':' . $method . L('_METHOD_NOT_EXIST_'));
            return $this;
        }
    }


    /**
     * 数据类型检测
     * @access protected
     *
     * @param mixed  $data 数据
     * @param string $key  字段名
     *
     * @return void
     */
    protected function _parseType(&$data, $key)
    {
        if (empty($this->options['bind'][':' . $key])) {
            $fieldType = strtolower($this->fields['_type'][$key]);
            if (false !== strpos($fieldType, 'enum')) {
                // 支持ENUM类型优先检测
            } elseif (false === strpos($fieldType, 'bigint') && false !== strpos($fieldType, 'int')) {
                $data[$key] = intval($data[$key]);
            } elseif (false !== strpos($fieldType, 'float') || false !== strpos($fieldType, 'double')) {
                $data[$key] = floatval($data[$key]);
            } elseif (false !== strpos($fieldType, 'bool')) {
                $data[$key] = (bool)$data[$key];
            }
        }
    }


    /**
     * 得到完整的数据表名
     * @access public
     * @return string
     */
    public function getTableName()
    {
        if (empty($this->realTableName)) {
            $tableName = !empty($this->tablePrefix) ? $this->tablePrefix : '';
            if (!empty($this->tableName)) {
                $tableName .= $this->tableName;
            } else {
                $tableName .= parse_name($this->name);
            }
            $this->realTableName = strtolower($tableName);
        }
        return (!empty($this->dbName) ? $this->dbName . '.' : '') . $this->realTableName;
    }

    /**
     * 分析表达式
     * @access protected
     *
     * @param array $options 表达式参数
     *
     * @return array
     */
    protected function _parseOptions($options = [])
    {
        if (is_array($options)) {
            $options = array_merge($this->options, $options);
        }


        if (!isset($options['table'])) {
            // 自动获取表名
            $options['table'] = $this->getTableName();
            $fields           = $this->fields;
        } else {
            // 指定数据表 则重新获取字段列表 但不支持类型检测
            $fields = $this->getDbFields();
        }

        // 查询过后清空sql表达式组装 避免影响下次查询 调换位置是因为 调用table之后不能正确获取对应的数据
        $this->options = array();

        if (!empty($options['alias'])) {
            $options['table'] .= ' ' . $options['alias'];
        }
        // 记录操作的模型名称
        $options['model'] = $this->name;

        // 字段类型验证
        if (isset($options['where']) && is_array($options['where']) && !empty($fields) && !isset($options['join'])) {
            // 对数组查询条件进行字段类型检查
            foreach ($options['where'] as $key => $val) {
                $key = trim($key);
                if (in_array($key, $fields, true)) {
                    if (is_scalar($val)) {
                        $this->_parseType($options['where'], $key);
                    } elseif (is_array($val) && isset($_REQUEST[$key]) && is_array($_REQUEST[$key])) {
                        $options['where'][$key] = (string)$val;
                    }
                } elseif (!is_numeric($key) && '_' != substr($key, 0, 1) && false === strpos(
                                $key,
                                '.'
                        ) && false === strpos($key, '(') && false === strpos($key, '|') && false === strpos($key, '&')
                ) {
                    unset($options['where'][$key]);
                }
            }
        }

        // 表达式过滤
        $this->_options_filter($options);
        return $options;
    }

    // 表达式过滤回调方法
    protected function _options_filter(&$options)
    {
    }

    /**
     * 获取一条记录的某个字段值
     * @access   public
     *
     * @param string $field 字段名
     * @param null   $sepa
     *
     * @return mixed
     */
    public function getField($field, $sepa = null)
    {
        $options['field'] = $field;
        $options          = $this->_parseOptions($options);
        $field            = trim($field);
        if (strpos($field, ',')) { // 多字段
            if (!isset($options['limit'])) {
                $options['limit'] = is_numeric($sepa) ? $sepa : '';
            }
            $resultSet = $this->getList($options);
            if (!empty($resultSet)) {
                $_field = explode(',', $field);
                $field  = array_keys($resultSet[0]);
                $key    = array_shift($field);
                $key2   = array_shift($field);
                $cols   = [];
                $count  = count($_field);
                foreach ($resultSet as $result) {
                    $name = $result[$key];
                    if (2 == $count) {
                        $cols[$name] = $result[$key2];
                    } else {
                        $cols[$name] = is_string($sepa) ? implode($sepa, $result) : $result;
                    }
                }
                return $cols;
            }
        } else {   // 查找一条记录
            // 返回数据个数
            if (true !== $sepa) {// 当sepa指定为true的时候 返回所有数据
                $options['limit'] = is_numeric($sepa) ? $sepa : 1;
            }
            $result = $this->getOne($options);

            if (!empty($result)) {
                if (true !== $sepa && 1 == $options['limit']) {
                    return reset($result[0]);
                }
                $array = [];
                foreach ($result as $val) {
                    $array[] = $val[$field];
                }
                return $array;
            }
        }
        return null;
    }

    /**
     * 获取主键名称
     * @access public
     * @return string
     */
    public function getPk()
    {
        return isset($this->fields['_pk']) ? $this->fields['_pk'] : $this->pk;
    }

    /**
     * 查询数据
     * @access public
     *
     * @param mixed $options 表达式参数
     *
     * @return mixed
     */
    public function find($options = [])
    {
        if (is_numeric($options) || is_string($options)) {
            $where[$this->getPk()] = $options;
            $options               = [];
            $options['where']      = $where;
        }
        // 总是查找一条记录
        $options['limit'] = 1;
        // 分析表达式
        $options   = $this->_parseOptions($options);
        $resultSet = $this->getOne($options);
        if (false === $resultSet) {
            return false;
        }
        if (empty($resultSet)) {// 查询结果为空
            return null;
        }
        $this->data = $resultSet;
        return $this->data;
    }

    /**
     * 指定查询条件 支持安全过滤
     * @access public
     *
     * @param mixed $where 条件表达式
     * @param mixed $parse 预处理参数
     *
     * @return BaseDBDAO
     */
    public function where($where, $parse = null)
    {
        if (!is_null($parse) && is_string($where)) {
            if (!is_array($parse)) {
                $parse = func_get_args();
                array_shift($parse);
            }
            $parse = array_map([$this, 'quote'], $parse);
            $where = vsprintf($where, $parse);
        } elseif (is_object($where)) {
            $where = get_object_vars($where);
        }
        if (is_string($where) && '' != $where) {
            $map            = [];
            $map['_string'] = $where;
            $where          = $map;
        }
        if (isset($this->options['where'])) {
            $this->options['where'] = array_merge($this->options['where'], $where);
        } else {
            $this->options['where'] = $where;
        }

        return $this;
    }

}
