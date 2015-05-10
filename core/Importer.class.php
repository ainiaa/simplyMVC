<?php

/**
 *
 * 用于include文件
 * @author jeff liu
 * TODO 在加载非当前group，module下的controller的时候 还需要注意 对应model和dao初始化问题。。。
 */
class Importer
{
    private static $loadedFiles = array(); //已经加载的文件


    /**
     * 返回所有已经加载过的文件
     * @return array
     */
    public static function getLoadedFiles()
    {
        return Importer::$loadedFiles;
    }

    /**
     *
     * 加载制定的文件
     *
     * @param string $filePath
     * @param string $fileExt
     * @param string $rootPath
     */
    public static function importFile($filePath, $fileExt = 'php', $rootPath = INCLUDE_PATH)
    {
        $finalPath = $rootPath . '/' . preg_replace('|\.|', '/', $filePath) . '.' . $fileExt;
        if (is_file($finalPath)) {
            if (is_readable($finalPath)) {
                $file_hash = md5($finalPath);
                if (!isset(Importer::$loadedFiles[$file_hash])) {
                    include $finalPath;
                    Importer::$loadedFiles[$file_hash] = $finalPath;
                }
            } else {
                trigger_error('file_is_not_readable:' . $finalPath, E_USER_ERROR);
            }
        } else {
            trigger_error('file_no_exists : ' . $finalPath, E_USER_ERROR);
        }
    }

    /**
     *
     * @author jeff liu
     *
     * @param string $filePath
     *
     * @param bool   $showError
     *
     * @return bool
     */
    public static function importFileByFullPath($filePath, $showError = true)
    {
        $loadResult = false;
        if (is_file($filePath)) {
            if (is_readable($filePath)) {
                $fileHash = md5($filePath);
                if (!isset(Importer::$loadedFiles[$fileHash])) {
                    include $filePath;
                    self::$loadedFiles[$fileHash] = $filePath;
                }
                $loadResult = true;
            } else {
                if ($showError) {
                    trigger_error('file_is_not_readable' . $filePath, E_USER_ERROR);
                }
            }
        } else {
            if ($showError) {
                trigger_error('file_no_exists:' . $filePath, E_USER_ERROR);
            }
        }
        return $loadResult;
    }

    /**
     *
     * 加载配置文件
     *
     * @param string $configPath    config目录
     * @param string $configFileExt config文件的后缀
     *
     * @return array
     */
    public static function importConfigFile($configPath, $configFileExt = 'inc.php')
    {
        $finalResult = array();
        if (empty($configPath)) {
            $configPath = ROOT_PATH . '/config';
        }

        if (is_file($configPath)) { //如果传递过来的是一个具体的文件路径的话 直接调用importFileByFullPath方法
            $configFiles = array($configPath);
        } else {
            $configFiles = glob($configPath . '/' . '*.' . $configFileExt);
        }

        if (!empty($configFiles) && is_array($configFiles)) {
            foreach ($configFiles as $configFile) {
                if (is_file($configFile)) {
                    if (is_readable($configFile)) {
                        $fileHash = md5($configFile);
                        if (!isset(Importer::$loadedFiles[$fileHash])) { //还没有加载过当前config文件 加载当前cofnig文件
                            $currentConf = include $configFile;
                            if (is_array($currentConf)) {
                                $finalResult += $currentConf;
                            }
                            Importer::$loadedFiles[$fileHash] = $configFile;
                        }
                    } else {
                        trigger_error('file_is_not_readable' . $configFile, E_USER_ERROR);
                    }
                } else {
                    trigger_error('file_no_exists' . $configFile, E_USER_ERROR);
                }
            }
        }

        return $finalResult;
    }


