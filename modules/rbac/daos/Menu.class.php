<?php

/**
 * 权限相关 model
 *
 * @author : Jeff Liu<liuwy@imageco.com.cn>
 * @date   : 2017/05/27
 */

/**
 * Class PMenuRedisModel
 *
 * redis 存储数据结构为
 * PREFIX:menu:$url => ['menu_name' =>'', 'pid' => '', 'priv' => 'priv_id']
 */
class MenuDAO extends RedisDBBase
{
    protected $tableName = 'tp_menu';
    protected $_pk = 'id';
    protected $_pk_auto = true;

    public function getMenuBelongsRoleByUrl($url)
    {
        $rIdList   = [];
        $menu      = $this->getOne(['menu_url' => $url]);
        $sql       = 'SELECT `id` FROM `tp_privilege` WHERE `priv_name`="access_menu" AND `priv_type`="MENU" LIMIT 1';
        $privilege = $this->query($sql);
        $mId       = isset($menu['id']) ? $menu['id'] : '';
        $pid       = isset($privilege['id']) ? $privilege['id'] : '';
        if ($mId) {
            $sql  = sprintf(
                    'SELECT rid FROM `tp_role_menu_privilege_relation` WHERE `mid`=%d AND `pid`=%d',
                    $mId,
                    $pid
            );
            $data = $this->query($sql);

            if ($data) {
                foreach ($data as $index => $datum) {
                    $rIdList[] = $datum['rid'];
                    unset($data[$index]);
                }
            }
        }

        return $rIdList;
    }

    public function getPrivByUrl($url)
    {
        $sql     = 'SELECT p.id FROM `' . $this->tableName . '` m LEFT JOIN tp_menu_privilege_relation rr ON m.id=rr.`mid` LEFT JOIN tp_privilege p ON p.id=rr.pid WHERE m.menu_url="' . $url . '"';
        $data    = $this->query($sql);
        $pIdList = [];
        if ($data) {
            foreach ($data as $index => $datum) {
                $pIdList[] = $datum['id'];
                unset($data[$index]);
            }
        }
        return $pIdList;
    }

    /**
     * @param        $where
     * @param string $limit
     *
     * @return mixed
     */
    public function getFullData($where, $limit = '')
    {
        $wh = '';
        if ($where) {
            foreach ($where as $index => $item) {
                $wh = 'a.' . $index;
                if (is_scalar($item)) {
                    $wh .= ' ' . $item;
                } else if (is_array($item)) {
                    $wh .= ' ' . implode(' ', $item);
                }
            }
        }
        if ($limit) {
            $limit = ' LIMIT ' . $limit;
        }
        $sql = 'SELECT a.*,b.menu_name as pmenu_name FROM `' . $this->tableName . '` a LEFT JOIN `' . $this->tableName . '` b ON a.pid=b.id ' . $wh . ' ' . $limit;
        return $this->query($sql);
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function getRealatedPrivilege($id)
    {
        $sql  = 'SELECT `pid` FROM `tp_menu_privilege_relation` WHERE `mid`=' . $id;
        $res  = $this->query($sql);
        $data = [];
        if ($res && is_array($res)) {
            foreach ($res as $info) {
                $data[] = $info['pid'];
            }
        }
        return $data;
    }

    /**
     * @param $id
     * @param $relatePrivilegeList
     *
     * @return bool
     */
    public function relatePrivilege($id, $relatePrivilegeList)
    {
        $sql = 'DELETE FROM `tp_menu_privilege_relation` WHERE `mid`=' . $id;
        $ret = $this->execute($sql);
        if ($ret !== false) {
            $sql    = 'INSERT INTO `tp_menu_privilege_relation`(`mid`,`pid`) VALUES';
            $tmpSql = [];
            foreach ($relatePrivilegeList as $relatePrivilege) {
                $tmpSql[] = sprintf('(%d, %d)', $id, $relatePrivilege);
            }
            $sql .= implode(',', $tmpSql);
            $ret = $this->execute($sql);
            if ($ret > 0) {
                return true;
            }
        }
        return false;
    }
}