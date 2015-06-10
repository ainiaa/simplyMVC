<?php

class MapDbStorage
{
    private static $uuidName;
    private static $uuidValue;
    private static $dbName;
    private static $tableName;
    private static $mapPrefix;
    private static $isPrimary;
    private static $manyIndex;

    /**
     * 获得单条记录
     *
     * @param array $param
     *
     * @return array|bool|mixed
     */
    public static function getOneResult($param)
    {
        $dbHandle = self::dbHandle($param['key']);
        if (empty($dbHandle)) {
            return false;
        }

        $where   = isset($param['where']) ? $param['where'] : null;
        $fields  = isset($param['fields']) ? $param['fields'] : null;
        $orderBy = isset($param['orderBy']) ? $param['orderBy'] : null;
        $dbSql   = self::getConditionQuerySentence($fields, $where, $orderBy);
        $result  = $dbHandle->getOneResult($dbSql);

        return $result;
    }

    /**
     * 获得多条记录
     *
     * @param array $param
     *
     * @return array|bool
     */
    public static function getManyResult($param)
    {
        $dbHandle = self::dbHandle($param['key']);
        if (empty($dbHandle)) {
            return false;
        }

        $where   = isset($param['where']) ? $param['where'] : null;
        $fields  = isset($param['fields']) ? $param['fields'] : null;
        $orderBy = isset($param['orderBy']) ? $param['orderBy'] : null;
        $start   = isset($param['start']) ? $param['start'] : null;
        $length  = isset($param['length']) ? $param['length'] : null;
        $dbSql   = self::getConditionQuerySentence($fields, $where, $orderBy, $start, $length);
        $result  = $dbHandle->getManyResult($dbSql);
        if (is_array($result)) {
            $tmp = array();
            foreach ($result as $value) {
                $tmp[$value[self::$manyIndex]] = $value;
            }

            $result = $tmp;
        }

        return $result;
    }

    /**
     * 插入单条数据
     *
     * @param array $param
     *
     * @return bool|int|string
     */
    public static function insert($param)
    {
        $dbHandle = self::dbHandle($param['key']);
        if (empty($dbHandle)) {
            return false;
        }

        $value    = isset($param['value']) ? $param['value'] : null;
        $dbSql    = self::getInsertSentence($value);
        $result   = $dbHandle->dbExec($dbSql);
        $insertId = $dbHandle->getInsertId();

        return empty($insertId) ? $result : $insertId;
    }

    /**
     * 插入多条数据
     *
     * @param array $param
     *
     * @return bool|int
     */
    public static function insertMany($param)
    {
        $dbHandle = self::dbHandle($param['key']);
        if (empty($dbHandle)) {
            return false;
        }

        $value  = isset($param['value']) ? $param['value'] : null;
        $dbSql  = self::getInsertManySentence($value);
        $result = $dbHandle->dbExec($dbSql);
        return $result;
    }

    /**
     * 更新数据
     *
     * @param array $param
     *
     * @return bool|int
     */
    public static function update($param)
    {
        if (empty($param['value'])) {
            return Logger::getInstance()->error(
                    array('msg' => 'MDS003', 'no' => 'MDS003', 'param' => array('paramString' => 'MDS003'))
            );//AC::setErrorNo('MDS003');
        }

        $dbHandle = self::dbHandle($param['key']);
        if (empty($dbHandle)) {
            return false;
        }

        $value  = $param['value'];
        $where  = isset($param['where']) ? $param['where'] : null;
        $dbSql  = self::getUpdateConditionSentence($value, $where);
        $result = $dbHandle->dbExec($dbSql);

        return $result;
    }

    /**
     * 删除数据
     *
     * @param array $param
     *
     * @return bool|int
     */
    public static function remove($param)
    {
        $dbHandle = self::dbHandle($param['key']);
        if (empty($dbHandle)) {
            return false;
        }

        $where  = isset($param['where']) ? $param['where'] : null;
        $dbSql  = self::getRemoveConditionSentence($where);
        $result = $dbHandle->dbExec($dbSql);

        return $result;
    }

    /**
     * 获得数据条数
     *
     * @param array $param
     *
     * @return bool|int
     */
    public static function fetchCountRow($param)
    {
        $dbHandle = self::dbHandle($param['key']);
        if (empty($dbHandle)) {
            return false;
        }

        $where  = isset($param['where']) ? $param['where'] : null;
        $dbSql  = self::getCountRowSentence($where);
        $result = $dbHandle->getOneResult($dbSql);
        if (empty($result['num'])) {
            return 0;
        }
        return $result['num'];
    }

