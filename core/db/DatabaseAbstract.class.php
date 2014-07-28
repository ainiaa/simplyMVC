<?php
abstract class DatabaseAbstract
{
    protected $initParams = array();
    protected $link = null;
    public $driverName = null;

    protected $config = array(
            'tablePreFix'      => null,
            'replaceTableName' => true,
            'initialization'   => array()
    );

    // get config
    public function __get($key)
    {
        return $this->getConfig($key);
    }

    // set config
    public function __set($key, $value)
    {
        return $this->setConfig($key, $value);
    }

    public function setConfig($key, $value)
    {
        $this->config[$key] = $value;

        return true;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getConfig($key)
    {
        return isset($this->config[$key]) ? $this->config[$key] : false;
    }

    public function getTable($table_name)
    {
        // for preg_replace_callback
        if (is_array($table_name) && isset($table_name[1])) {
            $table_name = $table_name[1];
        }

        $tablePreFix = $this->getConfig('tablePreFix');

        if (!$tablePreFix) {
            return $table_name;
        }

        if (is_string($tablePreFix)) {
            return $tablePreFix . $table_name;
        }

        foreach ($tablePreFix as $key => $val) {
            if ($val == '*') {
                return $key . $table_name;
            }
            if (in_array($table_name, $val)) {
                return $key . $table_name;
            }
        }

        return $table_name;
    }

    function __construct($initParams)
    {
        $this->initParams = $initParams;
        if (!is_array($this->initParams)) {
            $this->link = $this->initParams;
        }
    }

    public function getCol()
    {
        $param = func_get_args();
        $query = call_user_func_array(array($this, 'query'), $param);

        $rs = array();
        while ($rt = $this->fetch($query, Database::NUM)) {
            $rs[] = $rt[0];
        }

        return $rs;
    }

    public function getOne()
    {
        $param = func_get_args();
        $query = call_user_func_array(array($this, 'query'), $param);
        $rs    = $this->fetch($query, Database::NUM);

        return $rs[0];
    }

    public function getAll()
    {
        $param = func_get_args();
        $query = call_user_func_array(array($this, 'query'), $param);

        $rs = array();
        while ($rt = $this->fetch($query, Database::ASSOC)) {
            $rs[] = $rt;
        }

        return $rs;
    }

    public function getRow()
    {
        $param = func_get_args();
        $query = call_user_func_array(array($this, 'query'), $param);
        $rs    = $this->fetch($query, Database::ASSOC);

        return $rs === false ? array() : $rs;
    }

    public function getDriver()
    {
        return $this->initialization();
    }
}