<?php
/***
 * 框架入口文件
 * @date   2013-01-30
 * @author Jeff Liu
 * 设置编码为UTF-8 防止因为编码问题导致显示界面错乱
 */
header('Content-Type:text/html;charset=utf-8');

include dirname(__FILE__) . '/config/runtimeConst.php';

// 为了方便导入第三方类库 设置Vendor目录到include_path
set_include_path(get_include_path() . PATH_SEPARATOR . VENDOR_PATH);

include CORE_PATH . '/Importer.class.php';

Importer::importFileByFullPath(ROOT_PATH . '/core/helper/SmvcDebugHelper.class.php');

/**
 * 实现自动加载功能
 */
if (function_exists('spl_autoload_register')) {
    spl_autoload_register(array('Importer', 'autoLoad'));
} else {
    function __autoload($sClassName)
    {
        Importer::autoLoad($sClassName);
    }
}


class SimpleMVC
{


    public function __construct()
    {
        /**
         * 初始化框架
         */
        self::init(); //调用静态方法 不执行 该构造方法
    }


    /**
     * @author Jeff Liu
     */
    public static function startup()
    {
        self::init();

        Dispatcher::dispatch();
    }

    public static function getBaseFileList()
    {
        return array(
                CORE_PATH . '/Router.class.php',
                CORE_PATH . '/Factory.class.php',
                CORE_PATH . '/Dispatcher.class.php',
                CORE_PATH . '/Object.class.php',
                CORE_PATH . '/controller/Base.class.php',
                CORE_PATH . '/dao/Base.class.php',
                CORE_PATH . '/service/Base.class.php',
                CORE_PATH . '/view/View.class.php',
                CORE_PATH . '/SmvcConf.class.php',
                CORE_PATH . '/functions.class.php',
                VENDOR_PATH . '/FirePHP.class.php',
                VENDOR_PATH . '/Medoo/medoo.php',
        );
    }

    /**
     * 将core文件写入到一个文件里面 这样纸需要加载一次就行了。
     */
    public static function createBaseFileCache()
    {
        return false; //todo 暂时屏蔽
        $content      = '<?php ';
        $baseFileList = self::getBaseFileList();
        foreach ($baseFileList as $file) {
            $currentContent = php_strip_whitespace($file);
            $currentContent = trim(substr($currentContent, 5)); //去掉<?php
            if ('?>' == substr($currentContent, -2)) {
                $currentContent = substr($currentContent, 0, -2);
            }
            $content .= $currentContent;
        }
        file_put_contents(ROOT_PATH . '/public/tmp/~~core.php', $content);
    }

    /**
     * 初始化系统
     * @author Jeff Liu
     */
    private static function init()
    {
        if (file_exists(ROOT_PATH . '/public/tmp/~~core.php')) {
            Importer::importFileByFullPath(ROOT_PATH . '/public/tmp/~~core.php');
        } else if (method_exists('Importer', 'importBaseFiles')) {
            Importer::importBaseFiles();
            self::createBaseFileCache();
        } else {
            self::createBaseFileCache();
            $baseFileList = self::getBaseFileList();
            if (is_array($baseFileList)) {
                foreach ($baseFileList as $file) {
                    Importer::importFileByFullPath($file);
                }
            }
        }

        $isDebugMode = C('smvcDebug');
        if ($isDebugMode) {
            error_reporting(E_ALL);
            ini_set('display_errors', 'on');
        } else {
            error_reporting(0);
            ini_set('display_errors', 'off');
        }


        // 设定错误和异常处理
        register_shutdown_function(array('SimpleMVC', 'fatalError'));
        set_error_handler(array('SimpleMVC', 'appError'));
        set_exception_handler(array('SimpleMVC', 'appException'));


        //加载所有的配置文件
        SmvcConf::instance()->loadConfigFileList(CONF_PATH, 'inc.php');


        //设置加载路径
        $autoloadPath = C('autoLoadPath');
        if ($autoloadPath) {
            $autoloadPath = explode(',', $autoloadPath);
            $autoloadPath = implode(PATH_SEPARATOR, $autoloadPath);
            Importer::setIncludePath($autoloadPath);
        }

        // 定义当前请求的系统常量
        define('NOW_TIME', $_SERVER['REQUEST_TIME']);
        define('REQUEST_METHOD', $_SERVER['REQUEST_METHOD']);
        define('IS_GET', REQUEST_METHOD == 'GET' ? true : false);
        define('IS_POST', REQUEST_METHOD == 'POST' ? true : false);
        define('IS_PUT', REQUEST_METHOD == 'PUT' ? true : false);
        define('IS_DELETE', REQUEST_METHOD == 'DELETE' ? true : false);
        define('IS_AJAX', self::isAjax());

        self::initSession();
    }

    /**
     * 需要重新实现
     * 初始化session
     * @author Jeff Liu
     */
    public static function initSession()
    {
        Session::instance('db');
    }

    /**
     * @access public
     * @author Jeff Liu
     */
    public static function isAjax()
    {
        $isAjax = false;
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower(
                        $_SERVER['HTTP_X_REQUESTED_WITH']
                ) == 'xmlhttprequest'
        ) {
            $isAjax = true;
        } elseif (!empty($_POST[VAR_AJAX_SUBMIT])) {
            $isAjax = true;
        } elseif (!empty($_GET[VAR_AJAX_SUBMIT])) {
            $isAjax = true;
        }
        return $isAjax;
    }

    /**
     * 自定义异常处理
     * @access public
     *
     * @param mixed $e 异常对象
     */
    public static function appException($e)
    {
        echo $e->__toString();
    }

    /**
     * 自定义错误处理
     * @access public
     *
     * @param int    $errno   错误类型
     * @param string $errstr  错误信息
     * @param string $errfile 错误文件
     * @param int    $errline 错误行数
     *
     * @return void
     */
    public static function appError($errno, $errstr, $errfile, $errline)
    {
        switch ($errno) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                ob_end_clean();
                // 页面压缩输出支持
                if (defined('OUTPUT_ENCODE') && OUTPUT_ENCODE) {
                    $zlib = ini_get('zlib.output_compression');
                    if (empty($zlib)) {
                        ob_start('ob_gzhandler');
                    }
                }
                $errorStr = "$errstr " . $errfile . " 第 $errline 行.";
                if (defined('LOG_RECORD') && LOG_RECORD) {
                    //                Log::write("[$errno] " . $errorStr, Log::ERR);
                }
                function_exists('halt') ? halt($errorStr) : exit('ERROR:' . $errorStr); //todo halt方法不存在
                break;
            case E_STRICT:
            case E_USER_WARNING:
            case E_USER_NOTICE:
            default:
                $errorStr = "[$errno] $errstr " . $errfile . " 第 $errline 行.";
                echo $errorStr;
                break;
        }
    }

    // 致命错误捕获
    public static function fatalError()
    {
        if ($e = error_get_last()) {
            self::appError($e['type'], $e['message'], $e['file'], $e['line']);
        }
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

SimpleMVC::startup();