    /**
     * 保存数据
     *
     * @param array $param
     *
     * @return bool|int|string
     */
    public static function save($param)
    {
        $result = self::update($param);
        if (empty($result)) {
            $result = self::insert($param);
            if (empty($result)) {
                $result = 0;
            }
        }

        return $result;
    }

    /**
     * 更新数据
     *
     * @param array $param
     *
     * @return bool|int
     */
    public static function limitUpdate($param)
    {
        $dbHandle = self::dbHandle($param['key']);
        if (empty($dbHandle)) {
            return false;
        }

        $where  = isset($param['where']) ? $param['where'] : null;
        $value  = isset($param['value']) ? $param['value'] : null;
        $dbSql  = self::getLimitUpdateSentence($value, $where);
        $result = $dbHandle->dbExec($dbSql);

        return $result;
    }

    /**
     * 更新数据
     *
     * @param array $param
     *
     * @return bool|int|string
     */
    public static function complexUpdate($param)
    {
        if (empty($param['value']) && empty($param['limit'])) {
            return false;
        }
        if (empty($param['value'])) {
            $param['value'] = $param['limit'];
            return self::limitUpdate($param);
        }
        if (empty($param['limit'])) {
            return self::save($param);
        }

        $dbHandle = self::dbHandle($param['key']);
        if (empty($dbHandle)) {
            return false;
        }

        $where  = isset($param['where']) ? $param['where'] : null;
        $value  = $param['value'];
        $limit  = $param['limit'];
        $dbSql  = self::getComplexUpdateSentence($value, $limit, $where);
        $result = $dbHandle->dbExec($dbSql);

        return $result;
    }

    /**
     * 执行sql
     *
     * @param string $key
     * @param string $dbSql
     *
     * @return bool|int
     */
    public static function execDbSentence($key, $dbSql)
    {
        if (empty($dbSql)) {
            return false;
        }

        $dbHandle = self::dbHandle($key);
        if (empty($dbHandle)) {
            return false;
        }

        $result = $dbHandle->dbExec($dbSql);
        return $result;
    }

    /**
     * 获取数据库执行相关信息
     *
     * @param string $key
     * @param string $dbSql
     * @param int    $rows
     *
     * @return array|bool|mixed
     */
    public static function fetchDbSentence($key, $dbSql, $rows = 1)
    {
        if (empty($dbSql)) {
            return false;
        }

        $dbHandle = self::dbHandle($key);
        if (empty($dbHandle)) {
            return false;
        }

        if (1 == $rows) {
            $result = $dbHandle->getOneResult($dbSql);
        } else {
            $result = $dbHandle->getManyResult($dbSql);
        }

        return $result;
    }

    /**
     * @param      $fields
     * @param      $orderBy
     * @param null $start
     * @param null $len
     *
     * @return string
     */
    private static function getQuerySentence($fields, $orderBy, $start = null, $len = null)
    {
        $field = ' * ';
        if (!empty($fields)) {
            if (is_array($fields)) {
                $field = ' `' . implode('`,`', $fields) . '` ';
            } else {
                $field = $fields;
            }
        }
        $where = Array();
        self::getPrimaryKeyConditionArray($where);
        if (empty($where[self::$uuidName]) || empty(self::$uuidValue)) {
            $whereString = ' TRUE ';
        } else {
            $whereString = self::$uuidName . '="' . self::$uuidValue . '" ';
        }
        $limit = null;
        if (!is_null($start) && !empty($len)) {
            $limit = ' LIMIT ' . intval($start) . ',' . intval($len);
        }
        $orderByString = null;
        if (!empty($orderBy)) {
            $orderByString = ' ORDER BY ' . $orderBy;
        }
        $sql = '
                    SELECT ' . $field . '
                    FROM ' . self::$dbName . '.' . self::$tableName . '
                    WHERE ' . $whereString . $orderByString . $limit;
        return $sql;
    }

