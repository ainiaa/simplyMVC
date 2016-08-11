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
        $defaultData = $this->getCategoryDefaultData();
        $data        = array_merge($defaultData, $originData);
        if (!isset($originData['created_at'])) {
            $data['created_at'] = time();
        }
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
        return [
                'name'       => '',
                'desc'       => '',
                'parent_id'  => 0,
                'path'       => '',
                'order_by'   => '',
                'created_by' => '',
                'created_at' => '',
                'updated_by' => '',
                'updated_at' => '',
        ];
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

    /**
     * @param $id
     *
     * @return array|null
     */
    public function getCategoryInfo($id)
    {
        if (is_numeric($id) && $id > 0) {
            return $this->CategoryDAO->getCategoryInfo($id);
        }
        return null;
    }

    public function generateParentsSelector($id)
    {
        $validParents = $this->getValidParents($id);
        $selectorHtml = <<< HTML
            <select name="parentid" id="parentid">
                <option value="0">--请选择--</option> 
HTML;

        if ($validParents && is_array($validParents)) {
            foreach ($validParents as $index => $validParent) {
                if ($validParent['depth'] > 0) {
                    $selectorHtml .= sprintf(
                            '<option value="%s">%s</option>',
                            $validParent['id'],
                            '|' . str_repeat('-', $validParent['depth']) . $validParent['name']
                    );
                } else {
                    $selectorHtml .= sprintf(
                            '<option value="%s">%s</option>',
                            $validParent['id'],
                            $validParent['name']
                    );
                }

            }
        }
        $selectorHtml .= '</select>';

        return $selectorHtml;
    }
}


