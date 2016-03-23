<?php

/**
 * 图形验证码
 * Class ImageVerifyService
 * @author Jeff.Liu<liuwy@imageco.com.cn>
 */
class ImageVerifyService
{

    public static function buildImageCodeByParam(
            $verifyName = 'verify_cj',
            $length = 4,
            $mode = 1,
            $type = 'png',
            $width = 48,
            $height = 22
    ) {
        if (extension_loaded('imagick')) {
            import('@.ORG.Util.ImageBaseImagick');
            ImageBaseImagick::buildImageVerify($length, $mode, $type, $width, $height, $verifyName);
        } else {
            import('ORG.Util.Image');
            Image::buildImageVerify($length, $mode, $type, $width, $height, $verifyName);
        }
    }

    public static function buildImageCode()
    {
        if (extension_loaded('imagick')) {
            import('@.ORG.Util.ImageBaseImagick');
            ImageBaseImagick::buildImageVerify(
                    $length = 4,
                    $mode = 1,
                    $type = 'png',
                    $width = 48,
                    $height = 22,
                    $verifyName = 'verify_cj'
            );
        } else {
            import('ORG.Util.Image');
            Image::buildImageVerify(
                    $length = 4,
                    $mode = 1,
                    $type = 'png',
                    $width = 48,
                    $height = 22,
                    $verifyName = 'verify_cj'
            );
        }

    }

    /**
     * todo 还没有实现
     */
    public static function verifyImageCode()
    {

    }

}