    /**
     * 加载控制器文件
     *
     * @param string $controllerName
     * @param string $groupName
     * @param string $moduleName
     */
    public static function loadController($controllerName, $groupName = '@', $moduleName = '@')
    {
        if ('@' == $groupName) {
            $groupName = Router::getGroup();;
        }

        if ('@' == $moduleName) {
            $moduleName = Router::getModule();
        }
        $baseControllerFile = APP_PATH . '/' . $groupName . '/' . $groupName . '.controller.php';
        self::importFileByFullPath($baseControllerFile);

        $controllerFileName = $controllerName . '.controller.php';

        $controllerFile = APP_PATH . '/' . $groupName . '/' . $moduleName . '/controllers/' . $controllerFileName;
        $loadResult     = self::importFileByFullPath($controllerFile);

        if (!$loadResult) { //当前group module下加载controller失败
            $modules = self::getModuleList($groupName);
            foreach ($modules as $module) {
                $filePath = APP_PATH . '/' . $groupName . '/' . $module . '/controllers/' . $controllerFileName;
                $files    = self::getControllerListByGroupAndModule($groupName, $module);
                if (in_array($filePath, $files, true)) {
                    $loadResult = self::importFileByFullPath($controllerFile);
                    if ($loadResult) { //加载成功直接break
                        break;
                    }
                }
            }
            //当前group加载失败 获取所有的加载其他group下的controll 直到第一个加载成功为止
            if (!$loadResult) {
                $groupList = self::getGroupList();
                foreach ((array)$groupList as $group) {
                    $groupModules = self::getModuleList($group);
                    foreach ($groupModules as $groupModule) {
                        $filePath = APP_PATH . '/' . $group . '/' . $groupModule . '/controllers/' . $controllerFileName;
                        $files    = self::getControllerListByGroupAndModule($group, $groupModule);
                        if (in_array($filePath, $files, true)) {
                            $loadResult = self::importFileByFullPath($controllerFile);
                            if ($loadResult) { //加载成功直接break
                                break;
                            }
                        }
                    }
                }
            }
        }
    }


    /**
     * 加载对应的service文件
     *
     * @param string $serviceName
     * @param string $groupName
     * @param string $moduleName
     */
    public static function loadService($serviceName, $groupName = '@', $moduleName = '@')
    {
        if ('@' == $groupName) {
            $groupName = Router::getGroup();
        }

        if ('@' == $moduleName) {
            $moduleName = Router::getModule();
        }

        $serviceFileName = $serviceName . '.service.php';


        $serviceFile = APP_PATH . $groupName . '/' . $moduleName . '/services/' . ucfirst($serviceFileName);
        //echo $model_file,':',var_export(is_file($model_file));exit;
        $loadResult = self::importFileByFullPath($serviceFile);
        if (!$loadResult) { //当前group module下加载controller失败
            $modules = self::getModuleList($groupName);
            foreach ($modules as $module) {
                $filePath = APP_PATH . '/' . $groupName . '/' . $module . '/services/' . $serviceFileName;
                $files    = self::getServiceListByGroupAndModule($groupName, $module);
                if (in_array($filePath, $files, true)) {
                    $loadResult = self::importFileByFullPath($serviceFile);
                    if ($loadResult) { //加载成功直接break
                        break;
                    }
                }
            }
            //当前group加载失败 获取所有的加载其他group下的controll 直到第一个加载成功为止
            if (!$loadResult) {
                $groupList = self::getGroupList();
                foreach ((array)$groupList as $group) {
                    $groupModules = self::getModuleList($group);
                    foreach ($groupModules as $groupModule) {
                        $filePath = APP_PATH . '/' . $group . '/' . $groupModule . '/services/' . $serviceFileName;
                        $files    = self::getDAOListByGroupAndModule($group, $groupModule);
                        if (in_array($filePath, $files, true)) {
                            $loadResult = self::importFileByFullPath($serviceFile);
                            if ($loadResult) { //加载成功直接break
                                break;
                            }
                        }
                    }
                }
            }
        }
    }


