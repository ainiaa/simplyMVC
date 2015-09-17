<?php

class PostService extends BaseService
{

    /**
     * @var PostDAO
     */
    public $PostDAO;


    /**
     * 管理员列表
     * @author Jeff Liu
     */
    public function getList()
    {
        $PostList = $this->PostDAO->getAll();

        return $PostList;
    }

}