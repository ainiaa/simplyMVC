<?php

/**
 * 权限相关 model
 *
 * @author : Jeff.Liu<jeff.liu.guo@gmail.com>
 * @date   : 2017/05/27
 */
class UserInfoDAO extends BaseDBDAO
{
    protected $tableName = 'tp_user';

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
     * @param $id
     *
     * @return mixed
     */
    public function getRealatedUserGroup($id)
    {
        $sql  = 'SELECT `gid` FROM `tp_user_group_relation` WHERE `uid`=' . $id;
        $res  = $this->query($sql);
        $data = [];
        if ($res && is_array($res)) {
            foreach ($res as $info) {
                $data[] = $info['gid'];
            }
        }
        return $data;
    }

    /**
     * @param $id
     * @param $relateUserGroupList
     *
     * @return bool
     */
    public function relateUserGroup($id, $relateUserGroupList)
    {
        $table = '`tp_user_group_relation`';
        $sql   = 'DELETE FROM ' . $table . ' WHERE `uid`=' . $id;
        $ret   = $this->execute($sql);
        if ($ret !== false) {
            $sql    = 'INSERT INTO ' . $table . '(`uid`,`gid`) VALUES';
            $tmpSql = [];
            foreach ($relateUserGroupList as $relateUserGroup) {
                $tmpSql[] = sprintf('(%d, %d)', $id, $relateUserGroup);
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
    public function getRealatedRole($id)
    {
        $sql  = 'SELECT `rid` FROM `tp_user_role_relation` WHERE `uid`=' . $id;
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
     * @param $id
     * @param $relateRoleList
     *
     * @return bool
     */
    public function relateRole($id, $relateRoleList)
    {
        $sql = 'DELETE FROM `tp_user_role_relation` WHERE `uid`=' . $id;
        $ret = $this->execute($sql);
        if ($ret !== false) {
            $sql    = 'INSERT INTO `tp_user_role_relation`(`uid`,`rid`) VALUES';
            $tmpSql = [];
            foreach ($relateRoleList as $relateRole) {
                $tmpSql[] = sprintf('(%d, %d)', $id, $relateRole);
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
     * @param $userId
     *
     * @return mixed
     */
    public function getRealatedRoleByUserId($userId)
    {
        $sql = 'SELECT b.id FROM tp_user_role_relation a
                INNER JOIN tp_role b ON b.id=a.rid 
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
}