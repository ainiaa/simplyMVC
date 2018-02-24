<?php
/**
 * api
 */
return [
        'API_SERVICE' => [
                'category' => [
                        'getList' => [
                                'file'    => ROOT_DIR . 'modules' . DS . 'category' . DS . 'services' . DS . 'CategoryApi.class.php',
                                'service' => 'CategoryService',
                                'method'  => 'getCategoryList',
                        ],
                ],
        ],
];