<?php

/**
 * 直接使用php 作为模板
 * Class SmvcPHPTemplate
 */
class SmvcPHPTemplate
{
    public $tpl_vars = array();

    private $template_dir; //设置模板目录

    private $caching; //是否使用缓存，项目在调试期间，不建议启用缓存

    private $cache_dir; //缓存文件夹

    private $cache_lifetime; // 缓存更新时间, 默认 3600 秒

    private $force_compile; //是否需要强制编译



    function __construct()
    {
    }

    /**
     * @param $key
     * @param $value
     */
    public function assign($key, $value)
    {
        if (is_scalar($key)) {
            $this->tpl_vars[$key] = $value;
        } else if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->tpl_vars[$k] = $v;
            }
        }
    }

    /**
     * todo
     *
     * @param $file
     */
    public function fetch($file)
    {
        extract($this->tpl_vars);
        ob_start();
        include $this->template_dir . $file;
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }

    /**
     * @param null $key
     *
     * @return null
     */
    public function get($key = null)
    {
        if (isset($this->tpl_vars[$key])) {
            return $this->tpl_vars[$key];
        } else {
            return null;
        }
    }

    /**
     *
     * @param $file
     *
     * @return mixed
     */
    public function display($file)
    {
        echo $this->fetch($file);
    }

    /**
     * @param $method
     * @param $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        return $this->$method($args);
    }
}
