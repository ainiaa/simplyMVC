<?php

/**
 * 模版基类
 * 定义了 模版类公有的方法
 * @author jeff liu
 */

//Importer::importFile('core.model.db', 'class.php', ROOT_PATH);

class BaseModel extends Object
{
    protected $table_name = ''; //表名
    protected $alias_name = ''; //表别名
    protected $pk = ''; //主键
    protected $db = null; //数据库链接实例
    protected $prefix = ''; //表前缀
    protected $real_table = ''; //表真实的名称
    protected $db_name = ''; //数据库名称

    /**
     *
     * @author jeff liu
     */
    public function __construct()
    {
        parent::__construct();

        //$this->initDb();

        $this->table_name = empty($this->table_name) ? lcfirst(
                str_replace('Model', '', get_class($this))
        ) : $this->table_name;
        $this->table_name = $this->prefix . $this->table_name;

        $this->alias_name = empty($this->alias_name) ? $this->table_name : $this->alias_name;

        $this->initDb();

    }

    /**
     * 初始化数据库
     * @author jeff liu
     *
     * @param string $db_type
     */
    private function initDb($db_type = null)
    {
        if (empty($db_type)) {
            $db_type = 'mysql';
        }
        $this->db = new Database(array('pdo', 'mysql:dbname=test;host=localhost', 'root', ''));
    }

    /**
     *
     * 创建一条新的记录
     * @author jeffliu
     * powered by jeff 2011-5-31
     */
    public function add($data)
    {

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
     * 更新
     * powered by jeff 2011-5-31
     */
    public function update()
    {

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
     */
    public function getOne()
    {

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


    public function getAll()
    {
        $sql = 'SELECT * FROM ' . $this->table_name;
        return $this->db->getAll($sql);
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
     * 删除数据
     * @author jeff liu
     * powered by jeff 2011-5-31
     */
    public function delete()
    {

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
     *
     * powered by jeff 2011-5-31
     *
     * @param unknown_type $id
     */
    public function retrieveByPk($id)
    {

    }

}