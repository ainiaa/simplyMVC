<?php

class PostService extends BaseService
{

    /**
     * @var PostDAO
     */
    public $PostDAO;


    /**
     * @author Jeff Liu
     */
    public function getPostList()
    {
        return $this->PostDAO->getAll();
    }

    public function getOnePost($where)
    {
        return $this->PostDAO->getOne($where);
    }

    public function addPost($data)
    {
        return $this->PostDAO->add($data);
    }

    /**
     * @param $data
     * @param $where
     *
     * @return int
     */
    public function updatePost($data, $where)
    {
        return $this->PostDAO->update($data, $where);
    }

    public function getLastSql()
    {
        return $this->PostDAO->getSqlList();
    }

    public function getDbError()
    {
        return $this->PostDAO->error();
    }

    /**
     * @param $originData
     *
     * @return array
     */
    public function buildPostData($originData)
    {
        $defaultData          = $this->getPostDefaultData();
        $data                 = array_merge($defaultData, $originData);
        $data['post_date']    = date('Y-m-d H:i:s');
        $data['post_author']  = '';//todo 获得当前用户信息
        $data['post_excerpt'] = substr($data['post_content'], 0, 120);//todo 需要处理
        return $data;
    }

    public function getPostDefaultData()
    {
        return array(
                'post_author'    => '',
                'post_content'   => '',
                'post_title'     => '',
                'post_excerpt'   => '',
                'post_status'    => 'publish',
                'comment_status' => 'open',
                'ping_status'    => 'open',
                'post_password'  => '',
                'post_name'      => '',
                'to_ping'        => '',
                'pinged'         => '',
        );
    }

}