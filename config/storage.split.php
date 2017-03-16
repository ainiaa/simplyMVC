<?php
/**
 * db 分表分库配置
 */
return [
        'dbSplit'      => [//db 分表
                'table'  => [//分表相关配置
                        'tableName' => [
                                'sliceCount' => '10',//切片数量 (分成10张表]
                                'slicePK'    => 'uid',//切片依据(根据uid散列]
                        ],
                ],
                'schema' => [//分库相关配置
                        'sliceCount' => '3',//切片数量 (分成3个数据库]
                        'slicePK'    => 'uid',//切片依据(根据uid散列]
                ],
        ],
        'mcSplit'      => [//mc 分布
                'sliceCount' => '3',//切片数量 (分成3个部分]
                'slicePK'    => 'uid',//切片依据(根据uid散列]
        ],
        'redisSplit'   => [
                'sliceCount' => '3',//切片数量 (分成3个部分]
                'slicePK'    => 'uid',//切片依据(根据uid散列]
        ],
        'useUserSplit' => true,//是否使用分库功能
];