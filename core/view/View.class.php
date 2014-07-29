<?php

class View
{
    public $tpl_vars = array();

    /**
     *  模板引擎实例
     * @var Object
     */
    private $engine = null;

    /**
     * @var array
     */
    public $viewEngineConfig = array( //视图engine相关配置
            'caching'         => false, //是否使用缓存，项目在调试期间，不建议启用缓存
            'template_dir'    => '@/templates', //设置模板目录
            'compile_dir'     => '@/templates_c', //设置编译目录
            'cache_dir'       => '@/smarty_cache', //缓存文件夹
            'cache_lifetime'  => 3600, // 缓存更新时间, 默认 3600 秒
            'force_compile'   => false,
            'left_delimiter'  => '<{', // smarty左限定符
            'right_delimiter' => '}>', // smarty右限定符
            'auto_literal'    => true, // Smarty3新特性
    );

    function __construct()
    {
        $this->init();
    }

    public function setEngine($engine)
    {
        if (C('viewEnginePath')) {
            Importer::importFileByFullPath(C('viewEnginePath'));
        }
        $this->engine = new $engine();
    }

    /**
     * @return null|Object
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     *
     * TODO
     * 初始化视图
     */
    public function  init()
    {
        $engine = C('viewEngine');
        $this->setEngine($engine);
        if (C('viewEngineConf')) {
            $this->viewEngineConfig = C('viewEngineConf');
        }

        $engine_vars = get_class_vars(get_class($this->engine));
        $groupName   = Router::getGroup($_REQUEST);
        $moduleName  = Router::getModule($_REQUEST);

        foreach ((array)$this->viewEngineConfig as $key => $value) {
            $value = str_replace('@', ROOT_PATH . '/' . $groupName . '/' . $moduleName, $value);
            if (isset($engine_vars[$key])) {
                $this->engine->{$key} = $value;
            } elseif ('Smarty' === $engine && 'template_dir' == $key) { //@see http://www.smarty.net/docs/zh_CN/variable.template.dir.tpl   自从smarty 3.1 之后 在Smarty 3.1之后，$template_dir属性不能直接访问，需使用 getTemplateDir()， setTemplateDir() 和 addTemplateDir()来进行存取。
                $this->engine->setTemplateDir($value);
            } elseif ('Smarty' === $engine && 'compile_dir' == $key) { //@see http://www.smarty.net/docs/zh_CN/variable.compile.dir.tpl   在Smarty 3.1之后，$cache_dir属性不能直接访问，需使用getCompileDir() 和 setCompileDir() 来进行存取。
                $this->engine->setCompileDir($value);
            }
        }
    }

    public function assign($var, $value)
    {
        $this->engine->assign($var, $value);
    }

    public function fetch($file)
    {
        return $this->engine->fetch($file);
    }

    public function get($var = null)
    {
        return $this->engine->get($var);
    }

    public function display($file)
    {
        return $this->engine->display($file);
    }

    public function __call($method, $args)
    {
        return $this->engine->$method($args);
    }
}
