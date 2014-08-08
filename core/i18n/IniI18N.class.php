<?php

// TODO  https://github.com/gr4y/i18n/blob/master/i18n.php  按照这个来。。。  重新修改 I18N
//todo  https://github.com/appleboy/php-i18n/blob/master/libraries/Language.php
//todo l10n https://github.com/dotroll/I18N/blob/master/src/I18N/Language.php
//todo https://github.com/Philipp15b/php-i18n/blob/master/i18n.class.php


/**
 * php- ini i18n
 *
 * I18N class for translating text to any language
 *
 *    Usage: IniI18N::t("user.name")  # =>  "Karl Metum"
 *    In the above example "name" is nested under "user"
 *
 * Make sure that the following constants are set in
 * your environment somehow:
 *
 *    - DEFAULT_LOCALE = ""
 *    - DIR_LOCALE = ""    # full path to your locales
 */
class IniI18N extends I18N
{

    public function _getData()
    {
        if (empty($this->directory)) {
            self::setDirectory(ROOT_PATH . '/i18n/');
        }

        $filename = sprintf("%s/%s/%s.ini", $this->getDirectory(), $this->locale, $this->fileName);

        if (file_exists($filename)) {
            return parse_ini_file($filename, true);
        } else {
            die(sprintf("localization file %s does not exist!", $filename));
        }
    }

    /**
     * Alias for @translate
     */
    public static function t($term, $locale = null, $fileName = null)
    {
        return self::instance(__CLASS__)->translate($term, $locale, $fileName);
    }
}

if (!function_exists('LI')) {
    function LI($key, $lang = '')
    {
        return IniI18N::t($key, $lang);
    }
}