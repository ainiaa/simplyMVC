<?php

class TwigViewWrapper
{
    public $tplVars = [];

    /**
     * @var Twig_Environment
     */
    private $ViewEngine = null;

    /**
     * @var array
     */
    public $viewEngineConfig = [];

    public function __construct()
    {
        $this->init();
    }

    /**
     * @return Twig_Environment
     */
    public function getViewEngine()
    {
        return $this->ViewEngine;
    }

    private function init()
    {
        if (C('viewEngineConf')) {
            $this->viewEngineConfig = C('viewEngineConf');
        }

        $groupName   = Request::getGroup();
        $moduleName  = Request::getModule();
        $templateDir = isset($this->viewEngineConfig['template_dir']) ? $this->viewEngineConfig['template_dir'] : 'template';
        $templateDir = str_replace('@', ROOT_DIR . $groupName . '/' . $moduleName, $templateDir);
        $cache       = isset($this->viewEngineConfig['cache']) ? $this->viewEngineConfig['cache'] : false;
        if ($cache && is_string($cache)) {
            $cache = str_replace('@', ROOT_DIR . $groupName . '/' . $moduleName, $cache);

            $this->viewEngineConfig['cache'] = $cache;
        }
        $loader   = new Twig_Loader_Filesystem($templateDir, ROOT_DIR);
        $twig     = new Twig_Environment($loader, $this->viewEngineConfig);
        $twig->addExtension(new Project_Twig_Extension());
        $this->ViewEngine = $twig;
    }


    /**
     * @param $key
     * @param $value
     *
     * @return void
     */
    public function assign($key, $value = '')
    {
        if (is_scalar($key)) {
            $this->tplVars[$key] = $value;
        } else if (is_array($key)) {
            foreach ($key as $index => $item) {
                $this->tplVars[$index] = $item;
            }
        }
    }

    /**
     * @param null $var
     *
     * @return mixed
     */
    public function get($var = null)
    {
        if ($var) {
            return isset($this->tplVars[$var]) ? $this->tplVars[$var] : '';
        }
        return $this->tplVars;
    }

    /**
     * @param       $file
     * @param array $context
     */
    public function display($file, $context = [])
    {
        $this->ViewEngine->display($file, array_merge($this->tplVars, $context));
    }

    /**
     * @param       $file
     * @param array $context
     */
    public function render($file, $context = [])
    {
        $this->ViewEngine->render($file, array_merge($this->tplVars, $context));
    }

    /**
     * @param $method
     * @param $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        return $this->ViewEngine->$method($args);
    }
}