    /**
     * 加载对应的helper文件
     *
     * @param string $helperName
     * @param string $groupName
     * @param string $moduleName
     */
    public static function loadHelper($helperName, $groupName = '@', $moduleName = '@')
    {
        if ('@' == $groupName) {
            $groupName = Router::getGroup();
        }

        if ('@' == $moduleName) {
            $moduleName = Router::getModule();
        }

        $helperFileName = $helperName . '.class.php';

        //首先加载core目录下的helper
        $helperFile = CORE_PATH . '/helper/' . $helperFileName;
        $loadResult = self::importFileByFullPath($helperFile, false);
        //        SmvcDebugHelper::instance()->debug(
        //                array(
        //                        'info'  => $helperFile,
        //                        'label' => '$helperFile ' . __METHOD__,
        //                        'level' => 'info'
        //                )
        //        );

        if (!$loadResult) {
            $helperFile = APP_PATH . '/' . $groupName . '/' . $moduleName . '/helper/' . $helperFileName;
            $loadResult = self::importFileByFullPath($helperFile, false);

            if (!$loadResult) { //当前group module下加载controller失败
                $modules = self::getModuleList($groupName);
                foreach ($modules as $module) {
                    $filePath = APP_PATH . '/' . $groupName . '/' . $module . '/helper/' . $helperFileName;
                    $files    = self::getServiceListByGroupAndModule($groupName, $module);
                    if (in_array($filePath, $files, true)) {
                        $loadResult = self::importFileByFullPath($helperFile);
                        if ($loadResult) { //加载成功直接break
                            break;
                        }
                    }
                }
                //当前group加载失败 获取所有的加载其他group下的controll 直到第一个加载成功为止
                if (!$loadResult) {
                    $groupList = self::getGroupList();
                    foreach ((array)$groupList as $group) {
                        $groupModules = self::getModuleList($group);
                        //                        SmvcDebugHelper::instance()->debug(
                        //                                array(
                        //                                        'info'  => $groupModules,
                        //                                        'label' => '$groupModules ' . __METHOD__,
                        //                                        'level' => 'info'
                        //                                )
                        //                        );
                        foreach ($groupModules as $groupModule) {
                            $filePath = APP_PATH . '/' . $group . '/' . $groupModule . '/helpers/' . $helperFileName;
                            $files    = self::getDAOListByGroupAndModule($group, $groupModule);
                            if (in_array($filePath, $files, true)) {
                                $loadResult = self::importFileByFullPath($helperFile);
                                if ($loadResult) { //加载成功直接break
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }

    }

    /**
     * 加载对应的dao文件
     *
     * @param string $daoName
     * @param string $groupName
     * @param string $moduleName
     */
    public static function loadDAO($daoName, $groupName = '@', $moduleName = '@')
    {
        if ('@' == $groupName) {
            $groupName = Router::getGroup();
        }

        if ('@' == $moduleName) {
            $moduleName = Router::getModule();
        }

        $daoFileName = $daoName . '.dao.php';

        $daoFile    = APP_PATH . '/' . $groupName . '/' . $moduleName . '/daos/' . $daoFileName;
        $loadResult = self::importFileByFullPath($daoFile);

        if (!$loadResult) { //当前group module下加载controller失败
            $modules = self::getModuleList($groupName);
            foreach ($modules as $module) {
                $filePath = APP_PATH . '/' . $groupName . '/' . $module . '/daos/' . $daoFileName;
                $files    = self::getDAOListByGroupAndModule($groupName, $module);
                if (in_array($filePath, $files, true)) {
                    $loadResult = self::importFileByFullPath($daoFile);
                    if ($loadResult) { //加载成功直接break
                        break;
                    }
                }
            }
            //当前group加载失败 获取所有的加载其他group下的controll 直到第一个加载成功为止
            if (!$loadResult) {
                $groupList = self::getGroupList();
                foreach ((array)$groupList as $group) {
                    $groupModules = self::getModuleList($group);
                    foreach ($groupModules as $groupModule) {
                        $filePath = APP_PATH . '/' . $group . '/' . $groupModule . '/daos/' . $daoFileName;
                        $files    = self::getDAOListByGroupAndModule($group, $groupModule);
                        if (in_array($filePath, $files, true)) {
                            $loadResult = self::importFileByFullPath($daoFile);
                            if ($loadResult) { //加载成功直接break
                                break;
                            }
                        }
                    }
                }
            }
        }
    }


    /**
     * 根据给定的group和module获得controller列表
     *
     * @param string $groupName
     * @param string $moduleName
     *
     * @return array
     */
    public static function getControllerListByGroupAndModule($groupName, $moduleName)
    {
        $appFileStruct  = self::getAppFileStruct();
        $files          = isset($appFileStruct[$groupName][$moduleName]) ? $appFileStruct[$groupName][$moduleName] : array();
        $controllerList = isset($files['controllers']) ? $files['controllers'] : array();

        return $controllerList;
    }

    /**
     * 根据给定的group和module获得service列表
     *
     * @param string $groupName
     * @param string $moduleName
     *
     * @return array
     */
    public static function getServiceListByGroupAndModule($groupName, $moduleName)
    {
        $appFileStruct = self::getAppFileStruct();
        $files         = isset($appFileStruct[$groupName][$moduleName]) ? $appFileStruct[$groupName][$moduleName] : array();
        $serviceList   = isset($files['services']) ? $files['services'] : array();

        return $serviceList;
    }

    /**
     * 根据给定的group和module获得dao列表
     *
     * @param string $groupName
     * @param string $moduleName
     *
     * @return array
     */
    public static function getDAOListByGroupAndModule($groupName, $moduleName)
    {
        $appFileStruct = self::getAppFileStruct();
        $files         = isset($appFileStruct[$groupName][$moduleName]) ? $appFileStruct[$groupName][$moduleName] : array();
        $daoList       = isset($files['daos']) ? $files['daos'] : array();

        return $daoList;
    }

    /**
     * 获得目录下的目录结构 使用一维数组的方式展示
     *
     * @param string $path
     *
     * @return array
     */
    public static function getFileStructBypath($path)
    {
        $tree = array();
        if ('/' === substr($path, -1)) {
            $globPattern = $path . '*';
        } else {
            $globPattern = $path . '/*';
        }
        foreach (glob($globPattern) as $single) {
            if (is_dir($single)) {
                $tree = array_merge($tree, self::getFileStructBypath($single));
            } else {
                $tree[] = $single;
            }
        }
        return $tree;
    }

    /**
     * 获得分组列表
     * @return array
     */
    public static function getGroupList()
    {
        $groupList = explode(',', C('APP_GROUP_LIST'));
        return $groupList;
    }

    /**
     * 获得module列表
     *
     * @param string $groupName
     *
     * @return array
     */
    public static function getModuleList($groupName = '')
    {
        $finalData = array();
        $groupList = self::getGroupList();
        foreach ($groupList as $group) {
            $groupBasePath = APP_PATH . $group . '/';
            $files         = glob($groupBasePath . '*');
            foreach ((array)$files as $file) {
                if (is_dir($file)) {
                    $finalData[$group][] = basename($file);
                }
            }
        }

        if (!empty($groupName) && isset($finalData[$groupName])) {
            $finalData = $finalData[$groupName];
        }

        return $finalData;
    }

    /**
     * 获得app所有文件列表
     * @return mixed
     */
    public static function getAppFileStruct()
    {
        static $finalFileStruct = array();
        if (C('APP_GROUP_LIST')) {
            $appGroupList = explode(',', C('APP_GROUP_LIST'));
            foreach ($appGroupList as $groupName) {
                $groupBasePath                          = APP_PATH . $groupName . '/';
                $files                                  = glob($groupBasePath . '*');
                $finalFileStruct[$groupName]['_FILES_'] = $files;
                foreach ($files as $file) {
                    if (is_dir($file)) { //当期group下的一个module
                        $moduleName     = basename($file);
                        $moduleBasePath = $groupBasePath . $moduleName . '/';
                        $moduleFiles    = glob($moduleBasePath . '*');

                        $finalFileStruct[$groupName][$moduleName]['_FILES_'] = $moduleFiles;

                        foreach ($moduleFiles as $moduleFile) {
                            $fileName                                            = basename($moduleFile);
                            $finalFileStruct[$groupName][$moduleName][$fileName] = self::getFileStructBypath(
                                    $moduleFile
                            );
                        }

                    }
                }
            }
        }

        return $finalFileStruct;
    }

    /**
     * 自动加载
     * @author jeff liu
     *
     * @param String $className 需要加载的类名
     */
    public static function autoLoad($className)
    {
        //        echo '$className:', $className, '<br />';
        if ('Controller' == substr($className, -10)) { //controller类
            $controllerName = substr_replace($className, '', -10);
            self::loadController(lcfirst($controllerName));
        } else if ('DAO' == substr($className, -3)) { //dao类
            $daoName = substr_replace($className, '', -3);
            self::loadDAO(lcfirst($daoName));
        } else if ('Service' == substr($className, -7)) { //service类
            $serviceName = substr_replace($className, '', -7);
            self::loadService(lcfirst($serviceName));
        } else if ('Helper' == substr($className, -6)) { //辅助类
            //TODO 需要考虑 group moduel 里面的helper？？？  分组下面需要有吗？？？
            //            $helperName = substr_replace($className, '', -6);
            self::loadHelper(lcfirst($className));
        } else {

            //尝试获取fileMapping
            $loadResult = self::loadMappingFile($className);
            if (!$loadResult) {
                // 根据自动加载路径设置进行尝试搜索
                $includePath    = get_include_path();
                $paths          = explode(PATH_SEPARATOR, $includePath);
                $fileExtensions = array('.class.php', '.php');
                foreach ($paths as $path) {
                    // TODO 如果加载类成功则返回 这个需要完善。。。 文件名后缀需要整理
                    foreach ($fileExtensions as $fileExtension) {
                        $fullFilePath = $path . '/' . $className . $fileExtension;
                        //                        SmvcDebugHelper::instance()->debug(
                        //                                array(
                        //                                        'info'  => array('className' => $className, 'path' => $fullFilePath),
                        //                                        'label' => '$class_name',
                        //                                        'level' => 'warn'
                        //                                )
                        //                        );
                        if (self::importFileByFullPath($fullFilePath, false)) {
                            break;
                        }
                    }
                }
            }
        }

        //        SmvcDebugHelper::instance()->debug(
        //                array('info' => $className, 'label' => '$class_name', 'level' => 'warn')
        //        );
    }

    /**
     *
     * @author jeff liu
     *
     * @param string $filePath
     *
     * @return array|mixed
     */
    public static function loadConfigFile($filePath)
    {
        $loadResult = false;
        if (is_file($filePath)) {
            if (is_readable($filePath)) {
                $fileHash = md5($filePath);
                if (!isset(Importer::$loadedFiles[$fileHash])) {
                    $loadResult                   = include $filePath;
                    self::$loadedFiles[$fileHash] = $filePath;
                }
            } else {
                trigger_error('file_is_not_readable' . $filePath, E_USER_ERROR);
            }
        } else {
            trigger_error('file_no_exists:' . $filePath, E_USER_ERROR);
        }
        return $loadResult;
    }

    /**
     * 设置加载路径（帮助自动加载）
     * @todo   需要仔细想想怎么实现比较好
     * @author Jeff Liu
     *
     * @param $path
     */
    public static function setIncludePath($path)
    {
        set_include_path(get_include_path() . PATH_SEPARATOR . $path);
    }

    /**
     * 初始化autoload 配置项
     * @author Jeff Liu
     */
    public static function initAutoLoadConf()
    {
        //设置加载路径
        $autoloadPath = C('autoLoadPath');
        if ($autoloadPath) {
            $autoloadPath = explode(',', $autoloadPath);
            $autoloadPath = implode(PATH_SEPARATOR, $autoloadPath);
            Importer::setIncludePath($autoloadPath);
        }
    }

    /**
     * 加载基础文件
     * @author Jeff Liu
     * @date   2014-04-15
     */
    public static function loadFramewrok()
    {
        //初始化autoload 配置项
        $baseFileList = SimpleMVC::getBaseFileList();
        if (is_array($baseFileList)) {
            foreach ($baseFileList as $file) {
                Importer::importFileByFullPath($file);
            }
        }
    }

    /**
     * todo 这个也可以放到配置文件中
     * @return array
     */
    public static function fileMapping()
    {
        return array(
            // phpseclib /Crypt
                'Crypt_AES'                 => VENDOR_PATH . 'phpseclib/Crypt/AES.php',
                'Crypt_Base'                => VENDOR_PATH . 'phpseclib/Crypt/Base.php',
                'Crypt_Blowfish'            => VENDOR_PATH . 'phpseclib/Crypt/Blowfish.php',
                'Crypt_DES'                 => VENDOR_PATH . 'phpseclib/Crypt/DES.php',
                'Crypt_Hash'                => VENDOR_PATH . 'phpseclib/Crypt/Hash.php',
                'Crypt_RC2'                 => VENDOR_PATH . 'phpseclib/Crypt/RC2.php',
                'Crypt_RC4'                 => VENDOR_PATH . 'phpseclib/Crypt/RC4.php',
                'Crypt_Rijndael'            => VENDOR_PATH . 'phpseclib/Crypt/Rijndael.php',
                'Crypt_RSA'                 => VENDOR_PATH . 'phpseclib/Crypt/RSA.php',
                'Crypt_TripleDES'           => VENDOR_PATH . 'phpseclib/Crypt/TripleDES.php',
                'Crypt_Twofish'             => VENDOR_PATH . 'phpseclib/Crypt/Twofish.php',
            // phpseclib /File
                'File_ANSI'                 => VENDOR_PATH . 'phpseclib/File/ANSI.php',
                'File_ANSI1'                => VENDOR_PATH . 'phpseclib/File/ASN1.php',
                'File_X509'                 => VENDOR_PATH . 'phpseclib/File/X509.php',
            //phpseclib /Math
                'Math_BigInteger'           => VENDOR_PATH . 'phpseclib/Math/BigInteger.php',
            //phpseclib /Net
                'Net_SCP'                   => VENDOR_PATH . 'phpseclib/Net/SCP.php',
                'Net_SFTP'                  => VENDOR_PATH . 'phpseclib/Net/SFTP.php',
                'Net_SSH1'                  => VENDOR_PATH . 'phpseclib/Net/SSH1.php',
                'Net_SSH2'                  => VENDOR_PATH . 'phpseclib/Net/SSH2.php',
            //phpseclib /Net/SFTP
                'Net_SFTP_Stream'           => VENDOR_PATH . 'phpseclib/Net/SFTP/Stream.php',
            //phpseclib /System
                'System_SSH_Agent_Identity' => VENDOR_PATH . 'phpseclib/System/SSH/Agent.php',
        );
    }

    /**
     * @param $className
     *
     * @author Jeff Liu
     *
     * @return bool
     */
    public static function loadMappingFile($className)
    {
        $loadResult  = false;
        $fileMapping = self::fileMapping();
        if (isset($fileMapping[$className])) {
            $loadResult = self::importFileByFullPath($fileMapping[$className], false);
        }
        return $loadResult;
    }

}
