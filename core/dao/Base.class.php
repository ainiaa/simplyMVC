<?php

class BaseDAO extends Object
{

    protected $tableName = '';
    protected $realTableName = '';

    /**
     * @var medoo
     */
    protected $db = null;

    public function __construct()
    {
        parent::__construct();

        if (empty($this->realTableName)) {
            $this->tableName = C('DB_PREFIX') . $this->tableName;
        }

        $this->initDb();
    }

    public function initDb()
    {
        if (empty($this->db)) {
            $this->db = new medoo(array(
                    'database_type' => C('DB_TYPE', 'mysql'),
                    'database_name' => C('DB_NAME', 'test'),
                    'server'        => C('DB_HOST', 'localhost'),
                    'username'      => C('DB_USER', 'root'),
                    'password'      => C('DB_PASS', ''),
            ));
        }

        return $this->db;
    }

    /**
     *
     * 创建一条新的记录
     * @author jeffliu
     * powered by jeff 2011-5-31
     * @see    http://medoo.in/api/insert
     *
     * @param array $data
     *
     * @return int the last insert id
     */
    public function add($data)
    {
        return $this->db->insert($this->tableName, $data);
    }

    /**
     * 添加数据之前的动作
     * @author jeff liu
     */
    protected function preAdd()
    {

    }

    /**
     * 添加数据之后的动作
     * @author jeff liu
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
        array_unshift($data, $this->tableName);
        return call_user_func_array(array($this->db, 'insert'), $data);
    }


    /**
     * 添加数据之前的动作
     * @author jeff liu
     */
    protected function preMultiAdd()
    {
    }

    /**
     * 添加数据之后的动作
     * @author jeff liu
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
        return $this->db->update($this->tableName, $data, $where);
    }

    /**
     * 更新数据之前的动作
     * @author jeff liu
     */
    protected function preUpdate()
    {

    }

    /**
     * 更新数据之后的动作
     * @author jeff liu
     */
    protected function postUpdate()
    {

    }

    /**
     * 获得数据
     * @author jeff liu
     * powered by jeff 2011-5-31
     *
     * @param array|string $columns
     * @param              array
     *
     * @return array Return the data of the column.
     */
    public function getOne($columns, $where = array())
    {
        $return = $this->db->get($this->tableName, $columns, $where);
        return $return;
    }

    /**
     * 获得数据之前的动作
     * @author jeff liu
     */
    protected function preGetOne()
    {

    }

    /**
     * 获得数据之后的动作
     * @author jeff liu
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
        $return = $this->db->select($this->tableName, $columns, $where);

        echo '<br >last sql:',$this->db->last_query(),'<br >';

        return $return;
    }

    /**
     * 获得数据之前的动作
     * @author jeff liu
     */
    protected function preGetAll()
    {
    }

    /**
     * 获得数据之后的动作
     * @author jeff liu
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
        return $this->db->select($this->tableName, $join, $columns, $where);
    }


    /**
     * 删除数据
     * @author jeff liu
     * @see    http://medoo.in/api/delete
     * powered by jeff 2011-5-31
     *
     * @param array $where
     *
     * @return int The number of rows affected.
     */
    public function delete($where = array())
    {
        return $this->db->delete($this->tableName, $where);
    }

    /**
     * 删除数据之前的动作
     * @author jeff liu
     */
    protected function preDelete()
    {
    }

    /**
     * 删除数据之后的动作
     * @author jeff liu
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
        return $this->db->count($this->tableName, $where);
    }

    /**
     * @param string $column The target column will be calculated.
     * @param array  $where
     *
     * @return int  The maximum number of the column.
     */
    public function getMax($column, $where)
    {
        return $this->db->max($this->tableName, $column, $where);
    }

    /**
     * @param string $column The target column will be calculated.
     * @param array  $where
     *
     * @return int  The minimum number of the column.
     */
    public function getMin($column, $where)
    {
        return $this->db->min($this->tableName, $column, $where);
    }

    /**
     * @param string $column The target column will be calculated.
     * @param array  $where
     *
     * @return int  The average number of the column.
     */
    public function getAvg($column, $where)
    {
        return $this->db->avg($this->tableName, $column, $where);
    }

    /**
     * @param string $column The target column will be calculated.
     * @param array  $where
     *
     * @return int  The total number of the column.
     */
    public function getSum($column, $where)
    {
        return $this->db->sum($this->tableName, $column, $where);
    }

    /**
     * @param string $column The target column will be calculated.
     * @param array  $where
     *
     * @return int  The total number of the column.
     */
    public function has($where)
    {
        return $this->db->has($this->tableName, $where);
    }

    /**
     * @param string $join Table relativity for table joining.
     * @param array  $where
     *
     * @return int  The total number of the column.
     */
    public function hasWithJoin($join, $where)
    {
        return $this->db->has($this->tableName, $join, $where);
    }

    /**
     * @param $query
     *
     * @return object The PDOStatement object.
     */
    public function query($query)
    {
        return $this->db->query($query);
    }

    /**
     * @param $string
     *
     * @return string
     */
    public function quote($string)
    {
        return $this->db->quote($string);
    }

    /**
     * @return array an array of error information about the last operation performed
     */
    public function error()
    {
        return $this->db->error();
    }

    /**
     * @return mixed Return the last query performed.
     */
    public function last_query()
    {
        return $this->db->last_query();
    }

    /**
     * @return array
     */
    public function getDatabaseInfo()
    {
        return $this->db->info();
    }
}
