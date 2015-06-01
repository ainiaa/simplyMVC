<?php
/***
 * 框架入口文件
 * @date   2013-01-30
 * @author Jeff Liu
 * 设置编码为UTF-8 防止因为编码问题导致显示界面错乱
 */
header('Content-Type:text/html;charset=utf-8');

include dirname(__FILE__) . '/config/runtimeConst.inc.php';
include CORE_PATH . '/Importer.class.php';

//Importer::importFileByFullPath(ROOT_PATH . '/core/helper/SmvcDebugHelper.class.php');

class SimpleMVC
{

    private static $frameFileAllInOne;

    /**
     * @author Jeff Liu
     */
    public static function startup()
    {
        self::init();

        Dispatcher::dispatch();
    }

    /**
     * @return array
     */
    public static function getBaseFileList()
    {
        return include CORE_PATH . '/config/frameworkFileList.inc.php';
    }

    /**
     * 将core文件写入到一个文件里面 这样纸需要加载一次就行了。
     */
    public static function createBaseFileCache()
    {

        if (C('useAllInOneCache', false) === true) {
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

            if (!is_dir(dirname(self::$frameFileAllInOne))) {//如果 public/tmp目录不存在的会报warning错误
                mkdir(dirname(self::$frameFileAllInOne) . '/', 0777, true);
            }
            file_put_contents(self::$frameFileAllInOne, $content);
        }
    }

    /**
     * 初始化系统
     * @author Jeff Liu
     */
    private static function init()
    {
        self::$frameFileAllInOne = ROOT_PATH . '/public/tmp/~~core.php';

        self::initAutoLoad();

        //加载框架文件
        self::loadFramewrok();

        // 设定错误和异常处理
        self::initExceptionHandle();

        //加载所有的配置文件
        self::initConf();

        //设置自动加载系列
        Importer::initAutoLoad();

        //初始化session
        self::initSession();
    }

    /**
     *
     */
    public static function initExceptionHandle()
    {
        ExceptionHandle::init();
    }

    /**
     * 加载必须配置文件
     */
    private static function initConf()
    {
        SmvcConf::init(CONF_PATH, 'inc.php');
    }

    /**
     * 初始化框架文件（加载）
     * @author Jeff Liu
     */
    private static function loadFramewrok()
    {
        if (file_exists(self::$frameFileAllInOne)) {
            Importer::importFileByFullPath(self::$frameFileAllInOne);
        } else if (method_exists('Importer', 'loadFramewrok')) {
            Importer::loadFramewrok();
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
    }

    /**
     * 实现自动加载功能
     */
    public static function initAutoLoad()
    {
        if (function_exists('spl_autoload_register')) {
            spl_autoload_register(array('Importer', 'autoLoad'));
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
        if (C('session.auto_initialize')) {
            Session::instance(C('session.driver'));
        }
    }

    /**
     * 判断当前请求是否为ajax请求
     *
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