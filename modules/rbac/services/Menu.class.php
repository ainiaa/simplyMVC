<?php

/**
 * 权限管理
 * Class YmRbac
 * @author Jeff.Liu<liuwy@imageco.com.cn>
 * @date   2017/06/06
 */
class MenuService extends BaseService
{


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
     * @param     $where
     * @param int $perPageNum
     *
     * @return mixed
     */
    public function getPageInfo($where, $perPageNum = 20)
    {
        $count    = $this->MenuDAO->getCount($where);

        $Page     = new \Page($count, $perPageNum); // 实例化分页类 传入总记录数和每页显示的记录数
        echo $count;exit;
        $pageShow = $Page->show(); // 分页显示输出
        $limit    = $Page->firstRow . ',' . $Page->listRows;
        return ['pageShow' => $pageShow, 'limit' => $limit];
    }
}
