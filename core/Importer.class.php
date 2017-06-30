<?php

/**
 *
 * 用于include文件
 * @author Jeff.Liu<jeff.liu.guo@gmail.com>
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
                throw_exception('file_is_not_readable:' . $finalPath);
            }
        } else {
            throw_exception('file_no_exists:' . $finalPath);
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
                    throw_exception('file_is_not_readable:' . $filePath);
                }
            }
        } else {
            if ($showError) {
                debug_print_backtrace();
                throw_exception('file_no_exists:' . $filePath);
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
     * @param bool   $excludEnvFile 排除环境config
     *
     * @return array
     */
    public static function importConfigFile($configPath, $configFileExt = 'php', $excludEnvFile = true)
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
                $configFileName = basename($configFile);
                if ($excludEnvFile && stripos($configFileName, 'env.') === 0) {//环境
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
                        throw_exception('file_is_not_readable:' . $configFile);
                    }
                } else {
                    throw_exception('file_no_exists:' . $configFile);
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
            $groupName = Request::getGroup();;
        }

        if ('@' == $moduleName) {
            $moduleName = Request::getModule();
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
            $groupName = Request::getGroup();
        }

        if ('@' == $moduleName) {
            $moduleName = Request::getModule();
        }

        $serviceFileName = $serviceName . $serviceFileSuffer;

        $serviceFile = APP_DIR . $groupName . '/' . $moduleName . '/services/' . ucfirst($serviceFileName);
        $loadResult  = self::importFileByFullPath($serviceFile);
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
            $groupName = Request::getGroup();
        }

        if ('@' == $moduleName) {
            $moduleName = Request::getModule();
        }

        $helperFileName = $helperName . '.class.php';

        //首先加载core目录下的helper
        $helperFile = CORE_DIR . 'helper/' . $helperFileName;
        $loadResult = self::importFileByFullPath($helperFile, false);

        if (!$loadResult) {
            $helperFile = APP_DIR . $groupName . '/' . $moduleName . '/helper/' . $helperFileName;
            $loadResult = self::importFileByFullPath($helperFile, false);

            if (!$loadResult) { //当前group module下加载controller失败
                $modules = self::getModuleList($groupName);
                foreach ($modules as $module) {
                    $filePath = APP_DIR . $groupName . '/' . $module . '/helper/' . $helperFileName;
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
            $groupName = Request::getGroup();
        }

        if ('@' == $moduleName) {
            $moduleName = Request::getModule();
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
                    if (is_dir($file)) { //当前group下的一个module
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
            self::loadHelper(lcfirst($className));
        } else {

            //尝试获取fileMapping
            $loadResult = self::loadMappingFile($className);
            if (!$loadResult) {
                // 根据自动加载路径设置进行尝试搜索
                $includePath    = get_include_path();
                $paths          = explode(PATH_SEPARATOR, $includePath);
                $fileExtensions = C('autoLoadFileExtensions');
                if (empty($fileExtensions)) {
                    $fileExtensions = ['.class.php', '.php'];
                } else if (!is_array($fileExtensions)) {
                    $fileExtensions = [$fileExtensions];
                }

                $tryFileList = [];

                $found = false;

                foreach ($paths as $path) {
                    foreach ($fileExtensions as $fileExtension) {
                        $fullFilePath  = $path . '/' . $className . $fileExtension;
                        $tryFileList[] = $fullFilePath;
                        if (self::importFileByFullPath($fullFilePath, false)) {
                            $found = true;
                            break;
                        }
                    }
                }
                if (!$found) {
                    throw_exception('class ' . $className . ' not find in below path:' . var_export($tryFileList, 1));
                    if (defined('APP_DEBUG') && APP_DEBUG) {
                        file_debug(['class' => $className, 'msg' => 'not found', '$tryFileList' => $tryFileList]);
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
                throw_exception('file_is_not_readable' . $filePath);
            }
        } else {
            throw_exception('file_no_exists:' . $filePath);
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
            $autoloadPath = implode(PATH_SEPARATOR, $autoloadPath);
            Importer::setIncludePath($autoloadPath);
        }
    }


    /**
     * @return array
     */
    public static function fileMapping()
    {
        $fileMapping = C('fileMapping');
        if (empty($fileMapping)) {
            $fileMapping = [];
        } else if (is_scalar($fileMapping)) {
            $fileMapping = [$fileMapping];
        }
        return $fileMapping;
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
