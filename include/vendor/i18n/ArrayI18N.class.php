<?php


/**
 * php- array i18n
 *
 * I18N class for translating text to any language
 *
 *    Usage: ArrayI18N::t("user.name")  # =>  "Karl Metum"
 *    In the above example "name" is nested under "user"
 *
 * Make sure that the following constants are set in
 * your environment somehow:
 *
 *    - DEFAULT_LOCALE = ""
 *    - DIR_LOCALE = ""    # full path to your locales
 */
class ArrayI18N extends I18N
{

    public function _getData()
    {
        if (empty($this->directory)) {
            self::setDirectory(ROOT_DIR . 'i18n/');
        }

        $filename = sprintf("%s/%s/%s.php", $this->getDirectory(), $this->locale, $this->fileName);

        if (file_exists($filename)) {
            return include $filename;
        } else {
            die(sprintf("localization file %s does not exist!", $filename));
        }
    }

    /**
     * Alias for @translate
     *
     * @param      $term
     * @param null $locale
     * @param null $fileName
     *
     * @return string|void
     */
    public static function t($term, $locale = null, $fileName = null)
    {
        return self::instance(__CLASS__)->translate($term, $locale, $fileName);
    }
}

if (!function_exists('LA')) {
    function LA($key, $lang = '')
    {
        return ArrayI18N::t($key, $lang);
    }
}