<?php

/**
 * 数据库相关 DAO
 * @author Jeff.Liu<jeff.liu.guo@gmail.com>
 *
 * @version 0.1
 *
 * Class BaseDBDAO
 */
class UserSplitDBDAO extends BaseDBDAO
{
    protected $tableName = 'user_split';
    protected static $dbKey = 'db.split';
    protected static $readKey = 'db.split';
}
