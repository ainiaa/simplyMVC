<?php

/**
 * 分类
 * Class CategoryService
 * @author Jeff.Liu<liuwy@imageco.com.cn>
 */
class CategoryService
{
    /**
     * @var CategoryDAO
     */
    public $CategoryDAO;


    /**
     * @author Jeff Liu
     */
    public function getCategoryList()
    {
        return $this->CategoryDAO->getAll();
    }

    /**
     * @param $originData
     *
     * @return array
     */
    public function buildCategoryData($originData)
    {
        $defaultData        = $this->getCategoryDefaultData();
        $data               = array_merge($defaultData, $originData);
        $data['created_at'] = time();
        $data['created_by'] = '';//todo 获得当前用户信息
        return $data;
    }

    public function getDbError()
    {
        return $this->CategoryDAO->error();
    }

    /**
     * 设置默认值
     * @return array
     */
    public function getCategoryDefaultData()
    {
        return array(
                'name'       => '',
                'desc'       => '',
                'parent_id'  => 0,
                'path'       => '',
                'order_by'   => '',
                'created_by' => '',
                'created_at' => '',
                'updated_by' => '',
                'updated_at' => '',
        );
    }

    /**
     * 新增分类
     *
     * @param $data
     *
     * @return int
     */
    public function addCategory($data)
    {
        return $this->CategoryDAO->add($data);
    }

    public function getValidParents($id)
    {
        return $this->CategoryDAO->getValidParents($id);
    }
}


