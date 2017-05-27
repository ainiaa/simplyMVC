<?php

/**
 * 权限管理
 * Class YmRbac
 * @author Jeff.Liu<liuwy@imageco.com.cn>
 * @date   2017/05/09
 */
class RbacService extends BaseService
{

    /**
     * @var UserInfoDAO
     */
    private $UserInfoDAO;

    /**
     * @var MenuDAO
     */
    private $MenuDAO;

    /**
     * @var RoleDAO
     */
    private $RoleDAO;


    /**
     * @var PublicResourceDAO
     */
    private $PublicResourceDAO;

    /**
     * @var UserGroupDAO
     */
    private $UserGroupDAO;

    /**
     * @var SmvcRedisHelper
     */
    private $SmvcRedisHelper;

    public function __construct()
    {
        $this->SmvcRedisHelper = SmvcRedisHelper::getInstance();
    }


    /**
     * @param $userId
     *
     * @return string
     */
    public function generateUserKey($userId)
    {
        return 'rbac:user:' . $userId;
    }

    /**
     * @return string
     */
    public function generateRoleKey($rId)
    {
        return 'rbac:role:' . $rId;
    }

    public function generateRoleGroupKey($gId)
    {
        return 'rbac:role_group:' . $gId;
    }

    public function generateUserRoleGroupKey($userId)
    {
        return 'rbac:user:rolegroup:' . $userId;
    }

    /**
     * @param $url
     * @param $type
     *
     * @return string
     */
    public function generateResourceKey($type)
    {
        return 'rbac:resource:' . $type;
    }


    /**
     * 获得用户信息
     * @author Jeff.Liu<liuwy@imageco.com.cn>
     *
     * @param $userId
     *
     * @return mixed|null
     */
    public function getUserData($userId)
    {
        if (!is_int($userId)) {
            return null;
        }
        return $this->UserInfoDAO->getOne(['user_id' => $userId]);
    }

    /**
     * 根据用户ID获得对应的roleList
     *
     * @param $userId
     *
     * @return array
     */
    public function getUserBelongsRoleList($userId)
    {
        $roleList  = $this->UserGroupDAO->getRealatedRoleByUserId($userId);
        $roleList2 = $this->UserInfoDAO->getRealatedRoleByUserId($userId);
        $list      = array_merge($roleList, $roleList2);
        return array_unique($list);
    }

    /**
     * @param $userId
     *
     * @return array
     */
    public function getUserInfo($userId)
    {
        return $this->UserInfoDAO->getOne(['user_id' => $userId]);
    }

    /**
     * 获得用户的角色，角色组信息
     *
     * PREFIX:user:$userId => ['userInfo' => ['id' => $id, 'name' => 'name', 'is_super_admin' => 1],'role' => $roleList, 'roleGroup' => $roleGroup]
     *
     * @param $userId
     * @param $roleGroup
     *
     * @return array
     */
    public function getUserFinalRoleList($userId, $roleGroup)
    {
        $roles     = $this->getRolesByRoleGroup($roleGroup);
        $userRoles = $this->getRoles($userId);//用户所属角色列表
        $finalRole = [];
        if (is_array($roles)) {
            $finalRole = $roles;
        }
        if (is_array($userRoles)) {
            $finalRole = array_merge($finalRole, $userRoles);
        }
        return $finalRole;
    }

    /**
     * 获得用户$userId所属的用户角色组
     *  PS:用户 m:1 角色组
     * @author Jeff.Liu<liuwy@imageco.com.cn>
     * @date   2017/050/09
     *
     * @param $userId
     *
     * @return string
     */
    public function getUserRoleGroup($userId)
    {
        $userRoleGroup = $this->SmvcRedisHelper->get($this->generateUserRoleGroupKey($userId));
        if (empty($userRoleGroup)) {
            $userRoleGroup = $this->UserGroupDAO->getUserRoleGroup($userId);
            $this->SmvcRedisHelper->set($this->generateUserRoleGroupKey($userId), $userRoleGroup);
        }
        return $userRoleGroup;
    }

    /**
     * 获取角色组下的所有角色
     *
     * @param $roleGroup
     *
     * @return array
     */
    public function getRolesByRoleGroup($roleGroup)
    {
        return [];
    }

    /**
     * 根据用户$userId 获得所属角色
     *
     * PS:用户 1:n 角色组
     * @author Jeff.Liu<liuwy@imageco.com.cn>
     * @date   2017/050/09
     *
     * @param $userId
     *
     * @return string
     */
    public function getRoles($userId)
    {
        return $this->UserInfoDAO->getRealatedRoleByUserId($userId);
    }