    /**
     * @param      $fields
     * @param      $where
     * @param      $orderBy
     * @param null $start
     * @param null $len
     *
     * @return string
     */
    private static function getConditionQuerySentence($fields, $where, $orderBy, $start = null, $len = null)
    {
        if (empty($where)) {
            return self::getQuerySentence($fields, $orderBy, $start, $len);
        }

        $conditionArray = array();
        $whereString    = null;
        if (is_array($where)) {
            self::getPrimaryKeyConditionArray($where);

            foreach ($where as $field => $v) {
                if (empty($field)) {
                    continue;
                }
                $conditionArray[] = '`' . $field . '`="' . $v . '"';
            }
            $whereString = implode(' AND ', $conditionArray);
        } else {
            $whereString = $where;
            self::getPrimaryKeyConditionString($whereString);
        }
        if (empty($whereString)) {
            return self::getQuerySentence($fields, $orderBy, $start, $len);
        }

        $field = ' * ';
        if (!empty($fields)) {
            if (is_array($fields)) {
                $field = ' `' . implode('`,`', $fields) . '` ';
            } else {
                $field = $fields;
            }
        }
        $limit = null;
        if (!is_null($start) && !empty($len)) {
            $limit = ' LIMIT ' . intval($start) . ',' . intval($len);
        }
        $orderByString = null;
        if (!empty($orderBy)) {
            $orderByString = ' ORDER BY ' . $orderBy;
        }

        $sql = '
                    SELECT ' . $field . '
                    FROM ' . self::$dbName . '.' . self::$tableName . '
                    WHERE ' . $whereString . $orderByString . $limit;
        return $sql;
    }


    /**
     * @param $where
     *
     * @return string
     */
    private static function getCountRowSentence($where)
    {
        if (empty($where)) {
            $whereString = null;
            self::getPrimaryKeyConditionString($whereString);
        } elseif (is_array($where)) {
            self::getPrimaryKeyConditionArray($where);

            $conditionArray = array();
            foreach ($where as $field => $v) {
                if (empty($field)) {
                    continue;
                }
                $conditionArray[] = '`' . $field . '`="' . $v . '"';
            }
            $whereString = implode(' AND ', $conditionArray);
        } else {
            $whereString = $where;
            self::getPrimaryKeyConditionString($whereString);
        }

        $sql = 'SELECT
                count(*) AS num
                FROM ' . self::$dbName . '.' . self::$tableName . '
                WHERE ' . $whereString;
        return $sql;
    }

    /**
     * @param $value
     *
     * @return string
     */
    private static function getInsertSentence($value)
    {
        $sql = 'INSERT INTO ' . self::$dbName . '.' . self::$tableName . ' (`' . self::$uuidName . '`) VALUES("' . self::$uuidValue . '")';
        if (is_array($value)) {
            $fields = $newValues = array();
            foreach ($value as $field => $newValue) {
                if (empty($field)) {
                    continue;
                }
                $fields[]    = '`' . $field . '`';
                $newValues[] = "'" . SmvcUtilHelper::replaceData($newValue) . "'";
            }
            if (empty($fields)) {
                return $sql;
            }

            $fieldString = ' (' . implode(',', $fields) . ') ';
            $valueString = ' (' . implode(',', $newValues) . ') ';
            $sql         = '
                        INSERT INTO ' . self::$dbName . '.' . self::$tableName . $fieldString . '
                        VALUES ' . $valueString;
        }

        return $sql;
    }

    /**
     * @param $value
     *
     * @return string
     */
    private static function getInsertManySentence($value)
    {
        if (empty($value[0]) || !is_array($value[0])) {
            return self::getInsertSentence($value);
        }
        if (1 == count($value)) {
            return self::getInsertSentence($value[0]);
        }

        $fieldString = null;
        $valueString = array();
        foreach ($value as $mapArray) {
            $fields = $newValues = array();
            foreach ($mapArray as $field => $newValue) {
                if (empty($field)) {
                    continue;
                }
                if (empty($fieldString)) {
                    $fields[] = '`' . $field . '`';
                }

                $newValues[] = "'" . SmvcUtilHelper::replaceData($newValue) . "'";
            }
            if (empty($newValues)) {
                continue;
            }

            if (empty($fieldString)) {
                $fieldString = ' (' . implode(',', $fields) . ') ';
            }

            $valueString[] = ' (' . implode(',', $newValues) . ') ';
        }

        if (empty($valueString)) {
            return self::getInsertSentence($value);
        }
        $sql = '
                    INSERT INTO ' . self::$dbName . '.' . self::$tableName . $fieldString . '
                    VALUES ' . implode(',', $valueString);

        return $sql;
    }

