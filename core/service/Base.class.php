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
     */
    private function initDb()
    {
        $this->db = Database::instance(
                array(
                        C('DB_TYPE'),
                        sprintf('mysql:dbname=%s;host=%s', C('DB_NAME'), C('DB_HOST')),
                        C('DB_USER'),
                        C('DB_PASS'),
                )
        );
    }

    public function getAll()
    {
        $sql  = 'SELECT *,"1233333" FROM smvc_test';
        $data = $this->db->getAll($sql);
        SmvcDebugHelper::instance()->debug(
                array('info' => $data, 'label' => '$data ' . __METHOD__, 'level' => 'error')
        );
        return $data;
    }

}
