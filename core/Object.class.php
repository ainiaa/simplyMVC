<?php

/**
 *    所有类的基础类
 *
 * @author    Garbin
 * @usage     none
 */
class Object
{

    var $_errors = array();
    var $_errnum = 0;

    private static $i = 0;
    private static $fl = null;

    function __construct()
    {
        $this->Object();
    }


    function Object()
    {
    }

    /**
     * +----------------------------------------------------------
     * 自动变量设置
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     *
     * @param $name   属性名称
     * @param $value  属性值
    +----------------------------------------------------------
     */
    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        }
    }

    /**
     * +----------------------------------------------------------
     * 自动变量获取
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     *
     * @param $name 属性名称
    +----------------------------------------------------------
     *
     * @return mixed
    +----------------------------------------------------------
     */
    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        } else {
            return false;
        }
    }


    /**
     * 获取调用当前方法的方法的所在文件位置，行数
     * @author zhoubin
     *
     * @param unknown_type $c 查找层级，不填为第一子方法
     */
    public static function get_called_class($c = 2)
    {
        $bt = debug_backtrace();
        //使用call_user_func或call_user_func_array函数调用类方法，处理如下
        if (array_key_exists(3, $bt) && array_key_exists('function', $bt[3]) && in_array(
                        $bt[3]['function'],
                        array('call_user_func', 'call_user_func_array')
                )
        ) {
            //如果参数是数组
            if (is_array($bt[3]['args'][0])) {
                $toret = $bt[3]['args'][0][0];
                return $toret;
            } else {
                if (is_string($bt[3]['args'][0])) {
                    //如果参数是字符串
                    //如果是字符串且字符串中包含::符号，则认为是正确的参数类型，计算并返回类名
                    if (false !== strpos($bt[3]['args'][0], '::')) {
                        $toret = explode('::', $bt[3]['args'][0]);
                        return $toret[0];
                    }
                }
            }
        }

        //使用正常途径调用类方法，如:A::make()
        if (self::$fl == $bt[$c - 1]['file'] . $bt[$c - 1]['line']) {
            self::$i++;
        } else {
            self::$i = 0;
            self::$fl = $bt[$c - 1]['file'] . ' Line:[' . $bt[$c - 1]['line'] . ']';

        }
        //$lines = file($bt[$c]['file']);
        //preg_match_all('/([a-zA-Z0-9\_]+)::'.$bt[$c]['function'].'/',$lines[$bt[$c]['line']-1],$matches);
        //return $matches[1][self::$i];
        return self::$fl;
    }


    /**
     *    触发错误
     *
     * @author    Garbin
     *
     * @param     string $errmsg
     *
     * @return    void
     */
    function _error($msg, $obj = '')
    {
        if (is_array($msg)) {
            $this->_errors = array_merge($this->_errors, $msg);
            $this->_errnum += count($msg);
        } else {
            $this->_errors[] = compact('msg', 'obj');
            $this->_errnum++;
        }
    }

    /**
     *    检查是否存在错误
     *
     * @author    Garbin
     * @return    int
     */
    function has_error()
    {
        return $this->_errnum;
    }

    /**
     *    获取错误列表
     *
     * @author    Garbin
     * @return    array
     */
    function get_error()
    {
        return $this->_errors;
    }
}