    /**
     * @param $pids
     *
     * @return mixed
     */
    public function getRoleByPrivilege($pids)
    {
        return $this->RoleDAO->getRoleByPrivilege($pids);
    }

    /**
     *
     * @param $url
     *
     * @return string
     */
    public function getMenuPrivilegeByUrl($url)
    {
        return $this->MenuDAO->getPrivByUrl($url);
    }

    /**
     * todo
     *
     * @param $url
     * @param $type
     */
    public function getPrivilegeByUrl($url, $type)
    {

    }

    /**
     * todo
     * 根据用户Id获得所有权限
     * @author Jeff.Liu<liuwy@imageco.com.cn>
     * @date   2017/050/09
     *
     * @param $userId
     *
     * @return string
     */
    public function getPrivilege($userId)
    {
        return '';
    }

    /**
     * todo
     * 获得所有菜单项
     * @author Jeff.Liu<liuwy@imageco.com.cn>
     * @date   2017/050/09
     * @return string
     */
    public function getMenu()
    {
        return '';
    }

    /**
     * todo
     * 获得所有菜单项
     * @author Jeff.Liu<liuwy@imageco.com.cn>
     * @date   2017/050/09
     *
     * @param $userId
     *
     * @return string
     */
    public function getGrantedMenu($userId)
    {
        return '';
    }

    public function getMenuBelongsRoleByUrl($url)
    {
        return $this->MenuDAO->getPrivByUrl($url);
    }

    /**
     * PS redis 存储结构  PREFIX:resource:$type:$url => ['role' => $role]
     *
     * @param $url
     * @param $type
     *
     * @return string
     */
    public function getResourceBelongsRole($url, $type)
    {
        switch ($type) {
            case 'menu':
            default:
                $resourceBelongsRole = $this->getMenuBelongsRoleByUrl($url);
        }

        return $resourceBelongsRole;
    }


    /**
     * 判断$userId是否有$url的权限
     *
     * @param $userId
     * @param $urlParam
     * @param $type
     *
     * @return bool
     */
    public function isGranted($userId, $urlParam, $type = 'menu')
    {
        $isGranted = false;
        if (is_scalar($urlParam)) {
            $url = $urlParam;
        } else {
            $url       = sprintf('%s/%s/%s', $urlParam['group'], $urlParam['module'], $urlParam['action']);
            $isGranted = $this->isGranted($userId, $url, $type);
            if (!$isGranted) { //具体的权限没有，尝试查看更高级别的权限是否存在
                $url = sprintf('%s/%s/*', $urlParam['group'], $urlParam['module']);
            } else {
                return true;
            }
        }

        if ($this->needSkipCheck($userId, $url, $type)) { //超级管理员或者公开资源 直接返回true
            return true;
        }

        $belongsRoleList = $this->getUserBelongsRoleList($userId);

        //请求资源相关信息
        $resourceBelongsRole = $this->getResourceBelongsRole($url, $type);
        if ($belongsRoleList) {
            if (is_scalar($resourceBelongsRole) && in_array($resourceBelongsRole, $belongsRoleList)) {
                $isGranted = true;
            } else if (is_array($resourceBelongsRole)) {
                $isGranted = (boolean)array_intersect($resourceBelongsRole, $belongsRoleList);//存在交集有权限，否则没有权限
            }
        }

        return $isGranted;
    }

    /**
     * 是否需要跳过权限检查
     *
     * @param        $userId
     * @param        $url
     * @param string $type
     *
     * @return bool
     */
    public function needSkipCheck($userId, $url, $type = 'menu')
    {
        return $this->isPublicResource($url, $type) || $this->isSuperAdmin($userId);
    }

    /**
     * 是否为超级管理员
     *
     * @param $userId
     *
     * @return bool
     */
    public function isSuperAdmin($userId)
    {
        $userData = $this->getUserData($userId);
        if (isset($userData['new_role_id']) && $userData['new_role_id'] == 2) {
            return true;
        }
        return false;
    }

    /**
     * 是否为公开资源
     *
     * @param $url
     * @param $type
     *
     * @return bool
     */
    public function isPublicResource($url, $type = 'menu')
    {
        $isPublicResource = $this->SmvcRedisHelper->hExists($this->generateResourceKey($type), $url);
        if (!$isPublicResource) { //redis里面没有 尝试查询db
            if (mt_rand(0, 10000) >= 9000) { //10%的概率查询db，查询到的话更新到redis PS:原理上来说redis里面存储有所有的信息
                $isPublicResource = (boolean)$this->PublicResourceDAO->getPublicResource($url, $type);
            }
        }
        return $isPublicResource;
    }

}
