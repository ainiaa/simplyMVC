<?php

/**
 * 获取configTipsInfo配置项信息
 *
 * @author Jeff Liu<liuwy@imageco.com.cn>
 */
class TipsInfoService extends BaseService
{

    /**
     * 根据编号获得对一个tipsInfo
     *
     * @author Jeff Liu
     *
     * @param        $no
     * @param string $returnKey
     *
     * @return mixed
     */
    public static function getMessageInfoByErrnoWithoutDefault($no, $returnKey = '')
    {
        return self::getMessageInfoByErrno($no, $returnKey, false);
    }

    /**
     *
     * @param $no
     *
     * @return mixed
     */
    public static function getMessageInfoErrorSoftTxtByNoWithoutDefault($no)
    {
        return self::getMessageInfoByErrnoWithoutDefault($no, 'errorSoftTxt');
    }



    /**
     * 根据编号获得对一个tipsInfo
     *
     * @author Jeff Liu
     *
     * @param        $no
     * @param string $returnKey
     * @param bool   $withDefault
     *
     * @return mixed
     */
    public static function getMessageInfoByErrno($no, $returnKey = '', $withDefault = true)
    {
        $tipsInfoList = C('tipsInfo');
        $tipsInfo     = [];
        if (isset($tipsInfoList[$no])) {
            $tipsInfo = $tipsInfoList[$no];
        } else if ($withDefault) {
            $tipsInfo = isset($tipsInfoList['default']) ? $tipsInfoList['default'] : $tipsInfo;
        }
        if ($returnKey) {
            $tipsInfo = isset($tipsInfo[$returnKey]) ? $tipsInfo[$returnKey] : $tipsInfo;
        }
        return $tipsInfo;
    }

    /**
     *
     * @param $no
     *
     * @return mixed
     */
    public static function getMessageInfoErrorSoftTxtByNo($no)
    {
        return self::getMessageInfoByErrno($no, 'errorSoftTxt');
    }

    /**
     *
     * @param $no
     *
     * @return mixed
     */
    public static function getMessageInfoErrorTxtByNo($no)
    {
        return self::getMessageInfoByErrno($no, 'errorTxt');
    }

    /**
     *
     * @param $no
     *
     * @return mixed
     */
    public static function getMessageInfoErrorImgByNo($no)
    {
        return self::getMessageInfoByErrno($no, 'errorImg');
    }
}

