<?php

/**
 * based on https://github.com/xuanyan/Database
 * Class Database
 *
 * equire_once ‘Database.php’;
 *
 * // pdo
 * $db = Database::connect(‘pdo’, ‘mysql:dbname=test;host=localhost’, ‘root’, ‘root’);
 * $db = new Database(‘pdo’, ‘mysql:dbname=test;host=localhost’, ‘root’, ‘root’);
 *
 * $db = Database::connect(array(‘pdo’, ‘mysql:dbname=test;host=localhost’, ‘root’, ‘root’));
 * $db = new Database(array(‘pdo’, ‘mysql:dbname=test;host=localhost’, ‘root’, ‘root’));
 *
 * $link = new PDO;
 * $db = Database::connect($link);
 * $db = new Database($link);
 *
 * // mysql
 * $db = Database::connect(‘mysql’, ‘localhost’, ‘root’, ‘root’, ‘test’);
 * $db = new Database(‘mysql’, ‘localhost’, ‘root’, ‘root’, ‘test’);
 *
 * $db = Database::connect(array(‘mysql’, ‘localhost’, ‘root’, ‘root’, ‘test’));
 * $db = new Database(array(‘mysql’, ‘localhost’, ‘root’, ‘root’, ‘test’));
 *
 * $link = mysql_connect(‘localhost’, ‘root’, ‘root’);
 * mysql_select_db(‘test’, $link);
 * $db = Database::connect($link);
 * $db = new Database($link);
 *
 * // mysqli
 * $db = Database::connect(‘mysqli’, ‘localhost’, ‘root’, ‘root’, ‘test’);
 * $db = new Database(‘mysqli’, ‘localhost’, ‘root’, ‘root’, ‘test’);
 *
 * $db = Database::connect(array(‘mysqli’, ‘localhost’, ‘root’, ‘root’, ‘test’));
 * $db = new Database(array(‘mysqli’, ‘localhost’, ‘root’, ‘root’, ‘test’));
 *
 * $link = new mysqli(‘localhost’, ‘root’, ‘root’, ‘test’);
 * $db = Database::connect($link);
 * $link = mysqli_init();
 * $link→real_connect(‘localhost’, ‘root’, ‘root’, ‘test’);
 * $db = Database::connect($link);
 * $db = new Database($link);
 *
 * // sqlite
 * $db = Database::connect(‘sqlite’, ‘test.sqlite’);
 * $db = new Database(‘sqlite’, ‘test.sqlite’);
 *
 * $db = Database::connect(array(‘sqlite’, ‘test.sqlite’));
 * $db = new Database(array(‘sqlite’, ‘test.sqlite’));
 *
 * $link = new SQLiteDatabase(‘test.sqlite’);
 * $db = Database::connect($link);
 * $db = new Database($link);
 *
 * $link = sqlite_open(‘test.sqlite’);
 * $db = Database::connect($link);
 * $db = new Database($link);
 *
 * // sqlite3
 * $db = Database::connect(‘sqlite3’, ‘test.sqlite3’);
 * $db = new Database(‘sqlite3’, ‘test.sqlite3’);
 *
 * $db = Database::connect(array(‘sqlite3’, ‘test.sqlite3’));
 * $db = new Database(array(‘sqlite3’, ‘test.sqlite3’));
 *
 * $link = new SQLite3(‘test.sqlite3’);
 * $db = Database::connect($link);
 * $db = new Database($link);
 *
 * ?>
 * ```
 * use the $instance variable
 *
 * ```php5
 * <?php
 * require_once ‘Database.php’;
 *
 * // set the Database::$instance
 * Database::$instance = Database::connect(‘mysql’, ‘localhost’, ‘root’, ‘root’, ‘test’);
 *
 * //in some function, u can use them like this:
 *
 * print_r(test());
 *
 * function test() {
 * $sql = “SELECT * FROM test WHERE id = ?”;
 * $data = Database::$instance→getRow($sql, 1);
 * return $data;
 *
 * }
 * ?>
 * ```
 * demo code:
 *
 * ```php5
 * <?php
 * // origin sql
 * $sql = “SELECT * FROM test_table WHERE id = ‘$id’ AND name = ‘$name’”;
 * $result = $db→getAll($sql);
 *
 * // if there are variables in sql, you can do it like this, and don’t need to process the variables. : )
 * $sql = “SELECT * FROM test_table WHERE id = ? AND name = ?”;
 * $result = $db→getAll($sql, $id, $name);
 *
 * // you can allso use:
 * $sql = “SELECT * FROM test_table WHERE id = ? AND name = ?”;
 * $result = $db→getAll($sql, array($id, $name));
 *
 * // it allso support:
 * $sql = “SELECT * FROM test_table WHERE id = :id AND name = :name”;
 * $result = $db→getAll($sql, array(‘name’=>$name, ‘id’=>$id));
 * ?>
 * ```
 * database methods:
 *
 * ```php5
 * <?php
 * public function getRow();
 * // get a row result
 * public function getCol();
 * // get a col result
 * public function getOne();
 * // get a column value
 * public function getAll();
 * // get all results
 * public function exec();
 * // execute a sql
 * public function lastInsertId();
 * // get the id of the last inserted row or sequence value
 * public function getDriver();
 * // get the origin link or object of the database driver
 * public function query();
 * // execute a sql and returns a statement object
 * public function fetch($query);
 * // fetch a result whith the statement object from query
 * ?>
 * ```
 * the class just do the connecting work at initialization. you can exec ‘the initialization sql’ after connect like this, sure, it is lazy-executing just like the connecting.
 *
 * ```php5
 * <?php
 * //mysql
 * $db = Database::connect(‘mysql’, ‘localhost’, ‘root’, ‘root’, ‘test’);
 * $db→initialization = array(
 * ‘SET character_set_connection=utf8, character_set_results=utf8, character_set_client=binary’,
 * “SET sql_mode=’’”
 * );
 * // it’s same as above
 * $db→setConfig(‘initialization’, array(
 * ‘SET character_set_connection=utf8, character_set_results=utf8, character_set_client=binary’,
 * “SET sql_mode=’’”
 * ));
 * ?>
 * ```
 * use table-prefix
 *
 * ```php5
 * <?php
 * //mysql
 * $db = Database::connect(‘mysql’, ‘localhost’, ‘root’, ‘root’, ‘test’);
 *
 * $db→setConfig(‘tablePreFix’, ‘db_’);
 * // or set multi table-prefix
 * $db→setConfig(‘tablePreFix’, array(
 * ‘db1_’=>array(‘table1’, ‘table2’),
 * ‘db2_’=> ‘*’ // means the other tables use ‘db2_’ prefix
 * ));
 *
 * // and u can get the table name by getTable function
 *
 * $table_name = $db→getTable(‘table1’); // db1_table1
 *
 * // and the sql will be auto replaced the table name with ‘tablePreFix’ by dafault if you use ‘{{tablename}}’ in sql
 *
 * $sql = “SELECT * FROM {{table1}}”;
 * // SELECT * FROM db1_table1
 * $result = $db→getAll($sql);
 *
 * // and you can disable the auto-replace by using:
 *
 * $db→setConfig(‘replaceTableName’, false);
 *
 * ?>
 */
