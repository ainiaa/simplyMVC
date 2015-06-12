<?php

/**
 * 数据库相关 DAO
 * @author  Jeff Liu
 *
 * @version 0.1
 *
 * Class BaseDBDAO
 */
class UserSplitService extends BaseService
{

    /**
     * @var UserSplitDBDAO
     */
    public $UserSplitDBDAO;

    public function __construct()
    {
        parent::__construct();
    }

    public function getUserSplit($uId)
    {
        $where = array(
                'id' => $uId
        );

        return $this->UserSplitDBDAO->getOne('*', $where);
    }

    /**
     * todo 这个可以做一个数据校验
     *
     * @param $info
     *
     * @return int
     */
    public function setUserSplit($info)
    {
        return $this->UserSplitDBDAO->add($info);
    }
}
