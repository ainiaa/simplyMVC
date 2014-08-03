<?php

class BaseService extends Object
{
    public $BaseDAO;

    /**
     * @var Database
     */
    public $db;

    public function __construct()
    {
        parent::__construct();
        $this->initDb();
    }

    public function __call($method, $args)
    {
        $dao = $this->getDefaultDAO();
        return $this->$dao->$method($args);
    }

    public function getDefaultDAO()
    {
        return substr(get_class($this), 0, -7) . 'DAO'; //__CLASS__ 和 get_class($this) 返回的结果不一定一样
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

    public function getAll()
    {
        $sql = 'SELECT * FROM smvc_test';
        return $this->db->getAll($sql);
    }

}
