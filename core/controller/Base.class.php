<?php

/**
 * @author Jeff Liu
 * 控制器基类 定义了 控制器的基本方法
 */
class BaseController extends SmvcObject
{
    /**
     * @var View
     */
    protected $view = null; //视图对象
    protected $visitor = null; //访问者对象


    public function __construct()
    {
        parent::__construct();
        //初始化 视图类, session 和 访问者
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
     * @author Jeff Liu
     *
     * @param string $file
     *
     * @return string
     */
    function fetch($file)
    {
        //初始化 视图类
        $this->initView();

        return $this->view->fetch($file);
    }

    /**
     * 获得 模版变量
     * @author Jeff Liu
     *
     * @param mixed $var
     *
     * @return mixed
     */
    function get($var = null)
    {
        //初始化 视图类
        $this->initView();

        return $this->view->get($var);
    }

    /**
     * 显示模版信息
     * @author Jeff Liu
     *
     * @param string $tpl
     *
     * @return mixed
     */
    function display($tpl = null)
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
     * @author Jeff Liu
     *
     * @param string $url
     * @param mixed  $data
     *
     * @return array
     */
    public static function getRequest($url, $data)
    {
        return self::request($url, $data, 'get');
    }

    /**
     * 发送post请求
     * @author Jeff Liu
     *
     * @param string $url
     * @param array  $data
     *
     * @return array
     */
    public static function postRequest($url, $data)
    {
        return self::request($url, $data, 'post');
    }

    /**
     * 请求公共处理逻辑
     * @author Jeff Liu
     *
     * @param string $url
     * @param array  $data
     * @param string $type
     *
     * @return array
     */
    protected static function request($url, $data, $type = 'get')
    {
        $lst = array();
        $ch  = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($type == 'post') {
            $params = '';
            if (empty($data)) {
                $params = http_build_query($data);
            }
            curl_setopt($ch, CURLOPT_POST, 1); //启用POST提交
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params); //启用POST提交
        }

        $lst['rst']  = curl_exec($ch);
        $lst['info'] = curl_getinfo($ch);
        curl_close($ch);

        return $lst;
    }


    /**
     *
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
}