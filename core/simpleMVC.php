<?php
/***
 * @date   2013-01-30
 * @author Jeff.Liu<jeff.liu.guo@gmail.com>
 */
header('Content-Type:text/html;charset=utf-8');

include CORE_DIR . '/config/runtimeConst.php';
include CORE_DIR . 'Importer.class.php';

class SimpleMVC
{
    private static $frameFileAllInOne;

    /**
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     */
    public static function startup()
    {
        //初始化
        self::init();

        //解析url
        Router::route();

        Dispatcher::dispatch();
    }

    /**
     * @return array
     */
    public static function getBaseFileList()
    {
        return include CORE_DIR . 'config/frameworkFileList.php';
    }

    /**
     *
     * 将core文件写入到一个文件里面 这样纸需要加载一次就行了。
     *
     * @author Jeff Liu
     */
    public static function createBaseFileCache()
    {
        if (defined('USE_ALLINONE_CACHE') && USE_ALLINONE_CACHE) {
            $content      = '<?php ';
            $baseFileList = self::getBaseFileList();
            foreach ($baseFileList as $file) {
                $currentContent = php_strip_whitespace($file);
                $currentContent = trim(substr($currentContent, 5)); //去掉<?php
                if ('?>' === substr($currentContent, -2)) {
                    $currentContent = substr($currentContent, 0, -2);
                }
                $content .= $currentContent;
            }

            if (!is_dir(dirname(self::$frameFileAllInOne))) {//如果 public/tmp目录不存在的会报warning错误
                mkdir(dirname(self::$frameFileAllInOne) . '/', 0777, true);
            }
            file_put_contents(self::$frameFileAllInOne, $content, LOCK_EX);//防止在并发的时候 出现内容写入错乱的问题
        }
    }

    /**
     * 初始化框架
     *
     * @author Jeff Liu
     */
    private static function init()
    {
        self::$frameFileAllInOne = ROOT_DIR . 'public/tmp/~~core.php';

        //注册自动加载
        self::initAutoLoad();

        //加载框架文件
        self::loadFramewrok();

        // 设定错误和异常处理
        self::initExceptionHandler();

        //加载所有的配置文件
        self::initConf();

        //设置自动加载系列
        Importer::initAutoLoad();

        //初始化session
        self::initSession();
    }

    /**
     * 设置异常处理
     * @author Jeff Liu<jeff.liu.guo@gmail.com>
     */
    public static function initExceptionHandler()
    {
        ExceptionHandler::init();
    }

    /**
     * 加载必须配置文件
     * @author Jeff Liu<jeff.liu.guo@gmail.com>
     */
    private static function initConf()
    {
        SmvcConf::initEnv('env.php');
        SmvcConf::init(CONF_DIR, 'php');
    }

    /**
     * 初始化框架文件（加载）
     * @author Jeff Liu<jeff.liu.guo@gmail.com>
     */
    private static function loadFramewrok()
    {
        $needLoadFileOneByOne   = true;
        $needCreateAllInOneFile = false;
        if (defined('USE_ALLINONE_CACHE') && USE_ALLINONE_CACHE) {
            if (file_exists(self::$frameFileAllInOne) && is_readable(self::$frameFileAllInOne)) {
                $loadResult = Importer::importFileByFullPath(self::$frameFileAllInOne);
                if ($loadResult) {
                    $needLoadFileOneByOne = false;
                } else {
                    $needLoadFileOneByOne   = true;
                    $needCreateAllInOneFile = true;
                }
            }
        }
        if ($needLoadFileOneByOne) {
            $baseFileList = self::getBaseFileList();
            if (is_array($baseFileList)) {
                foreach ($baseFileList as $file) {
                    Importer::importFileByFullPath($file);
                }
            }
        }

        if ($needCreateAllInOneFile) {
            self::createBaseFileCache();
        }
    }

    /**
     * 实现自动加载功能
     */
    public static function initAutoLoad()
    {
        $composerLoadFile = ROOT_DIR . 'include/vendor/autoload.php';
        if (file_exists($composerLoadFile)) {
            include $composerLoadFile;
        }

        if (function_exists('spl_autoload_register')) {
            spl_autoload_register(['Importer', 'autoLoad']);
        } else {
            function __autoload($sClassName)
            {
                Importer::autoLoad($sClassName);
            }
        }
    }

    /**
     * 初始化session
     * @author Jeff Liu
     */
    public static function initSession()
    {
        $autoInitialize = C('session.auto_initialize');
        if ($autoInitialize) {
            Session::getInstance(C('session.driver'));
        }
    }

    /**
     * 判断当前请求是否为ajax请求
     *
     * @access public
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     */
    public static function isAjax()
    {
        $isAjax = false;
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower(
                        $_SERVER['HTTP_X_REQUESTED_WITH']
                ) == 'xmlhttprequest') {
            $isAjax = true;
        } elseif (isset($_POST[VAR_AJAX_SUBMIT]) && $_POST[VAR_AJAX_SUBMIT]) {
            $isAjax = true;
        } elseif (isset($_GET[VAR_AJAX_SUBMIT]) && $_GET[VAR_AJAX_SUBMIT]) {
            $isAjax = true;
        }

        return $isAjax;
    }

    public static function value($value)
    {
        if ($value instanceof Closure) {
            return $value();
        } else {
            return $value;
        }
    }

}

//启动框架
SimpleMVC::startup();