class Database
{
    public static $sql = array();
    private static $connections = array();
    public static $instance = null;
    public static $debug = false;
    private $driver = null;

    const NUM   = 0;
    const ASSOC = 1;
    const BOTH  = 2;

    const VERSION = '20140728';

    function __get($name)
    {
        return $this->driver->$name;
    }

    function __set($name, $value)
    {
        $this->driver->$name = $value;
    }

    function __call($fun, $params = array())
    {
        return call_user_func_array(array($this->driver, $fun), $params);
    }

    function __construct()
    {
        $params = func_get_args();

        if (count($params) == 1) {
            $params = $params[0];
        }

        list(, $sp) = self::getParamHash($params);

        $this->driver = self::getDriver($params, $sp);
    }

    private static function getDriver($params, $sp)
    {
        if (is_array($params)) {
            $driver = array_shift($params);
        } elseif (strpos($params, '://')) { // dsn
            if (!$dsn = parse_url($params)) {
                throw new DatabaseException("cant detect the dsn: {$params}");
            }
            if (!isset($dsn['scheme'])) {
                throw new DatabaseException("cant detect the driver: {$params}");
            }
            $driver = $dsn['scheme'];
            $params = array();

            $params[0] = isset($dsn['host']) ? $dsn['host'] : '';
            $params[1] = isset($dsn['user']) ? $dsn['user'] : '';
            $params[2] = isset($dsn['pass']) ? $dsn['pass'] : '';
            $params[3] = isset($dsn['path']) ? ltrim($dsn['path'], '/') : '';

            if ($driver == 'mysql') {
                isset($dsn['port']) && $params[0] .= ":{$dsn['port']}";
            } elseif ($driver == 'mysqli') {
                isset($dsn['port']) && $params[4] = $dsn['port'];
            } else {
                throw new DatabaseException("not support dsn driver: {$driver}");
            }

        } elseif (preg_match('/type \((\w+)|object\((\w+)\)/', $sp, $driver)) {
            $driver = strtolower(array_pop($driver));
            if ($driver == 'sqlitedatabase') {
                $driver = 'sqlite';
            }
        } else {
            throw new DatabaseException("cant auto detect the database driver");
        }

        //todo 这个其实可以使用 autoload 以后在修改吧。
        Importer::importFileByFullPath(
                dirname(__FILE__) . '/db/DatabaseAbstract.class.php'
        );
        Importer::importFileByFullPath(
                dirname(__FILE__) . '/db/DatabaseException.class.php'
        );
        Importer::importFileByFullPath(
                dirname(__FILE__) . '/db/DatabaseWrapper.class.php'
        );
        Importer::importFileByFullPath(
                dirname(__FILE__) . '/db/Driver/'.$driver.'Wrapper.class.php'
        );

        $class = $driver . 'Wrapper';

        return new $class($params);
    }

    private static function getParamHash($params)
    {
        // mabe the param is object, so use var_dump
        ob_start();
        var_dump($params);
        $sp  = ob_get_clean();
        $key = sha1($sp);
        // $key = md5(serialize($params));

        return array($key, $sp);
    }

    public static function connect()
    {
        $params = func_get_args();

        if (count($params) == 1) {
            $params = $params[0];
        }

        list($key, $sp) = self::getParamHash($params);

        if (!isset(self::$connections[$key])) {
            self::$connections[$key] = self::getDriver($params, $sp);
        }

        return self::$connections[$key];
    }
}