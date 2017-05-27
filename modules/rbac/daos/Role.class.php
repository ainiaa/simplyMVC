<?php

/**
 * 权限相关 model
 *
 * @author : Jeff Liu<liuwy@imageco.com.cn>
 * @date   : 2017/05/27
 */
class RoleDAO extends RedisDBBase
{
    protected $tableName = 'tp_role';
    protected $_pk = 'id';
    protected $_pk_auto = true;

    /**
     * @param $pids
     *
     * @return array
     */
    public function getRoleByPrivilege($pids)
    {
        $sql = '';
        if (is_scalar($pids)) {
            $sql = 'SELECT DISTINCT rid as id FROM `tp_role_privilege_relation` where pid=' . $pids;
        } else if (is_array($pids)) {
            $pids = implode(',', $pids);
            $sql  = 'SELECT DISTINCT rid as id FROM `tp_role_privilege_relation` where pid in(' . $pids . ')';
        }
        $data = [];
        if ($sql) {
            $data = $this->query($sql);
        }

        $rIdList = [];
        if ($data) {
            foreach ($data as $index => $datum) {
                $rIdList[] = $datum['id'];
                unset($data[$index]);
            }
        }
        return $rIdList;
    }


    /**
     * @param $id
     *
     * @return mixed
     */
    public function getRealatedPrivilege($id)
    {
        $sql  = 'SELECT `pid` FROM `tp_role_privilege_relation` WHERE `rid`=' . $id;
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
        $sql = 'DELETE FROM `tp_role_privilege_relation` WHERE `rid`=' . $id;
        $ret = $this->execute($sql);
        if ($ret !== false) {
            $sql    = 'INSERT INTO `tp_role_privilege_relation`(`rid`,`pid`) VALUES';
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


    /**
     * @param $id
     *
     * @return mixed
     */
    public function getRealatedMenuPrivilege($id)
    {
        $sql = 'SELECT `mpid`,`mid`,`pid` FROM `tp_role_menu_privilege_relation` WHERE `rid`=' . $id;
        return $this->query($sql);
    }

    /**
     * @param $id
     * @param $relatePrivilegeList
     *
     * @return bool
     */
    public function relateMenuPrivilege($id, $relateMenuPrivilegeList)
    {
        $sql = 'DELETE FROM `tp_role_menu_privilege_relation` WHERE `rid`=' . $id;
        $ret = $this->execute($sql);
        if ($ret !== false) {
            $sql    = 'INSERT INTO `tp_role_menu_privilege_relation`(`rid`,`mid`, `pid`) VALUES';
            $tmpSql = [];
            foreach ($relateMenuPrivilegeList as $relateMenuPrivilege) {
                list($mid, $pid) = explode(',', $relateMenuPrivilege);
                $tmpSql[] = sprintf('(%d, %d, %d)', $id, $mid, $pid);
            }
            $sql .= implode(',', $tmpSql);
            $ret = $this->execute($sql);
            if ($ret > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getMenuPrivilegeList()
    {
        $sql = <<<DOT
            SELECT t1.*,t2.menu_name,t3.priv_name FROM `tp_menu_privilege_relation` t1
            LEFT JOIN `tp_menu` t2 ON t1.mid=t2.id
            LEFT JOIN `tp_privilege` t3 ON t1.pid=t3.id
            ORDER by t1.mid,t1.pid ASC 
      
DOT;
        return $this->query($sql);
    }
}