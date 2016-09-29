<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/30
 * Time: 22:40
 */
class AdminService extends BaseService
{

    /**
     * @var AdminDAO
     */
    public $AdminDAO;

    /**
     * @author Jeff Liu
     * @param $userName
     *
     * @return array
     */
    public function getAdminInfoByUserName($userName)
    {
        $adminInfo = $this->AdminDAO->getOne(array('user_name', 'password', 'email'),array('user_name' => $userName));
        return $adminInfo;
    }


    /**
     * @author Jeff Liu
     */
    public function getAdminList()
    {
        $adminInfo = $this->AdminDAO->getAll();
        return $adminInfo;
    }

}