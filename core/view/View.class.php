<?php

class View
{
    /**
     *  模板引擎实例
     * @var Object
     */
    private $ViewWrapper = null;

    private static $instance = null;

    /**
     * @return View
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new View();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $viewEngine = C('viewEngine');
        switch (strtolower($viewEngine)) {
            case 'twig':
                $this->ViewWrapper = new TwigViewWrapper();
                break;
            default:
                $this->ViewWrapper = new DefaultViewWrapper();
        }
    }

    /**
     * @return null|Object
     */
    public function getViewWrapper()
    {
        return $this->ViewWrapper;
    }

    /**
     * @param $var
     * @param $value
     *
     * @return mixed
     */
    public function assign($var, $value)
    {
        return $this->ViewWrapper->assign($var, $value);
    }

    /**
     * @param $file
     *
     * @return mixed
     */
    public function fetch($file)
    {
        return $this->ViewWrapper->fetch($file);
    }

    /**
     * @param null $var
     *
     * @return mixed
     */
    public function get($var = null)
    {
        return $this->ViewWrapper->get($var);
    }

    /**
     * @param $file
     *
     * @return mixed
     */
    public function display($file)
    {
        return $this->ViewWrapper->display($file);
    }

    /**
     * @param $method
     * @param $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        return $this->ViewWrapper->$method($args);
    }
}
