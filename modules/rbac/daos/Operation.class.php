<?php

/**
 * 权限相关 model
 *
 * @author : Jeff.Liu<jeff.liu.guo@gmail.com>
 * @date   : 2017/05/27
 */
class OperationDAO extends RedisDBBase
{
    protected $tableName = 'tp_operation';
    protected $_pk = 'id';
}