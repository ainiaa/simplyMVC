<?php

/**
 *
 * 用于include文件
 * @author Jeff Liu
 * TODO 在加载非当前group，module下的controller的时候 还需要注意 对应model和dao初始化问题。。。
 */
class Importer
{
    private static $loadedFiles = []; //已经加载的文件


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
    public static function importFile($filePath, $fileExt = 'php', $rootPath = INCLUDE_DIR)
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
     * @author Jeff Liu
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
     * @param bool   $excludEnv 排除环境config
     *
     * @return array
     */
    public static function importConfigFile($configPath, $configFileExt = 'php', $excludEnv = true)
    {
        $finalResult = [];
        if (empty($configPath)) {
            $configPath = ROOT_DIR . 'config';
        }

        if (is_file($configPath)) { //如果传递过来的是一个具体的文件路径的话 直接调用importFileByFullPath方法
            $configFiles = [$configPath];
        } else {
            $configFiles = glob($configPath . '/' . '*.' . $configFileExt);
        }

        if (!empty($configFiles) && is_array($configFiles)) {
            foreach ($configFiles as $configFile) {
                if ($excludEnv && stripos($configFile, 'env.') === 0) {//环境
                    continue;
                }
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
        $controllerFileSuffer = C('controllerFileSuffer');
        if ('@' == $groupName) {
            $groupName = Router::getGroup();;
        }

        if ('@' == $moduleName) {
            $moduleName = Router::getModule();
        }
        $baseControllerFile = APP_DIR . $groupName . '/' . $groupName . $controllerFileSuffer;
        self::importFileByFullPath($baseControllerFile);

        $controllerFileName = $controllerName . $controllerFileSuffer;

        $controllerFile = APP_DIR . $groupName . '/' . $moduleName . '/controllers/' . $controllerFileName;
        $loadResult     = self::importFileByFullPath($controllerFile);

        if (!$loadResult) { //当前group module下加载controller失败
            $modules = self::getModuleList($groupName);
            foreach ($modules as $module) {
                $filePath = APP_DIR . $groupName . '/' . $module . '/controllers/' . $controllerFileName;
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
                        $filePath = APP_DIR . $group . '/' . $groupModule . '/controllers/' . $controllerFileName;
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
        $serviceFileSuffer = C('serviceFileSuffer');
        if ('@' == $groupName) {
            $groupName = Router::getGroup();
        }

        if ('@' == $moduleName) {
            $moduleName = Router::getModule();
        }

        $serviceFileName = $serviceName . $serviceFileSuffer;

        $serviceFile = APP_DIR . $groupName . '/' . $moduleName . '/services/' . ucfirst($serviceFileName);
        //echo $model_file,':',var_export(is_file($model_file));exit;
        $loadResult = self::importFileByFullPath($serviceFile);
        if (!$loadResult) { //当前group module下加载controller失败
            $modules = self::getModuleList($groupName);
            foreach ($modules as $module) {
                $filePath = APP_DIR . $groupName . '/' . $module . '/services/' . $serviceFileName;
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
                        $filePath = APP_DIR . $group . '/' . $groupModule . '/services/' . $serviceFileName;
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
        $helperFile = CORE_DIR . 'helper/' . $helperFileName;
        $loadResult = self::importFileByFullPath($helperFile, false);
        //        SmvcDebugHelper::instance()->debug(
        //                array(
        //                        'info'  => $helperFile,
        //                        'label' => '$helperFile ' . __METHOD__,
        //                        'level' => 'info'
        //                )
        //        );

        if (!$loadResult) {
            $helperFile = APP_DIR .  $groupName . '/' . $moduleName . '/helper/' . $helperFileName;
            $loadResult = self::importFileByFullPath($helperFile, false);

            if (!$loadResult) { //当前group module下加载controller失败
                $modules = self::getModuleList($groupName);
                foreach ($modules as $module) {
                    $filePath = APP_DIR  . $groupName . '/' . $module . '/helper/' . $helperFileName;
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
                        foreach ($groupModules as $groupModule) {
                            $filePath = APP_DIR . $group . '/' . $groupModule . '/helpers/' . $helperFileName;
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
        $daoFileSuffer = C('daoFileSuffer');

        if ('@' == $groupName) {
            $groupName = Router::getGroup();
        }

        if ('@' == $moduleName) {
            $moduleName = Router::getModule();
        }

        $daoFileName = $daoName . $daoFileSuffer;

        $daoFile    = APP_DIR . $groupName . '/' . $moduleName . '/daos/' . $daoFileName;
        $loadResult = self::importFileByFullPath($daoFile);

        if (!$loadResult) { //当前group module下加载controller失败
            $modules = self::getModuleList($groupName);
            foreach ($modules as $module) {
                $filePath = APP_DIR . $groupName . '/' . $module . '/daos/' . $daoFileName;
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
                        $filePath = APP_DIR . $group . '/' . $groupModule . '/daos/' . $daoFileName;
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
        $files          = isset($appFileStruct[$groupName][$moduleName]) ? $appFileStruct[$groupName][$moduleName] : [];
        $controllerList = isset($files['controllers']) ? $files['controllers'] : [];

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
        $files         = isset($appFileStruct[$groupName][$moduleName]) ? $appFileStruct[$groupName][$moduleName] : [];
        $serviceList   = isset($files['services']) ? $files['services'] : [];

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
        $files         = isset($appFileStruct[$groupName][$moduleName]) ? $appFileStruct[$groupName][$moduleName] : [];
        $daoList       = isset($files['daos']) ? $files['daos'] : [];

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
        $tree = [];
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
        $finalData = [];
        $groupList = self::getGroupList();
        foreach ($groupList as $group) {
            $groupBasePath = APP_DIR . $group . '/';
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
        static $finalFileStruct = [];
        if (C('APP_GROUP_LIST')) {
            $appGroupList = explode(',', C('APP_GROUP_LIST'));
            foreach ($appGroupList as $groupName) {
                $groupBasePath                          = APP_DIR . $groupName . '/';
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
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     *
     * @param String $className 需要加载的类名
     */
    public static function autoLoad($className)
    {
        //        echo '$className:', $className, '<br />';
        $controllerSuffer    = 'Controller';
        $controllerSufferLen = strlen($controllerSuffer);
        $daoSuffer           = 'DAO';
        $daoSufferLen        = strlen($daoSuffer);
        $serviceSuffer       = 'Service';
        $serviceSufferLen    = strlen($serviceSuffer);
        $helperSuffer        = 'Helper';
        $helperSufferLen     = strlen($helperSuffer);

        if ($controllerSuffer === substr($className, -$controllerSufferLen)) { //controller类
            $controllerName = substr_replace($className, '', -$controllerSufferLen);
            self::loadController(lcfirst($controllerName));
        } else if ($daoSuffer === substr($className, -$daoSufferLen)) { //dao类
            $daoName = substr_replace($className, '', -$daoSufferLen);
            self::loadDAO(lcfirst($daoName));
        } else if ($serviceSuffer === substr($className, -$serviceSufferLen)) { //service类
            $serviceName = substr_replace($className, '', -$serviceSufferLen);
            self::loadService(lcfirst($serviceName));
        } else if ($helperSuffer === substr($className, -$helperSufferLen)) { //辅助类
            //TODO 需要考虑 group moduel 里面的helper？？？  分组下面需要有吗？？？
            //            $helperName = substr_replace($className, '', -$helperSufferLen);
            self::loadHelper(lcfirst($className));
        } else {

            //尝试获取fileMapping
            $loadResult = self::loadMappingFile($className);
            if (!$loadResult) {
                // 根据自动加载路径设置进行尝试搜索
                $includePath    = get_include_path();
                $paths          = explode(PATH_SEPARATOR, $includePath);
                $fileExtensions = ['.class.php', '.php'];
                foreach ($paths as $path) {
                    // TODO 如果加载类成功则返回 这个需要完善。。。 文件名后缀需要整理
                    foreach ($fileExtensions as $fileExtension) {
                        $fullFilePath = $path . '/' . $className . $fileExtension;
                        if (self::importFileByFullPath($fullFilePath, false)) {
                            break;
                        }
                    }
                }
            }
        }
    }

    /**
     *
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
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
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     *
     * @param $path
     */
    public static function setIncludePath($path)
    {
        set_include_path(get_include_path() . PATH_SEPARATOR . $path);
    }

    /**
     * 初始化autoload 配置项
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
     */
    public static function initAutoLoad()
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
     *
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
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
        return [
            // phpseclib /Crypt
                'Crypt_AES'                 => VENDOR_DIR . 'phpseclib/Crypt/AES.php',
                'Crypt_Base'                => VENDOR_DIR . 'phpseclib/Crypt/Base.php',
                'Crypt_Blowfish'            => VENDOR_DIR . 'phpseclib/Crypt/Blowfish.php',
                'Crypt_DES'                 => VENDOR_DIR . 'phpseclib/Crypt/DES.php',
                'Crypt_Hash'                => VENDOR_DIR . 'phpseclib/Crypt/Hash.php',
                'Crypt_RC2'                 => VENDOR_DIR . 'phpseclib/Crypt/RC2.php',
                'Crypt_RC4'                 => VENDOR_DIR . 'phpseclib/Crypt/RC4.php',
                'Crypt_Rijndael'            => VENDOR_DIR . 'phpseclib/Crypt/Rijndael.php',
                'Crypt_RSA'                 => VENDOR_DIR . 'phpseclib/Crypt/RSA.php',
                'Crypt_TripleDES'           => VENDOR_DIR . 'phpseclib/Crypt/TripleDES.php',
                'Crypt_Twofish'             => VENDOR_DIR . 'phpseclib/Crypt/Twofish.php',
            // phpseclib /File
                'File_ANSI'                 => VENDOR_DIR . 'phpseclib/File/ANSI.php',
                'File_ANSI1'                => VENDOR_DIR . 'phpseclib/File/ASN1.php',
                'File_X509'                 => VENDOR_DIR . 'phpseclib/File/X509.php',
            //phpseclib /Math
                'Math_BigInteger'           => VENDOR_DIR . 'phpseclib/Math/BigInteger.php',
            //phpseclib /Net
                'Net_SCP'                   => VENDOR_DIR . 'phpseclib/Net/SCP.php',
                'Net_SFTP'                  => VENDOR_DIR . 'phpseclib/Net/SFTP.php',
                'Net_SSH1'                  => VENDOR_DIR . 'phpseclib/Net/SSH1.php',
                'Net_SSH2'                  => VENDOR_DIR . 'phpseclib/Net/SSH2.php',
            //phpseclib /Net/SFTP
                'Net_SFTP_Stream'           => VENDOR_DIR . 'phpseclib/Net/SFTP/Stream.php',
            //phpseclib /System
                'System_SSH_Agent_Identity' => VENDOR_DIR . 'phpseclib/System/SSH/Agent.php',
        ];
    }

    /**
     * @param $className
     *
     * @author Jeff.Liu<jeff.liu.guo@gmail.com>
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
