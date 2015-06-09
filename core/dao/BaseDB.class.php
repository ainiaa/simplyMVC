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

    protected $tableName = '';
    protected $realTableName = '';

    /**
     * @var medoo
     */
    protected $readerStorage = null;

    /**
     * @var medoo
     */
    protected $writerStorage = null;

    /**
     * 最后一次使用的
     * @var String
     */
    protected $latestStorageType = null;

    const LATEST_STORAGE_WRITER = 1;

    const LATEST_STORAGE_READER = 2;


    protected $storageType = null;

    public function __construct()
    {
        parent::__construct();

        if (empty($this->realTableName)) {
            $this->tableName = C('DB_PREFIX') . $this->tableName;
        }

        $this->initDb();
    }

    /**
     * todo 数据库 原理上来说可以通用。可以提到一个公用的地方 统一处理
     * @return medoo
     */
    public function initDb()
    {
        if (empty($this->writerStorage)) {
            $dbMasterConfig      = C('db.master');
            $masterIndex         = array_rand($dbMasterConfig);
            $masterConifg        = $dbMasterConfig[$masterIndex];
            $this->writerStorage = new medoo(
                    array(
                            'database_type' => $masterConifg['DB_TYPE'],
                            'database_name' => $masterConifg['DB_NAME'],
                            'server'        => $masterConifg['DB_HOST'],
                            'username'      => $masterConifg['DB_USER'],
                            'password'      => $masterConifg['DB_PASS'],
                    )
            );
        }

        if (empty($this->readerStorage)) {
            $dbSlaveConfig       = C('db.slave');
            $slaveIndex          = array_rand($dbSlaveConfig);
            $slaveConifg         = $dbSlaveConfig[$slaveIndex];
            $this->readerStorage = new medoo(
                    array(
                            'database_type' => $slaveConifg['DB_TYPE'],
                            'database_name' => $slaveConifg['DB_NAME'],
                            'server'        => $slaveConifg['DB_HOST'],
                            'username'      => $slaveConifg['DB_USER'],
                            'password'      => $slaveConifg['DB_PASS'],
                    )
            );
        }
    }

    public function setLatestStorageType($type)
    {
        if ($type === self::LATEST_STORAGE_WRITER || $type === self::LATEST_STORAGE_READER) {
            $this->latestStorageType = $type;
        }
    }

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
        $this->setLatestStorageType(self::LATEST_STORAGE_WRITER);
        return $this->writerStorage->insert($this->tableName, $data);
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
        $this->setLatestStorageType(self::LATEST_STORAGE_WRITER);
        array_unshift($data, $this->tableName);
        return call_user_func_array(array($this->writerStorage, 'insert'), $data);
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
        $this->setLatestStorageType(self::LATEST_STORAGE_WRITER);
        return $this->writerStorage->update($this->tableName, $data, $where);
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
        $this->setLatestStorageType(self::LATEST_STORAGE_READER);
        return $this->readerStorage->get($this->tableName, $columns, $where);
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
        $this->setLatestStorageType(self::LATEST_STORAGE_READER);
        return $this->readerStorage->select($this->tableName, $columns, $where);
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
        $this->setLatestStorageType(self::LATEST_STORAGE_READER);
        return $this->readerStorage->select($this->tableName, $join, $columns, $where);
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
        $this->setLatestStorageType(self::LATEST_STORAGE_READER);
        return $this->writerStorage->delete($this->tableName, $where);
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
        $this->setLatestStorageType(self::LATEST_STORAGE_READER);
        return $this->readerStorage->count($this->tableName, $where);
    }

    /**
     * @param string $column The target column will be calculated.
     * @param array  $where
     *
     * @return int  The maximum number of the column.
     */
    public function getMax($column, $where)
    {
        $this->setLatestStorageType(self::LATEST_STORAGE_READER);
        return $this->readerStorage->max($this->tableName, $column, $where);
    }

    /**
     * @param string $column The target column will be calculated.
     * @param array  $where
     *
     * @return int  The minimum number of the column.
     */
    public function getMin($column, $where)
    {
        $this->setLatestStorageType(self::LATEST_STORAGE_READER);
        return $this->readerStorage->min($this->tableName, $column, $where);
    }

    /**
     * @param string $column The target column will be calculated.
     * @param array  $where
     *
     * @return int  The average number of the column.
     */
    public function getAvg($column, $where)
    {
        $this->setLatestStorageType(self::LATEST_STORAGE_READER);
        return $this->readerStorage->avg($this->tableName, $column, $where);
    }

    /**
     * @param string $column The target column will be calculated.
     * @param array  $where
     *
     * @return int  The total number of the column.
     */
    public function getSum($column, $where)
    {
        $this->setLatestStorageType(self::LATEST_STORAGE_READER);
        return $this->readerStorage->sum($this->tableName, $column, $where);
    }

    /**
     * @param string $column The target column will be calculated.
     * @param array  $where
     *
     * @return int  The total number of the column.
     */
    public function has($where)
    {
        $this->setLatestStorageType(self::LATEST_STORAGE_READER);
        return $this->readerStorage->has($this->tableName, $where);
    }

    /**
     * @param string $join Table relativity for table joining.
     * @param array  $where
     *
     * @return int  The total number of the column.
     */
    public function hasWithJoin($join, $where)
    {
        $this->setLatestStorageType(self::LATEST_STORAGE_READER);
        return $this->readerStorage->has($this->tableName, $join, $where);
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
        if (is_null($type)) {
            if ('SELECT' === strtoupper(substr($query, 0, 6))) {
                $type = self::LATEST_STORAGE_READER;
            } else {
                $type = self::LATEST_STORAGE_WRITER;
            }
        }

        if ($type === self::LATEST_STORAGE_READER) {
            return $this->readerStorage->query($query);
        } else if ($type === self::LATEST_STORAGE_WRITER) {
            return $this->writerStorage->query($query);
        }
        return null;
    }

    /**
     * @param $string
     *
     * @return string
     */
    public function quote($string)
    {
        return $this->readerStorage->quote($string);
    }

    /**
     * @return array an array of error information about the last operation performed
     */
    public function error()
    {
        if ($this->latestStorageType === self::LATEST_STORAGE_READER) {
            return $this->readerStorage->error();
        } else {
            return $this->writerStorage->error();
        }
    }

    /**
     * @return mixed Return the last query performed.
     */
    public function lastQuery()
    {
        if ($this->latestStorageType === self::LATEST_STORAGE_READER) {
            return $this->readerStorage->last_query();
        } else {
            return $this->writerStorage->last_query();
        }
    }

    /**
     * @return array
     */
    public function getDatabaseInfo()
    {
        if ($this->latestStorageType === self::LATEST_STORAGE_READER) {
            return $this->readerStorage->info();
        } else {
            return $this->writerStorage->info();
        }
    }
}