<?php

class PostService extends BaseService
{

    /**
     * @var PostDAO
     */
    public $PostDAO;


    /**
     * ����Ա�б�
     * @author Jeff Liu
     */
    public function getList()
    {
        $PostList = $this->PostDAO->getAll();

        return $PostList;
    }

}