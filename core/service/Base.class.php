<?php

/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 13-2-24
 * Time: 下午7:59
 * To change this template use File | Settings | File Templates.
 */
class BaseService extends Object
{
    public $BaseDAO;

    public function __construct()
    {
        parent::__construct();
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
}
