<?php
/**
 * db 分表分库配置
 */
return array(
        'db'    => array(//db 分表
                'table'  => array(//分表相关配置
                        'tableName' => array(
                                'sliceCount' => '10',//切片数量 (分成10张表)
                                'slicePK'    => 'uid',//切片依据(根据uid散列)
                        ),
                ),
                'schema' => array(//分库相关配置
                        'sliceCount' => '3',//切片数量 (分成3个数据库)
                        'slicePK'    => 'uid',//切片依据(根据uid散列)
                ),
        ),
        'mc'    => array(//mc 分布
                'sliceCount' => '3',//切片数量 (分成3个部分)
                'slicePK'    => 'uid',//切片依据(根据uid散列)
        ),
        'redis' => array(
                'sliceCount' => '3',//切片数量 (分成3个部分)
                'slicePK'    => 'uid',//切片依据(根据uid散列)
        ),
);