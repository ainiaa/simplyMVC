<?php


/**
 * php- json i18n
 *
 * I18N class for translating text to any language
 *
 *    Usage: JsonI18N::t("user.name")  # =>  "Karl Metum"
 *    In the above example "name" is nested under "user"
 *
 * Make sure that the following constants are set in
 * your environment somehow:
 *
 *    - DEFAULT_LOCALE = ""
 *    - DIR_LOCALE = ""    # full path to your locales
 */
class JsonI18N extends I18N
{

    public function _getData()
    {
        if (empty($this->directory)) {
            self::setDirectory(ROOT_PATH . '/i18n/');
        }

        $filename = sprintf("%s/%s/%s.json", $this->getDirectory(), $this->locale, $this->fileName);

        if (file_exists($filename)) {
            $jsonString = file_get_contents($filename);
            return json_decode($jsonString, true);
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

if (!function_exists('LJ')) {
    function LJ($key, $lang = '')
    {
        return JsonI18N::t($key, $lang);
    }
}