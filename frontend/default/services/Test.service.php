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
     * @var TestDBDAO
     */
    public $TestDAO;

    public function getAll()
    {
        $where = array(
                "id[<]" => 3
        );
        return $this->TestDAO->getAll('*', $where);
    }
}