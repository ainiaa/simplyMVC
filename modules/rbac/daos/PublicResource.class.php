<?php

/**
 * 权限相关 model
 *
 * @author : Jeff Liu<liuwy@imageco.com.cn>
 * @date   : 2017/05/27
 */
class PublicResourceDAO extends RedisDBBase
{
    protected $tableName = 'tp_public_resource';
    protected $_pk = 'id';
    protected $_pk_auto = true;

    /**
     * 查询是否为
     *
     * @param        $url
     * @param string $type
     *
     * @return bool
     */
    public function getPublicResource($url, $type = 'menu')
    {
        return $this->getOne(['resource_url' => $url, 'resource_type' => $type]);
    }
}