<?php

/**
 * Class mysqliWrapper
 */
class mysqliWrapper extends DatabaseAbstract implements DatabaseWrapper
{
    public $driverName = 'mysql';

    // lazy loading
    protected function initialization()
    {
        if (!($this->link instanceof mysqli)) {
            $this->link = call_user_func_array(array(new ReflectionClass('mysqli'), 'newInstance'), $this->initParams);
            foreach ($this->initialization as $val) {
                $this->link->query($val);
            }
        }

        return $this->link;
    }

    public function query()
    {
        $params = func_get_args();
        $sql    = array_shift($params);

        if ($this->getConfig('replaceTableName')) {
            $sql = preg_replace_callback('/{{(\w+)}}/', array($this, 'getTable'), $sql);
        }

        Database::$debug && Database::$sql[] = $sql;

        $this->initialization();

        if (isset($params[0])) {
            if (is_array($params[0])) {
                $params = $params[0];
            }
            if (preg_match_all('/:(\w+)/i', $sql, $tmp)) {
                $p = array();
                foreach ($tmp[1] as $val) {
                    $p[] = $params[$val];
                }
                $params = $p;
                $sql    = str_replace($tmp[0], '?', $sql);
            }
            if (!$stmt = $this->link->prepare($sql)) {
                throw new DatabaseException("Error sql query:$sql");
            }

            $refs = array();
            foreach ($params as $key => $value) {
                $refs[$key] = & $params[$key];
            }

            $s = str_repeat('s', count($refs));
            array_unshift($refs, $s);
            call_user_func_array(array($stmt, 'bind_param'), $refs);
        } elseif (!$stmt = $this->link->prepare($sql)) {
            throw new DatabaseException("Error sql query:$sql");
        }

        $stmt->execute();

        return $stmt;
    }

    public function exec()
    {
        $param = func_get_args();
        $stmt  = call_user_func_array(array($this, 'query'), $param);

        return $stmt->affected_rows;
    }

    public function getOne()
    {
        $param = func_get_args();
        $stmt  = call_user_func_array(array($this, 'query'), $param);

        $stmt->bind_result($result);
        $stmt->fetch();

        return $result;
    }

    public function getCol()
    {
        $param = func_get_args();
        $stmt  = call_user_func_array(array($this, 'query'), $param);

        $stmt->bind_result($result);
        $out = array();
        while ($stmt->fetch()) {
            $out[] = $result;
        }

        return $out;
    }

    public function getAll()
    {
        $param = func_get_args();
        $stmt  = call_user_func_array(array($this, 'query'), $param);

        $result = array();
        while ($rt = $this->fetch($stmt)) {
            $result[] = $rt;
        }

        return $result;
    }

    public function fetch($stmt, $result_type = Database::ASSOC)
    {
        $field  = $stmt->result_metadata()->fetch_fields();
        $out    = array();
        $fields = array();
        foreach ($field as $val) {
            $fields[] = & $out[$val->name];
        }
        call_user_func_array(array($stmt, 'bind_result'), $fields);
        if (!$stmt->fetch()) {
            return array();
        }

        if ($result_type == Database::ASSOC) {
            return $out;
        } elseif ($result_type == Database::NUM) {
            return array_values($out);
        }

        return array_merge($out, array_values($out));
    }

    public function lastInsertId()
    {
        $this->initialization();

        return $this->link->insert_id;
    }
}