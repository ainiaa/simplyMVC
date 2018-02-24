<?php

/**
 * 分类
 * Class CategoryService
 * @author Jeff.Liu<jeff.liu.guo@gmail.com>
 */
class CategoryApiService extends CategoryService
{
    public function getList()
    {
        return $this->getCategoryList();
    }
}


