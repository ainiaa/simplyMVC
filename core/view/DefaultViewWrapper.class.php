<?php

class DefaultViewWrapper
{
    public $tpl_vars = [];

    /**
     *  模板引擎实例
     * @var Object
     */
    private $ViewWrapper = null;

    /**
     * @var array
     */
    public $viewEngineConfig = [ //视图engine相关配置
                                 'caching'         => false, //是否使用缓存，项目在调试期间，不建议启用缓存
                                 'template_dir'    => '@/templates', //设置模板目录
                                 'compile_dir'     => '@/templates_c', //设置编译目录
                                 'cache_dir'       => '@/smarty_cache', //缓存文件夹
                                 'cache_lifetime'  => 3600, // 缓存更新时间, 默认 3600 秒
                                 'force_compile'   => false,
                                 'left_delimiter'  => '<{', // smarty左限定符
                                 'right_delimiter' => '}>', // smarty右限定符
                                 'auto_literal'    => true, // Smarty3新特性
    ];


    public function __construct()
    {
        $this->init();
    }

    public function setViewWrapper($ViewWrapper)
    {
        if (C('viewEnginePath')) {
            Importer::importFileByFullPath(C('viewEnginePath'));
        }
        $this->ViewWrapper = new $ViewWrapper();
    }

    /**
     * @return null|Object
     */
    public function getViewWrapper()
    {
        return $this->ViewWrapper;
    }

    private function init()
    {
        $engine = C('viewEngine');
        $this->setViewWrapper($engine);
        if (C('viewEngineConf')) {
            $this->viewEngineConfig = C('viewEngineConf');
        }

        $engineVars = get_class_vars(get_class($this->ViewWrapper));
        $groupName  = Request::getGroup();
        $moduleName = Request::getModule();

        foreach ((array)$this->viewEngineConfig as $key => $value) {
            $value = str_replace('@', ROOT_DIR . $groupName . '/' . $moduleName, $value);
            if (isset($engineVars[$key])) {
                $this->ViewWrapper->{$key} = $value;
            } elseif ('Smarty' === $engine && 'template_dir' == $key) { //@see http://www.smarty.net/docs/zh_CN/variable.template.dir.tpl   自从smarty 3.1 之后 在Smarty 3.1之后，$template_dir属性不能直接访问，需使用 getTemplateDir()， setTemplateDir() 和 addTemplateDir()来进行存取。
                $this->ViewWrapper->setTemplateDir($value);
            } elseif ('Smarty' === $engine && 'compile_dir' == $key) { //@see http://www.smarty.net/docs/zh_CN/variable.compile.dir.tpl   在Smarty 3.1之后，$cache_dir属性不能直接访问，需使用getCompileDir() 和 setCompileDir() 来进行存取。
                $this->ViewWrapper->setCompileDir($value);
            }
        }
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
