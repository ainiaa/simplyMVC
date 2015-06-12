<?php

/**
 * 数据库相关 DAO
 * @author  Jeff Liu
 *
 * @version 0.1
 *
 * Class BaseDBDAO
 */
class UserSplitDBDAO extends BaseDBDAO
{
    protected $tableName = 'user_split';
    protected static $writeKey = 'db.split';
    protected static $readKey = 'db.split';
}
