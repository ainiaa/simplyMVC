<?php

class TaxonomyService extends BaseService {

    /**
     * @var PostDAO
     */
    public $PostDAO;


    /**
     * @author Jeff Liu
     */
    public function getList() {
        $PostList = $this->PostDAO->getAll();

        return $PostList;
    }

}