<?php

/**
 * 权限相关 model
 *
 * @author : Jeff Liu<liuwy@imageco.com.cn>
 * @date   : 2017/05/27
 */
class UserGroupDAO extends RedisDBBase
{
    protected $tableName = 'tp_usergroup';
    protected $_pk = 'id';
    protected $_pk_auto = true;

    public function getUserRoleGroup($userId)
    {
        $sql = 'SELECT b.id,b.group_name FROM tp_user_group_relation a
                INNER JOIN tp_usergroup b  ON g.id=b.gid 
                WHERE a.uid=' . $userId;

        $data    = $this->query($sql);
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
     * @param $gid
     *
     * @return mixed
     */
    public function getRealatedRole($gid)
    {
        $sql  = 'SELECT `rid` FROM `tp_usergroup_role_relation` WHERE `gid`=' . $gid;
        $res  = $this->query($sql);
        $data = [];
        if ($res && is_array($res)) {
            foreach ($res as $info) {
                $data[] = $info['rid'];
            }
        }
        return $data;
    }

    /**
     * @param $userId
     *
     * @return array
     */
    public function getRealatedRoleByUserId($userId)
    {
        $sql  = 'SELECT `rid` FROM `tp_usergroup_role_relation` WHERE `gid` IN (SELECT `gid` FROM `tp_user_group_relation` WHERE `uid`=' . $userId . ')';
        $res  = $this->query($sql);
        $data = [];
        if ($res && is_array($res)) {
            foreach ($res as $info) {
                $data[] = $info['rid'];
            }
        }
        return $data;
    }

    /**
     * @param $gid
     * @param $relateRoleList
     *
     * @return bool
     */
    public function relateRole($gid, $relateRoleList)
    {
        $sql = 'DELETE FROM `tp_usergroup_role_relation` WHERE `gid`=' . $gid;
        $ret = $this->execute($sql);
        if ($ret !== false) {
            $sql    = 'INSERT INTO `tp_usergroup_role_relation`(`gid`,`rid`) VALUES';
            $tmpSql = [];
            foreach ($relateRoleList as $relateRole) {
                $tmpSql[] = sprintf('(%d, %d)', $gid, $relateRole);
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