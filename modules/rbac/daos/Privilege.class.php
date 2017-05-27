<?php

/**
 * 权限相关 model
 *
 * @author : Jeff Liu<liuwy@imageco.com.cn>
 * @date   : 2017/05/27
 */
class PrivilegeDAO extends RedisDBBase
{
    protected $tableName = 'tp_privilege';
    protected $_pk = 'id';
    protected $_pk_auto = true;
    protected $_sk = null;
}