    /**
     * @param $value
     *
     * @return null|string
     */
    private static function getUpdateSentence($value)
    {
        $sql = null;
        if (empty($value) || !is_array($value)) {
            return $sql;
        }

        $setArray = array();
        foreach ($value as $field => $newValue) {
            if (empty($field)) {
                continue;
            }
            $setArray[] = '`' . $field . "` = '" . SmvcUtilHelper::replaceData($newValue) . "'";
        }
        if (empty($setArray)) {
            return $sql;
        }

        $sql = '
                    UPDATE ' . self::$dbName . '.' . self::$tableName . '
                    SET ' . implode(',', $setArray) . '
                    WHERE ' . self::$uuidName . '="' . self::$uuidValue . '"';
        #       self::$uuidName. '="'. self::$uuidValue. '"
        #   LIMIT 1
        #   ';
        return $sql;
    }

    /**
     * @param $value
     * @param $where
     *
     * @return null|string
     */
    private static function getUpdateConditionSentence($value, $where)
    {
        if (empty($where)) {
            return self::getUpdateSentence($value);
        }

        $sql = null;
        if (empty($value) || !is_array($value)) {
            return $sql;
        }

        # if where is null ,the call getUpdateSentence()
        $conditionArray = array();
        $whereString    = null;
        if (is_array($where)) {
            self::getPrimaryKeyConditionArray($where);

            foreach ($where as $field => $v) {
                if (empty($field)) {
                    continue;
                }
                $conditionArray[] = '`' . $field . '`="' . $v . '"';
            }
            $whereString = implode(' AND ', $conditionArray);
        } else {
            $whereString = $where;
            self::getPrimaryKeyConditionString($whereString);
        }
        if (empty($whereString)) {
            return self::getUpdateSentence($value);
        }

        # if set value is null, return NULL
        $setArray = array();
        foreach ($value as $field => $newValue) {
            if (empty($field)) {
                continue;
            }
            $setArray[] = '`' . $field . "` = '" . SmvcUtilHelper::replaceData($newValue) . "'";
        }
        if (empty($setArray)) {
            return $sql;
        }

        $sql = '
                    UPDATE ' . self::$dbName . '.' . self::$tableName . '
                    SET ' . implode(',', $setArray) . '
                    WHERE ' . $whereString;
        return $sql;
    }

    /**
     * @return string
     */
    private static function getRemoveSentence()
    {
        $sql = '
                    DELETE
                    FROM ' . self::$dbName . '.' . self::$tableName . '
                    WHERE ' . self::$uuidName . '="' . self::$uuidValue . '"';
        #       self::$uuidName. '="'. self::$uuidValue. '"
        #   LIMIT 1
        #   ';
        return $sql;
    }

    /**
     * @param $where
     *
     * @return string
     */
    private static function getRemoveConditionSentence($where)
    {
        if (empty($where)) {
            return self::getRemoveSentence();
        }

        $conditionArray = array();
        $whereString    = null;
        if (is_array($where)) {
            self::getPrimaryKeyConditionArray($where);

            foreach ($where as $field => $v) {
                if (empty($field)) {
                    continue;
                }
                $conditionArray[] = '`' . $field . '`="' . $v . '"';
            }
            $whereString = implode(' AND ', $conditionArray);
        } else {
            $whereString = $where;
            self::getPrimaryKeyConditionString($whereString);
        }
        if (empty($whereString)) {
            return self::getRemoveSentence();
        }

        $sql = '
                    DELETE
                    FROM ' . self::$dbName . '.' . self::$tableName . '
                    WHERE ' . $whereString;
        return $sql;
    }

    /**
     * @param      $value
     * @param null $where
     *
     * @return null|string
     */
    private static function getLimitUpdateSentence($value, $where = null)
    {
        $sql = null;
        if (empty($value) || !is_array($value)) {
            return $sql;
        }

        $setArray = $whereArray = array();
        if (!empty($where)) {
            if (is_array($where)) {
                foreach ($where as $f => $v) {
                    $whereArray[] = ' ' . $f . '="' . $v . '"';
                }
            } else {
                $whereArray[] = ' ' . $where . ' ';
            }
        }
        $whereArray[] = self::$uuidName . '="' . self::$uuidValue . '" ';
        foreach ($value as $field => $changeValue) {
            if (empty($field) || empty($changeValue)) {
                continue;
            }

            if ($changeValue > 0) {
                $setArray[] = ' `' . $field . '` = `' . $field . '` + ' . $changeValue . ' ';
                continue;
            }

            $absValue     = abs($changeValue);
            $setArray[]   = ' `' . $field . '` = `' . $field . '` - ' . $absValue . ' ';
            $whereArray[] = ' (' . $field . ' >= ' . $absValue . ') ';
        }
        if (empty($setArray)) {
            return $sql;
        }
        $sql = '
                    UPDATE ' . self::$dbName . '.' . self::$tableName . '
                    SET ' . implode(',', $setArray) . '
                    WHERE ' . implode(' AND ', $whereArray);
        #       implode(' AND ', $whereArray). '
        #   LIMIT 1
        #   ';
        return $sql;
    }

