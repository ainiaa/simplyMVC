<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 13-3-13
 * Time: 下午10:03
 * To change this template use File | Settings | File Templates.
 */
class TestService extends BaseService
{
    /**
     * @var TestDAO
     */
    public $TestDAO;

    public function getAll()
    {
        return $this->TestDAO->getAll('*');
    }
}