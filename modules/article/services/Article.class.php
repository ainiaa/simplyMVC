<?php

/**
 * @author Jeff.Liu<jeff.liu.guo@gmail.com>
 */
class ArticleService
{
    /**
     * @var ArticleDAO
     */
    public $ArticleDAO;


    /**
     * @author Jeff Liu
     */
    public function getArticleList()
    {
        return $this->ArticleDAO->getAll();
    }

    public function deleteArticleById($id)
    {
        return $this->ArticleDAO->delete(['id' => $id]);
    }

    /**
     * @param     $originData
     * @param int $id
     *
     * @return array
     */
    public function buildArticleData($originData, $id = 0)
    {
        if ($id) {
            $oldData = $this->getArticleInfo($id);
        } else {
            $oldData = $this->getArticleDefaultData();
        }

        $data = array_merge($oldData, $originData);
        if (!isset($originData['created_at'])) {
            $data['created_at'] = time();
        }
        return $data;
    }

    public function getDbError()
    {
        return $this->ArticleDAO->error();
    }

    /**
     * 设置默认值
     * @return array
     */
    public function getArticleDefaultData()
    {
        return [
                'post_title'     => '',
                'post_excerpt'   => '',
                'post_password'  => '',
                'post_content'   => '',
                'post_author'    => '',
                'post_status'    => '',
                'post_type'      => '',
                'comment_status' => '',
                'comment_count'  => '',
                'created_by'     => '',
                'created_at'     => '',
                'updated_by'     => '',
                'updated_at'     => '',
        ];
    }

    /**
     * 新增分类
     *
     * @param $data
     *
     * @return int
     */
    public function addArticle($data)
    {
        return $this->ArticleDAO->add($data);
    }

    /**
     * @param $data
     * @param $where
     *
     * @return int
     */
    public function updateArticle($data, $where)
    {
        $return = $this->ArticleDAO->update($data, $where);
        return $return;
    }

    /**
     * @param $id
     *
     * @return array
     */
    public function getValidParents($id)
    {
        return $this->ArticleDAO->getValidParents($id);
    }

    /**
     * @param $id
     *
     * @return array|null
     */
    public function getArticleInfo($id)
    {
        if (is_numeric($id) && $id > 0) {
            return $this->ArticleDAO->getCategoryInfo($id);
        }
        return null;
    }

    /**
     * @param int    $id
     * @param int    $selectedId
     * @param string $labelName
     * @param null   $labelId
     *
     * @return string
     */
    public function generateCategorySelector($id = -1, $selectedId = 0, $labelName = 'pid', $labelId = null)
    {
        $validParents = $this->getValidParents($id);
        if (empty($labelId)) {
            $labelId = $labelName;
        }
        $selectorHtml = <<< HTML
            <select name="{$labelName}" id="{$labelId}" class="form-control">
                <option value="0">--请选择--</option> 
HTML;

        if ($validParents && is_array($validParents)) {
            foreach ($validParents as $index => $validParent) {
                if ($validParent['id'] == $selectedId) {
                    $selected = 'selected="selected"';
                } else {
                    $selected = '';
                }
                if ($validParent['depth'] > 0) {
                    $selectorHtml .= sprintf(
                            '<option value="%s" %s>%s</option>',
                            $validParent['id'],
                            $selected,
                            '|' . str_repeat('-', $validParent['depth']) . $validParent['name']
                    );
                } else {
                    $selectorHtml .= sprintf(
                            '<option value="%s" %s>%s</option>',
                            $validParent['id'],
                            $selected,
                            $validParent['name']
                    );
                }
            }
        }
        $selectorHtml .= '</select>';

        return $selectorHtml;
    }
}


