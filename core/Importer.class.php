<?php

/**
 *
 * 用于include文件
 * @author jeff liu
 * TODO 在加载非当前group，module下的controller的时候 还需要注意 对应model和dao初始化问题。。。
 */
class Importer
{
    private static $_loadedFiles = array(); //已经加载的文件


    /**
     * 返回所有已经加载过的文件
     * @return array
     */
    public static function getLoadedFiles()
    {
        return Importer::$_loadedFiles;
    }

    /**
     *
     * 加载制定的文件
     *
     * @param string $file_path
     * @param string $file_ext
     * @param string $root_path
     */
    public static function importFile($file_path, $file_ext = 'php', $root_path = INCLUDE_PATH)
    {
        $final_path = $root_path . '/' . preg_replace('|\.|', '/', $file_path) . '.' . $file_ext;
        if (is_file($final_path)) {
            if (is_readable($final_path)) {
                $file_hash = md5($final_path);
                if (!isset(Importer::$_loadedFiles[$file_hash])) {
                    include $final_path;
                    Importer::$_loadedFiles[$file_hash] = $final_path;
                }
            } else {
                trigger_error('file_is_not_readable:' . $final_path, E_USER_ERROR);
            }
        } else {
            trigger_error('file_no_exists : ' . $final_path, E_USER_ERROR);
        }
    }

    /**
     *
     * @author jeff liu
     *
     * @param string $file_path
     *
     * @return bool
     */
    public static function importFileByFullPath($file_path, $show_error = true)
    {
        $load_result = false;
        if (is_file($file_path)) {
            if (is_readable($file_path)) {
                $file_hash = md5($file_path);

                if (!isset(Importer::$_loadedFiles[$file_hash])) {
                    include $file_path;
                    self::$_loadedFiles[$file_hash] = $file_path;
                }
                $load_result = true;
            } else {
                if ($show_error) {
                    trigger_error('file_is_not_readable' . $file_path, E_USER_ERROR);
                }

            }
        } else {
            if ($show_error) {
                trigger_error('file_no_exists:' . $file_path, E_USER_ERROR);
            }
        }
        return $load_result;
    }

    /**
     *
     * 加载配置文件
     *
     * @param string $config_path     config目录
     * @param string $config_file_ext config文件的后缀
     *
     * @return array
     */
    public static function importConfigFile($config_path, $config_file_ext = 'inc.php')
    {
        $final_result = array();
        if (empty($config_path)) {
            $config_path = ROOT_PATH . '/config';
        }

        if (is_file($config_path)) { //如果传递过来的是一个具体的文件路径的话 直接调用importFileByFullPath方法
            $config_files = array($config_path);
        } else {
            $config_files = glob($config_path . '/' . '*.' . $config_file_ext);
        }

        if (!empty($config_files) && is_array($config_files)) {
            foreach ($config_files as $config_file) {
                if (is_file($config_file)) {
                    if (is_readable($config_file)) {
                        $file_path_info = pathinfo($config_file); //获得当文件的pathinfo信息
                        $file_name      = $file_path_info['filename']; //获得当前文件名
                        $file_hash      = md5($config_file);
                        if (!isset(Importer::$_loadedFiles[$file_hash])) { //还没有加载过当前config文件 加载当前cofnig文件
                            $final_result[$file_name] = include $config_file;
                            Importer::$_loadedFiles[$file_hash] = $config_file;
                        }
                    } else {
                        trigger_error('file_is_not_readable' . $config_file, E_USER_ERROR);
                    }

                } else {
                    trigger_error('file_no_exists' . $config_file, E_USER_ERROR);
                }
            }
        }

        return $final_result;
    }


