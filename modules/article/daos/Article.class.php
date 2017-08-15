<?php

/**
 * Class ArticleDAO
 */
class ArticleDAO extends BaseDBDAO
{
    protected $tableName = 'category';

    public function getValidParents($id)
    {
        $currentInfo = $this->getOne(['id' => $id]);
        $query       = sprintf(
                'SELECT * FROM `%s` WHERE`path` NOT LIKE \'%%,%s,%%\' ',
                'smvc_category',
                $currentInfo['path']
        );
        $q           = $this->query($query, 1);
        if ($q === false) {
            return $this->getError();
        } else {
            return $q->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    public function getCategoryInfo($id)
    {
        return $this->getOne(['id' => $id]);
    }
}