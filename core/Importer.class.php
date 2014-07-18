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
        $load_result = false;
        if (is_file($filePath)) {
            if (is_readable($filePath)) {
                $file_hash = md5($filePath);

                if (!isset(Importer::$loadedFiles[$file_hash])) {
                    include $filePath;
                    self::$loadedFiles[$file_hash] = $filePath;
                }
                $load_result = true;
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
        return $load_result;
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
            $groupName = 'frontend';
        }

        if ('@' == $moduleName) {
            $moduleName = 'default';
        }
        $baseControllerFile = APP_PATH . '/' . $groupName . '/' . $groupName . '.control.php';
        self::importFileByFullPath($baseControllerFile);

        $controllerFileName = $controllerName . '.control.php';

        $controllerFile = APP_PATH . '/' . $groupName . '/' . $moduleName . '/controllers/' . $controllerFileName;
        $loadResult     = self::importFileByFullPath($controllerFile);

        if (!$loadResult) { //当前group module下加载controller失败
            $modules = self::getModuleList('frontend');
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
     * 加载model文件
     * @todo 需要重新实现
     *
     * @param string $modelName
     * @param string $groupName
     * @param string $moduleName
     */
    public static function loadModel($modelName, $groupName = '@', $moduleName = '@')
    {
        if ('@' == $groupName) {
            $groupName = GROUP_NAME;
        }

        if ('@' == $moduleName) {
            $moduleName = MODULE_NAME;
        }
        //        $base_model_file = APP_PATH . '/' . $group_name . '/' . $group_name . '.model.php';
        //        self::importFileByFullPath($base_model_file);

        $modelFileName = $modelName . '.model.php';

        $modelFile  = APP_PATH . '/' . $groupName . '/' . $moduleName . '/models/' . $modelFileName;
        $loadResult = self::importFileByFullPath($modelFile);

        if (!$loadResult) { //当前group module下加载controller失败
            $modules = self::getModuleList(GROUP_NAME);
            foreach ($modules as $module) {
                $filePath = APP_PATH . '/' . $groupName . '/' . $module . '/models/' . $modelFileName;
                $files    = self::getModelListByGroupAndModule($groupName, $module);
                if (in_array($filePath, $files, true)) {
                    $loadResult = self::importFileByFullPath($modelFile);
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
                        $filePath = APP_PATH . '/' . $group . '/' . $groupModule . '/models/' . $modelFileName;
                        $files    = self::getModelListByGroupAndModule($group, $groupModule);
                        if (in_array($filePath, $files, true)) {
                            $loadResult = self::importFileByFullPath($modelFile);
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
     * @todo 需要重新实现
     *
     * @param string $service_name
     * @param string $group_name
     * @param string $module_name
     */
    public static function loadService($service_name, $group_name = '@', $module_name = '@')
    {
        if ('@' == $group_name) {
            $group_name = GROUP_NAME;
        }

        if ('@' == $module_name) {
            $module_name = MODULE_NAME;
        }

        $serviceFileName = $service_name . '.service.php';


        $modelFile = APP_PATH . $group_name . '/' . $module_name . '/services/' . ucfirst($serviceFileName);
        //echo $model_file,':',var_export(is_file($model_file));exit;
        $loadResult = self::importFileByFullPath($modelFile);
        if (!$loadResult) { //当前group module下加载controller失败
            $modules = self::getModuleList(GROUP_NAME);
            foreach ($modules as $module) {
                $filePath = APP_PATH . '/' . $group_name . '/' . $module . '/services/' . $serviceFileName;
                $files    = self::getServiceListByGroupAndModule($group_name, $module);
                if (in_array($filePath, $files, true)) {
                    $loadResult = self::importFileByFullPath($modelFile);
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
                            $loadResult = self::importFileByFullPath($modelFile);
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
     * @todo 需要重新实现
     *
     * @param string $helperName
     * @param string $groupName
     * @param string $moduleName
     */
    public static function loadHelper($helperName, $groupName = '@', $moduleName = '@')
    {
        if ('@' == $groupName) {
            $groupName = GROUP_NAME;
        }

        if ('@' == $moduleName) {
            $moduleName = MODULE_NAME;
        }

        $helperFileName = $helperName . '.helper.php';

        $helperFile = APP_PATH . '/' . $groupName . '/' . $moduleName . '/helpers/' . $helperFileName;
        $loadResult = self::importFileByFullPath($helperFile);

        if (!$loadResult) { //当前group module下加载controller失败
            $modules = self::getModuleList(GROUP_NAME);
            foreach ($modules as $module) {
                $filePath = APP_PATH . '/' . $groupName . '/' . $module . '/helpers/' . $helperFileName;
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

    /**
     * 加载对应的dao文件
     * @todo 需要重新实现
     *
     * @param string $daoName
     * @param string $groupName
     * @param string $moduleName
     */
    public static function loadDAO($daoName, $groupName = '@', $moduleName = '@')
    {
        if ('@' == $groupName) {
            $groupName = GROUP_NAME;
        }

        if ('@' == $moduleName) {
            $moduleName = MODULE_NAME;
        }
        //        $base_dao_file = APP_PATH . '/' . $group_name . '/' . $group_name . '.dao.php';
        //        self::importFileByFullPath($base_dao_file);

        $daoFileName = $daoName . '.dao.php';

        $daoFile    = APP_PATH . '/' . $groupName . '/' . $moduleName . '/daos/' . $daoFileName;
        $loadResult = self::importFileByFullPath($daoFile);

        if (!$loadResult) { //当前group module下加载controller失败
            $modules = self::getModuleList(GROUP_NAME);
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
     * @param string $group_name
     * @param string $module_name
     *
     * @return array
     */
    public static function getControllerListByGroupAndModule($group_name, $module_name)
    {
        $appFileStruct  = self::getAppFileStruct();
        $files          = isset($appFileStruct[$group_name][$module_name]) ? $appFileStruct[$group_name][$module_name] : array();
        $controllerList = isset($files['controllers']) ? $files['controllers'] : array();

        return $controllerList;
    }

    /**
     * 根据给定的group和module获得model列表
     *
     * @param string $group_name
     * @param string $module_name
     *
     * @return array
     */
    public static function getModelListByGroupAndModule($group_name, $module_name)
    {
        $appFileStruct = self::getAppFileStruct();
        $files         = isset($appFileStruct[$group_name][$module_name]) ? $appFileStruct[$group_name][$module_name] : array();
        $modelList     = isset($files['models']) ? $files['models'] : array();

        return $modelList;
    }

    /**
     * 根据给定的group和module获得service列表
     *
     * @param string $group_name
     * @param string $module_name
     *
     * @return array
     */
    public static function getServiceListByGroupAndModule($group_name, $module_name)
    {
        $appFileStruct = self::getAppFileStruct();
        $files         = isset($appFileStruct[$group_name][$module_name]) ? $appFileStruct[$group_name][$module_name] : array();
        $serviceList   = isset($files['services']) ? $files['services'] : array();

        return $serviceList;
    }

    /**
     * 根据给定的group和module获得dao列表
     *
     * @param string $group_name
     * @param string $module_name
     *
     * @return array
     */
    public static function getDAOListByGroupAndModule($group_name, $module_name)
    {
        $appFileStruct = self::getAppFileStruct();
        $files         = isset($appFileStruct[$group_name][$module_name]) ? $appFileStruct[$group_name][$module_name] : array();
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
            $glob_pattern = $path . '*';
        } else {
            $glob_pattern = $path . '/*';
        }
        foreach (glob($glob_pattern) as $single) {
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
        $group_list = array();
        !defined('APP_GROUP_LIST') && define('APP_GROUP_LIST', '');
        if (APP_GROUP_LIST) {
            $group_list = explode(',', APP_GROUP_LIST);
        }
        return $group_list;
    }

    /**
     * 获得module列表
     *
     * @param string $group_name
     *
     * @return array
     */
    public static function getModuleList($group_name = '')
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

        if (!empty($group_name) && isset($finalData[$group_name])) {
            $finalData = $finalData[$group_name];
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
        !defined('APP_GROUP_LIST') && define('APP_GROUP_LIST', '');
        if (APP_GROUP_LIST) {
            $appGroupList = explode(',', APP_GROUP_LIST);
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
     * @param String $class_name 需要加载的类名
     */
    public static function autoLoad($class_name)
    {
        SmvcDebugHelper::instance()->debug(
                array('info' => $class_name, 'label' => '$class_name', 'level' => 'warn')
        );
        if ('Control' == substr($class_name, -7)) { //controller类
            $controllerName = substr_replace($class_name, '', -7);
            self::loadController(lcfirst($controllerName));
            return;
        } elseif ('Model' == substr($class_name, -5)) { //model类 TODO 使用service dao  model 这个还需要吗？？？
            $modelName = substr_replace($class_name, '', -5);
            self::loadModel(lcfirst($modelName));
            return;
        } elseif ('DAO' == substr($class_name, -3)) { //dao类
            $daoName = substr_replace($class_name, '', -3);
            self::loadDAO(lcfirst($daoName));
            return;
        } elseif ('Service' == substr($class_name, -7)) { //service类
            $serviceName = substr_replace($class_name, '', -7);
            self::loadService(lcfirst($serviceName));
            return;
        } elseif ('Helper' == substr($class_name, -6)) { //辅助类
            //TODO 需要考虑 group moduel 里面的helper？？？  分组下面需要有吗？？？
            $helperName = substr_replace($class_name, '', -6);
            self::loadHelper(lcfirst($helperName));
            return;
        }

        // 根据自动加载路径设置进行尝试搜索
        $includePath    = get_include_path();
        $paths          = explode(PATH_SEPARATOR, $includePath);
        $fileExtensions = array('.class.php', '.php');
        foreach ($paths as $path) {
            // TODO 如果加载类成功则返回 这个需要完善。。。 文件名后缀需要整理
            foreach ($fileExtensions as $file_extension) {
                $fullFilePath = $path . '/' . $class_name . $file_extension;
                if (self::importFileByFullPath($fullFilePath, false)) {
                    break;
                }
            }
        }
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
     * 加载基础文件
     * @author Jeff Liu
     * @date   2014-04-15
     */
    public static function importBaseFiles()
    {
        Importer::importFile('core.Router', 'class.php', ROOT_PATH);
        Importer::importFile('core.Factory', 'class.php', ROOT_PATH);
        Importer::importFile('core.Dispatcher', 'class.php', ROOT_PATH);
        Importer::importFile('core.Object', 'class.php', ROOT_PATH);
        Importer::importFile('core.control.Base', 'class.php', ROOT_PATH);
        Importer::importFile('core.model.Base', 'class.php', ROOT_PATH);
        Importer::importFile('core.dao.Base', 'class.php', ROOT_PATH);
        Importer::importFile('core.service.Base', 'class.php', ROOT_PATH);
        Importer::importFile('core.view.View', 'class.php', ROOT_PATH);
        Importer::importFile('core.SmvcConf', 'class.php', ROOT_PATH);
    }

}