    /**
     * 加载控制器文件
     *
     * @param string $controller_name
     * @param string $group_name
     * @param string $module_name
     */
    public static function loadController($controller_name, $group_name = '@', $module_name = '@')
    {
        if ('@' == $group_name) {
            $group_name = 'frontend';
        }

        if ('@' == $module_name) {
            $module_name = 'default';
        }
        $base_controller_file = APP_PATH . '/' . $group_name . '/' . $group_name . '.control.php';
        self::importFileByFullPath($base_controller_file);

        $controller_file_name = $controller_name . '.control.php';

        $controller_file = APP_PATH . '/' . $group_name . '/' . $module_name . '/controllers/' . $controller_file_name;
        $load_result     = self::importFileByFullPath($controller_file);

        if (!$load_result) { //当前group module下加载controller失败
            $modules = self::getModuleList('frontend');
            foreach ($modules as $module) {
                $file_path = APP_PATH . '/' . $group_name . '/' . $module . '/controllers/' . $controller_file_name;
                $files     = self::getControllerListByGroupAndModule($group_name, $module);
                if (in_array($file_path, $files, true)) {
                    $load_result = self::importFileByFullPath($controller_file);
                    if ($load_result) { //加载成功直接break
                        break;
                    }
                }
            }
            //当前group加载失败 获取所有的加载其他group下的controll 直到第一个加载成功为止
            if (!$load_result) {
                $group_list = self::getGroupList();
                foreach ((array)$group_list as $group) {
                    $group_modules = self::getModuleList($group);
                    foreach ($group_modules as $g_module) {
                        $file_path = APP_PATH . '/' . $group . '/' . $g_module . '/controllers/' . $controller_file_name;
                        $files     = self::getControllerListByGroupAndModule($group, $g_module);
                        if (in_array($file_path, $files, true)) {
                            $load_result = self::importFileByFullPath($controller_file);
                            if ($load_result) { //加载成功直接break
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
     *
     * @param string $model_name
     * @param string $group_name
     * @param string $module_name
     */
    public static function loadModel($model_name, $group_name = '@', $module_name = '@')
    {
        if ('@' == $group_name) {
            $group_name = GROUP_NAME;
        }

        if ('@' == $module_name) {
            $module_name = MODULE_NAME;
        }
        //        $base_model_file = APP_PATH . '/' . $group_name . '/' . $group_name . '.model.php';
        //        self::importFileByFullPath($base_model_file);

        $model_file_name = $model_name . '.model.php';

        $model_file  = APP_PATH . '/' . $group_name . '/' . $module_name . '/models/' . $model_file_name;
        $load_result = self::importFileByFullPath($model_file);

        if (!$load_result) { //当前group module下加载controller失败
            $modules = self::getModuleList(GROUP_NAME);
            foreach ($modules as $module) {
                $file_path = APP_PATH . '/' . $group_name . '/' . $module . '/models/' . $model_file_name;
                $files     = self::getModelListByGroupAndModule($group_name, $module);
                if (in_array($file_path, $files, true)) {
                    $load_result = self::importFileByFullPath($model_file);
                    if ($load_result) { //加载成功直接break
                        break;
                    }
                }
            }
            //当前group加载失败 获取所有的加载其他group下的controll 直到第一个加载成功为止
            if (!$load_result) {
                $group_list = self::getGroupList();
                foreach ((array)$group_list as $group) {
                    $group_modules = self::getModuleList($group);
                    foreach ($group_modules as $g_module) {
                        $file_path = APP_PATH . '/' . $group . '/' . $g_module . '/models/' . $model_file_name;
                        $files     = self::getModelListByGroupAndModule($group, $g_module);
                        if (in_array($file_path, $files, true)) {
                            $load_result = self::importFileByFullPath($model_file);
                            if ($load_result) { //加载成功直接break
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

        $service_file_name = $service_name . '.service.php';


        $model_file = APP_PATH . $group_name . '/' . $module_name . '/services/' . ucfirst($service_file_name);
        //echo $model_file,':',var_export(is_file($model_file));exit;
        $load_result = self::importFileByFullPath($model_file);
        if (!$load_result) { //当前group module下加载controller失败
            $modules = self::getModuleList(GROUP_NAME);
            foreach ($modules as $module) {
                $file_path = APP_PATH . '/' . $group_name . '/' . $module . '/services/' . $service_file_name;
                $files     = self::getServiceListByGroupAndModule($group_name, $module);
                if (in_array($file_path, $files, true)) {
                    $load_result = self::importFileByFullPath($model_file);
                    if ($load_result) { //加载成功直接break
                        break;
                    }
                }
            }
            //当前group加载失败 获取所有的加载其他group下的controll 直到第一个加载成功为止
            if (!$load_result) {
                $group_list = self::getGroupList();
                foreach ((array)$group_list as $group) {
                    $group_modules = self::getModuleList($group);
                    foreach ($group_modules as $g_module) {
                        $file_path = APP_PATH . '/' . $group . '/' . $g_module . '/services/' . $service_file_name;
                        $files     = self::getDAOListByGroupAndModule($group, $g_module);
                        if (in_array($file_path, $files, true)) {
                            $load_result = self::importFileByFullPath($model_file);
                            if ($load_result) { //加载成功直接break
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
     * @param string $service_name
     * @param string $group_name
     * @param string $module_name
     */
    public static function loadHelper($service_name, $group_name = '@', $module_name = '@')
    {
        if ('@' == $group_name) {
            $group_name = GROUP_NAME;
        }

        if ('@' == $module_name) {
            $module_name = MODULE_NAME;
        }

        $helper_file_name = $service_name . '.helper.php';

        $helper_file = APP_PATH . '/' . $group_name . '/' . $module_name . '/helpers/' . $helper_file_name;
        $load_result = self::importFileByFullPath($helper_file);

        if (!$load_result) { //当前group module下加载controller失败
            $modules = self::getModuleList(GROUP_NAME);
            foreach ($modules as $module) {
                $file_path = APP_PATH . '/' . $group_name . '/' . $module . '/helpers/' . $helper_file_name;
                $files     = self::getServiceListByGroupAndModule($group_name, $module);
                if (in_array($file_path, $files, true)) {
                    $load_result = self::importFileByFullPath($helper_file);
                    if ($load_result) { //加载成功直接break
                        break;
                    }
                }
            }
            //当前group加载失败 获取所有的加载其他group下的controll 直到第一个加载成功为止
            if (!$load_result) {
                $group_list = self::getGroupList();
                foreach ((array)$group_list as $group) {
                    $group_modules = self::getModuleList($group);
                    foreach ($group_modules as $g_module) {
                        $file_path = APP_PATH . '/' . $group . '/' . $g_module . '/helpers/' . $helper_file_name;
                        $files     = self::getDAOListByGroupAndModule($group, $g_module);
                        if (in_array($file_path, $files, true)) {
                            $load_result = self::importFileByFullPath($helper_file);
                            if ($load_result) { //加载成功直接break
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
     *
     * @param string $model_name
     * @param string $group_name
     * @param string $module_name
     */
    public static function loadDAO($model_name, $group_name = '@', $module_name = '@')
    {
        if ('@' == $group_name) {
            $group_name = GROUP_NAME;
        }

        if ('@' == $module_name) {
            $module_name = MODULE_NAME;
        }
        //        $base_dao_file = APP_PATH . '/' . $group_name . '/' . $group_name . '.dao.php';
        //        self::importFileByFullPath($base_dao_file);

        $dao_file_name = $model_name . '.dao.php';

        $model_file  = APP_PATH . '/' . $group_name . '/' . $module_name . '/daos/' . $dao_file_name;
        $load_result = self::importFileByFullPath($model_file);

        if (!$load_result) { //当前group module下加载controller失败
            $modules = self::getModuleList(GROUP_NAME);
            foreach ($modules as $module) {
                $file_path = APP_PATH . '/' . $group_name . '/' . $module . '/daos/' . $dao_file_name;
                $files     = self::getDAOListByGroupAndModule($group_name, $module);
                if (in_array($file_path, $files, true)) {
                    $load_result = self::importFileByFullPath($model_file);
                    if ($load_result) { //加载成功直接break
                        break;
                    }
                }
            }
            //当前group加载失败 获取所有的加载其他group下的controll 直到第一个加载成功为止
            if (!$load_result) {
                $group_list = self::getGroupList();
                foreach ((array)$group_list as $group) {
                    $group_modules = self::getModuleList($group);
                    foreach ($group_modules as $g_module) {
                        $file_path = APP_PATH . '/' . $group . '/' . $g_module . '/daos/' . $dao_file_name;
                        $files     = self::getDAOListByGroupAndModule($group, $g_module);
                        if (in_array($file_path, $files, true)) {
                            $load_result = self::importFileByFullPath($model_file);
                            if ($load_result) { //加载成功直接break
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
        $app_file_struct = self::getAppFileStruct();
        $files           = isset($app_file_struct[$group_name][$module_name]) ? $app_file_struct[$group_name][$module_name] : array();
        $controller_list = isset($files['controllers']) ? $files['controllers'] : array();

        return $controller_list;
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
        $app_file_struct = self::getAppFileStruct();
        $files           = isset($app_file_struct[$group_name][$module_name]) ? $app_file_struct[$group_name][$module_name] : array();
        $model_list      = isset($files['models']) ? $files['models'] : array();

        return $model_list;
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
        $app_file_struct = self::getAppFileStruct();
        $files           = isset($app_file_struct[$group_name][$module_name]) ? $app_file_struct[$group_name][$module_name] : array();
        $service_list    = isset($files['services']) ? $files['services'] : array();

        return $service_list;
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
        $app_file_struct = self::getAppFileStruct();
        $files           = isset($app_file_struct[$group_name][$module_name]) ? $app_file_struct[$group_name][$module_name] : array();
        $dao_list        = isset($files['daos']) ? $files['daos'] : array();

        return $dao_list;
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
        if (C('APP_GROUP_LIST')) {
            $group_list = explode(',', C('APP_GROUP_LIST'));
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
        $final_data = array();
        $group_list = self::getGroupList();
        foreach ($group_list as $group) {
            $group_base_path = APP_PATH . $group . '/';
            $files           = glob($group_base_path . '*');
            foreach ((array)$files as $file) {
                if (is_dir($file)) {
                    $final_data[$group][] = basename($file);
                }
            }
        }

        if (!empty($group_name) && isset($final_data[$group_name])) {
            $final_data = $final_data[$group_name];
        }

        return $final_data;
    }

    /**
     * 获得app所有文件列表
     * @return mixed
     */
    public static function getAppFileStruct()
    {
        static $final_file_struct = array();
        if (C('APP_GROUP_LIST')) {
            $app_group_list = explode(',', C('APP_GROUP_LIST'));
            foreach ($app_group_list as $group_name) {
                $group_base_path                           = APP_PATH . $group_name . '/';
                $files                                     = glob($group_base_path . '*');
                $final_file_struct[$group_name]['_FILES_'] = $files;
                foreach ($files as $file) {
                    if (is_dir($file)) { //当期group下的一个module
                        $module_name      = basename($file);
                        $module_base_path = $group_base_path . $module_name . '/';
                        $module_files     = glob($module_base_path . '*');

                        $final_file_struct[$group_name][$module_name]['_FILES_'] = $module_files;

                        foreach ($module_files as $module_file) {
                            $file_name                                                = basename($module_file);
                            $final_file_struct[$group_name][$module_name][$file_name] = self::getFileStructBypath(
                                    $module_file
                            );
                        }

                    }
                }
            }
        }

        return $final_file_struct;
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
            $controller_name = substr_replace($class_name, '', -7);
            self::loadController(lcfirst($controller_name));
            return;
        } elseif ('Model' == substr($class_name, -5)) { //model类 TODO 使用service dao  model 这个还需要吗？？？
            $model_name = substr_replace($class_name, '', -5);
            self::loadModel(lcfirst($model_name));
            return;
        } elseif ('DAO' == substr($class_name, -3)) { //dao类
            $dao_name = substr_replace($class_name, '', -3);
            self::loadDAO(lcfirst($dao_name));
            return;
        } elseif ('Service' == substr($class_name, -7)) { //service类
            $service_name = substr_replace($class_name, '', -7);
            self::loadService(lcfirst($service_name));
            return;
        } elseif ('Helper' == substr($class_name, -6)) { //辅助类
            //TODO 需要考虑 group moduel 里面的helper？？？  分组下面需要有吗？？？
            $helper_name = substr_replace($class_name, '', -3);
            self::loadHelper(lcfirst($helper_name));
            return;
        }

        // 根据自动加载路径设置进行尝试搜索
        !defined('APP_AUTOLOAD_PATH') && define('APP_AUTOLOAD_PATH', '.');
        $paths           = explode(',', APP_AUTOLOAD_PATH);
        $file_extensions = array('.class.php', '.php');
        foreach ($paths as $path) {
            // TODO 如果加载类成功则返回 这个需要完善。。。 文件名后缀需要整理

            $full_file_path = '';
            foreach ($file_extensions as $file_extension) {
                $full_file_path = $path . '/' . $class_name . $file_extension;
                if (self::importFileByFullPath($full_file_path, false)) {
                    break;
                }
            }
        }
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
        //            Importer::importFile('core.dao.db', 'class.php', ROOT_PATH);
        //            Importer::importFile('core.Driver.Db.DbMysql', 'class.php', ROOT_PATH);
        Importer::importFile('core.view.View', 'class.php', ROOT_PATH);
    }

}
