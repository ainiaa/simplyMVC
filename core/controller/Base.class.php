<?php

/**
 * @author Jeff.Liu<jeff.liu.guo@gmail.com>
 * 控制器基类 定义了 控制器的基本方法
 */
class BaseController extends SmvcObject
{
    /**
     * @var View
     */
    protected $view = null; //视图对象

    /**
     * @var null
     */
    protected $visitor = null; //访问者对象


    public function __construct()
    {
        parent::__construct();

        $this->init();
    }

    /**
     * 将值设置到模版变量中
     *
     * @param mixed $var
     * @param mixed $value
     *
     * @return mixed
     */
    public function assign($var = null, $value = null)
    {
        //初始化 视图类
        $this->initView();

        return $this->view->assign($var, $value);
    }

    /**
     * 获得模版 替换之后的内容
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     *
     * @param string $file
     *
     * @return string
     */
    public function fetch($file)
    {
        //初始化 视图类
        $this->initView();

        return $this->view->fetch($file);
    }

    /**
     * 获得 模版变量
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     *
     * @param mixed $var
     *
     * @return mixed
     */
    public function get($var = null)
    {
        //初始化 视图类
        $this->initView();

        return $this->view->get($var);
    }

    /**
     * 显示模版信息
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     *
     * @param string $tpl
     *
     * @return mixed
     */
    public function display($tpl = null)
    {
        //初始化 视图类
        $this->initView();

        return $this->view->display($tpl);
    }

    /**
     * 为框架的正常运行进行必要的初始化工作
     */
    protected function init()
    {
        //初始化访问者
        $this->initVisitor();
    }

    /**
     * 初始化 view
     */
    protected function initView()
    {
        if (null === $this->view) {
            $this->view = View::getInstance();
        }
    }


    /**
     * 初始化访问者 TODO 具体怎么初始化Visitor 还需要实现
     * @author Jeff Liu
     */
    protected function initVisitor()
    {

    }


    /**
     * 发送get请求
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     *
     * @param string $url
     * @param mixed  $data
     *
     * @return array
     */
    public static function sendGet($url, $data)
    {
        return SmvcHttpHelper::sendGet($url, $data);
    }

    /**
     * 发送post请求
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     *
     * @param string $url
     * @param array  $data
     *
     * @return array
     */
    public static function sendPost($url, $data)
    {
        return SmvcHttpHelper::sendPost($url, $data);
    }


    /**
     * 跳转程序
     *
     * 应用程序的控制器类可以覆盖该函数以使用自定义的跳转程序
     *
     * @param string $url   需要前往的地址
     * @param int    $delay 延迟时间
     */
    public function jump($url, $delay = 0)
    {
        echo "<html><head><meta http-equiv='refresh' content='{$delay};url={$url}'></head><body></body></html>";
        exit;
    }

    /**
     *
     * 错误提示程序
     *
     * 应用程序的控制器类可以覆盖该函数以使用自定义的错误提示
     *
     * @param string $msg 错误提示需要的相关信息
     * @param string $url 跳转地址
     */
    public function error($msg, $url = '')
    {
        $url = empty($url) ? "window.history.back();" : "location.href=\"{$url}\";";
        echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script>function sptips(){alert(\"{$msg}\");{$url}}</script></head><body onload=\"sptips()\"></body></html>";
        exit;
    }

    /**
     *
     * 成功提示程序
     *
     * 应用程序的控制器类可以覆盖该函数以使用自定义的成功提示
     *
     * @param string $msg 成功提示需要的相关信息
     * @param string $url 跳转地址
     */
    public function success($msg, $url = '')
    {
        $url = empty($url) ? "window.history.back();" : "location.href=\"{$url}\";";
        echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script>function sptips(){alert(\"{$msg}\");{$url}}</script></head><body onload=\"sptips()\"></body></html>";
        exit;
    }

    /**
     * 魔术函数，获取赋值作为模板内变量
     */
    public function __set($name, $value)
    {
        return $this->view->assign($name, $value);
    }


    /**
     * 魔术函数，返回已赋值的变量值
     */
    public function __get($name)
    {
        return $this->view->get($name);
    }


    /**
     * TODO 需要重新实现
     */
    public function __call($name, $args)
    {
    }

    /**
     * 获取模板引擎实例
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * URL跳转
     *
     * @param string $url  跳转地址
     * @param int    $time 跳转延时(单位:秒)
     * @param string $msg  提示语
     */
    function redirect($url, $time = 0, $msg = '')
    {
        $url = str_replace(array("\n", "\r"), '', $url); // 多行URL地址支持
        if (empty($msg)) {
            $msg = "系统将在 {$time}秒 之后自动跳转到 {$url} ！";
        }
        if (headers_sent()) {
            $str = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
            if ($time != 0) {
                $str .= $msg;
            }
            exit($str);
        } else {
            if (0 === $time) {
                header("Location: " . $url);
            } else {
                header("Content-type: text/html; charset=utf-8");
                header("refresh:{$time};url={$url}");
                echo $msg;
            }
            exit;
        }
    }

    /**
     * @param        $error
     * @param string $tpl
     */
    public function displayError($error, $tpl = '')
    {
        if ($tpl) {
            $this->assign('error', $error);
            $this->display($tpl);
        } else {
            echo '<pre>';
            var_export($error);
            echo '<pre>';
            exit;
        }
    }
}