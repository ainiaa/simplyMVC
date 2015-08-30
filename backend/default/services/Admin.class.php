<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/30
 * Time: 22:40
 */
class AdminService extends BaseService
{

    public function __construct()
    {
        parent::__construct();
        $this->AdminDAO = new AdminDAO();
    }

    /**
     * @var AdminDAO
     */
    public $AdminDAO;

    public function getAdminInfoByUserName($userName)
    {
        $adminInfo = $this->AdminDAO->getOne(array('user_name', 'password', 'email'),array('user_name' => $userName));
        return $adminInfo;
    }

}