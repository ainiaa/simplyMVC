<?php
/***
 * 框架入口文件
 * @date   2013-01-30
 * @author Jeff Liu
 * 设置编码为UTF-8 防止因为编码问题导致显示界面错乱
 */
header('Content-Type:text/html;charset=utf-8');

include dirname(__FILE__) . '/config/runtimeConst.php';
include CORE_PATH . '/Importer.class.php';

//Importer::importFileByFullPath(ROOT_PATH . '/core/helper/SmvcDebugHelper.class.php');

class SimpleMVC
{

    /**
     * @author Jeff Liu
     */
    public static function startup()
    {
        self::init();

        Dispatcher::dispatch();
    }

    /**
     * todo 这个可以放到一个 framework config文件里面
     * @return array
     */
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
                CORE_PATH . '/exception/ExceptionHandle.class.php',
                VENDOR_PATH . '/FirePHP.class.php',
                VENDOR_PATH . '/Medoo/medoo.php',

        );
    }

    /**
     * 将core文件写入到一个文件里面 这样纸需要加载一次就行了。
     */
    public static function createBaseFileCache()
    {
        return false; //todo 暂时屏蔽  使用这个的话 加载某些文件的时候 会出现问题
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
        //加载框架文件
        self::initFramewrok();

        self::initAutoLoad();

        // 设定错误和异常处理
        ExceptionHandle::init();

        //加载所有的配置文件
        self::initConf();

        //初始化session
        self::initSession();
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
    private static function initFramewrok()
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

        Importer::initAutoLoadConf();
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
     * todo 需要重新实现
     * 初始化session
     * @author Jeff Liu
     */
    public static function initSession()
    {
        //Session::instance('db');
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

    public static function value($value)
    {
        if ($value instanceof Closure) {
            return $value();
        } else {
            return $value;
        }
    }
}

//启动。。
SimpleMVC::startup();