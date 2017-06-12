<?php

class TermsService extends BaseService
{

    /**
     * @var TermsDAO
     */
    public $TermsDAO;


    /**
     * @author Jeff Liu
     */
    public function getList()
    {
        $adminInfo = $this->TermsDAO->getAll();
        return $adminInfo;
    }

}