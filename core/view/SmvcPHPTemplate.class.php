<?php

/**
 * 直接使用 PHP 作为模板
 * Class SmvcPHPTemplate
 * @author Jeff Liu
 */
class SmvcPHPTemplate
{
    public $tplVars =[];

    private $templateDir; //设置模板目录

    /**
     * @param $key
     * @param $value
     */
    public function assign($key, $value)
    {
        if (is_scalar($key)) {
            $this->tplVars[$key] = $value;
        } else if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->tplVars[$k] = $v;
            }
        }
    }

    /**
     * todo
     *
     * @param $file
     *
     * @return string
     */
    public function fetch($file)
    {
        extract($this->tplVars);
        ob_start();
        include $this->templateDir . $file;
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
        if (isset($this->tplVars[$key])) {
            return $this->tplVars[$key];
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
