<?php

class TermsService extends BaseService
{

    /**
     * @var TermsDAO
     */
    public $TermsDAO;


    /**
     * ����Ա�б�
     * @author Jeff Liu
     */
    public function getList()
    {
        $adminInfo = $this->TermsDAO->getAll();
        return $adminInfo;
    }

}