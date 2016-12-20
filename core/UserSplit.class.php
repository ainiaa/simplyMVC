<?php

/**
 * Class UserSplit
 */
class UserSplit
{
    private static $userSplitLib;

    public static $splitLibMapSwitch = true;

    /**
     * 获得userSplitLib
     *
     * @param $uId
     *
     * @return mixed
     */
    public static function getUserSplitLib($uId)
    {
        if (!isset(self::$userSplitLib[$uId])) {
            self::$userSplitLib[$uId] = self::getSplitLib($uId);
        }
        return self::$userSplitLib[$uId];
    }


    /**
     * 获得最终的 split配置项
     * @return mixed
     */
    public static function getRealSplitLib()
    {
        $uId = 0;//todo 这个需要处理
        return self::getUserSplitLib($uId);
    }

    /**
     * @param $key
     *
     * @return array|null
     */
    public static function getSplitLib($key)
    {
        if (empty(self::$splitLibMapSwitch)) {
            return null;
        }

        return self::calcSplitLib($key);

    }

    /**
     * todo
     * @param $key
     */
    public static function getPersistUserSplit($key)
    {
        $dbConf        = C('db.split');
        $currentDbConf = $dbConf[array_rand($dbConf)];
    }

    /**
     * @param $key
     *
     * @return array
     */
    private static function calcSplitLib($key)
    {
        $dbCount    = count(C('db.master'));
        $redisCount = count(C('redis'));

        $dbNo    = self::getIdByHash($key, $dbCount);
        $redisNo = self::getIdByHash($key, $redisCount);
        return ['db' => $dbNo, 'redis' => $redisNo];
    }

    /**
     * @param $key
     * @param $num
     *
     * @return int
     */
    private static function getIdByHash($key, $num)
    {
        $md5Id  = md5($key);
        $decNum = hexdec(substr($md5Id, -2));
        $result = $decNum % $num;
        return $result;
    }
}