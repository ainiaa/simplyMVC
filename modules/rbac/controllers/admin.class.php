<?php

/**
 * Admin
 * @author Jeff.Liu<jeff.liu.guo@gmail.com>
 * @date   2017/05/27
 */
class AdminController extends BackendController
{

    public function _initialize()
    {
        parent::_initialize();
        //判断权限
    }

    /**
     * @param array|int $validParents
     * @param string    $nameKey
     * @param int       $selectedId
     * @param string    $labelName
     * @param null      $labelId
     *
     * @return string
     */
    public function generateCategorySelector($validParents = -1, $selectedId = 0, $labelName = 'pid', $labelId = null, $nameKey = 'menu_name')
    {
        if (empty($labelId)) {
            $labelId = $labelName;
        }
        $selectorHtml = <<< HTML
            <select name="{$labelName}" id="{$labelId}">
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
                            '|' . str_repeat('-', $validParent['depth']) . $validParent[$nameKey]
                    );
                } else {
                    $selectorHtml .= sprintf(
                            '<option value="%s" %s>%s</option>',
                            $validParent['id'],
                            $selected,
                            $validParent[$nameKey]
                    );
                }
            }
        }
        $selectorHtml .= '</select>';

        return $selectorHtml;
    }
}
