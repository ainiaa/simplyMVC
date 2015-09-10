<?php

class TermsService extends BaseService
{

    /**
     * @var TermsDAO
     */
    public $TermsDAO;


    /**
     * 管理员列表
     * @author Jeff Liu
     */
    public function getList()
    {
        $adminInfo = $this->TermsDAO->getAll();
        return $adminInfo;
    }

}