    /**
     * @param      $value
     * @param      $limit
     * @param null $where
     *
     * @return null|string
     */
    private static function getComplexUpdateSentence($value, $limit, $where = null)
    {
        $sql = null;
        if (empty($value) || !is_array($value)) {
            return $sql;
        }
        if (empty($limit) || !is_array($limit)) {
            return $sql;
        }

        $setArray = $whereArray = array();
        if (!empty($where)) {
            if (is_array($where)) {
                foreach ($where as $f => $v) {
                    $whereArray[] = ' ' . $f . '="' . $v . '"';
                }
            } else {
                $whereArray[] = ' ' . $where . ' ';
            }
        }
        $whereArray[] = self::$uuidName . '="' . self::$uuidValue . '" ';
        foreach ($limit as $field => $changeValue) {
            if (empty($field) || empty($changeValue)) {
                continue;
            }

            if ($changeValue > 0) {
                $setArray[] = ' `' . $field . '` = `' . $field . '` + ' . $changeValue . ' ';
                continue;
            }

            $absValue     = abs($changeValue);
            $setArray[]   = ' `' . $field . '` = `' . $field . '` - ' . $absValue . ' ';
            $whereArray[] = ' (' . $field . ' >= ' . $absValue . ') ';
        }
        foreach ($value as $field => $v) {
            $setArray[] = ' `' . $field . '` = "' . $v . '" ';
        }
        if (empty($setArray)) {
            return $sql;
        }
        $sql = '
                    UPDATE ' . self::$dbName . '.' . self::$tableName . '
                    SET ' . implode(',', $setArray) . '
                    WHERE ' . implode(' AND ', $whereArray);
        #       implode(' AND ', $whereArray). '
        #   LIMIT 1
        #   ';
        return $sql;
    }

    /**
     * @param $whereArray
     *
     * @return null
     */
    private static function getPrimaryKeyConditionArray(&$whereArray)
    {
        if (self::$isPrimary && !empty(self::$uuidValue)) {
            $whereArray = array(self::$uuidName => self::$uuidValue) + $whereArray;
        }
        return null;
    }

    /**
     * @param $whereString
     *
     * @return null
     */
    private static function getPrimaryKeyConditionString(&$whereString)
    {
        if (self::$isPrimary && !empty(self::$uuidValue)) {
            if (empty($whereString)) {
                $whereString = self::$uuidName . '="' . self::$uuidValue . '"';
            } else {
                $whereString = ' ' . self::$uuidName . '="' . self::$uuidValue . '" AND ' . $whereString;
            }
        }
        return null;
    }

    /**
     * @param $key
     *
     * @return DatabaseStorage
     */
    private static function dbHandle($key)  
    {
        $configArray = SC::getDbClient($key);
        if (empty(SC::$dbHostConfig[$configArray['clientServer']])) {
            #   exit ('DB server is not exist! ');
            #      'MDS001';
            return Logger::getInstance()->error(
                    array('msg' => 'MDS001', 'no' => 'MDS001', 'param' => array('paramString' => 'MDS001'))
            );
        }
        $hostConfig = SC::$dbHostConfig[$configArray['clientServer']];

        self::$uuidName  = $configArray['uuidName'];
        self::$uuidValue = $configArray['uuidValue'];
        self::$dbName    = $configArray['dbName'];
        self::$tableName = $configArray['tableName'];
        self::$mapPrefix = $configArray['mapPrefix'];
        self::$isPrimary = $configArray['isPrimary'];
        self::$manyIndex = $configArray['manyIndex'];
        $dbHandle        = new DatabaseStorage();
        $dbHandle->dbConnect($configArray['clientServer'], $hostConfig);
        if (empty($dbHandle)) {
            Logger::getInstance()->error(
                    array('msg' => 'MDS002', 'no' => 'MDS002', 'param' => array('paramString' => 'MDS002'))
            );
        }

        return $dbHandle;
    }

}

