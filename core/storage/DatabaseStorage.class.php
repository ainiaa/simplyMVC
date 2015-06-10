<?php

class DatabaseStorage
{
    /**
     * @var PDO
     */
    private static $dbHandle;

    /**
     * @var PDO
     */
    private $dbLink;

    /**
     * 获得单条记录
     *
     * @param string $sql sql语句
     *
     * @return array|bool|mixed
     */
    public function getOneResult($sql)
    {
        $result = false;
        if (empty($sql)) {
            return $result;
        }

        $query = $this->dbQuery($sql);
        if (empty($query)) {
            return $result;
        }

        $result = $query->fetch(PDO::FETCH_ASSOC);
        if (empty($result)) {
            return array();
        }

        return $result;
    }

    public function getManyResult($sql)
    {
        $result = false;
        if (empty($sql)) {
            return $result;
        }

        $query = $this->dbQuery($sql);
        if (empty($query)) {
            return $result;
        }

        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    public function getInsertId()
    {
        return $this->dbLink->lastInsertId();
    }

    public function dbExec($sql)
    {
        $result = false;
        if (empty($sql)) {
            return $result;
        }

        $result = $this->dbQuery($sql);

        if ($result) {
            $line = $result->rowCount();
            $sql  = trim($sql);

            if (empty($line)) {

                if ('DELETE' == substr($sql, 0, 6)) {
                    $line = true;
                }
            }
            return $line;
        } else {
            $error  = $result->errorInfo();
            $string = $sql . '-' . DOP::encodeData($error);
            return Logger::getInstance()->error(
                    array('msg' => $string, 'no' => 'DBS001', 'param' => array('paramString' => $string))
            );
        }
    }

    /**
     * 执行sql查询
     *
     * @param string $sql
     *
     * @return bool|PDOStatement
     */
    public function dbQuery($sql)
    {
        if (empty($sql)) {
            return false;
        }

        $query = $this->dbLink->prepare($sql);

        try {
            $query->execute();
        } catch (Exception $e) {//todo 报错
        }

        return $query;
    }

    /**
     * 链接mysql
     *
     * @param string $configKey
     * @param array  $hostConfig
     *
     * @return null|PDO
     */
    public function dbConnect($configKey, $hostConfig)
    {
        if (empty($hostConfig) || empty($hostConfig['host'])) {
            exit ('DB server key is wrong! ');
        }

        if (empty(self::$dbHandle[$configKey])) {
            $options = array(
                    PDO::ATTR_CASE               => PDO::CASE_NATURAL,//字段不默认大小写
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,//异常模式
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8',//初始化语句
                #   PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,//使用查询缓存
                #   PDO::ATTR_PERSISTENT => true//长连接
                #   PDO::ATTR_TIMEOUT => 10,//超时
            );

            $host   = isset($hostConfig['host']) ? $hostConfig['host'] : '';
            $port   = isset($hostConfig['port']) ? $hostConfig['port'] : '';
            $user   = isset($hostConfig['user']) ? $hostConfig['user'] : '';
            $passwd = isset($hostConfig['passwd']) ? $hostConfig['passwd'] : '';

            try {
                $dsn          = 'mysql:host=' . $host . ';port=' . $port;
                $this->dbLink = new PDO($dsn, $user, $passwd, $options);
            } catch (Exception $e) {//todo 报错
            }
            if (empty($this->dbLink)) {
                return null;
            }

            self::$dbHandle[$configKey] = $this->dbLink;
        } else {
            $this->dbLink = self::$dbHandle[$configKey];
        }

        return $this->dbLink;
    }

}


