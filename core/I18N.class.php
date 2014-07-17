<?php

/**
 * php-i18n
 *
 * I18N class for translating text to any language
 *
 *    Usage: I18N::t("user.name")  # =>  "Karl Metum"
 *    In the above example "name" is nested under "user"
 *
 * Make sure that the following constants are set in
 * your environment somehow:
 *
 *    - DEFAULT_LOCALE = ""
 *    - DIR_LOCALE = ""    # full path to your locales
 */
abstract class I18N
{
    /**
     * Holds the current locale
     */
    private static $locale;

    /**
     * Holds the character which terms are seperated by
     */
    private static $termSeparator = ".";

    /**
     * Main construct
     *
     * @param locale
     */
    public function __construct($locale = null)
    {
        $this->setLocale($locale);
    }

    /**
     * Sets the current locale
     */
    private static function setLocale($locale = null)
    {
        !defined('DEFAULT_LOCALE') && define('DEFAULT_LOCALE', 'en_us');
        $locale       = is_null($locale) ? DEFAULT_LOCALE : $locale;
        self::$locale = $locale;
    }

    /**
     * Gets current locale
     */
    private static function getLocale()
    {
        return self::$locale;
    }

    /**
     * Translates given term
     */
    public static function _translate($term)
    {
        $parsedData = self::_getData();
        return self::find($term, $parsedData);
    }

    public static function _getData()
    {
        return array();
    }


    /**
     * Finds given term inside given array
     *
     * @param string $term       the text to examine and look for
     * @param array  $parsedData yaml parsed data
     *
     * @return string
     */
    private static function find($term, $parsedData)
    {
        $locale = self::getLocale();
        if (!empty($parsedData[$locale])) {
            $lastData = $parsedData[$locale];
            $terms    = explode(self::$termSeparator, $term);
            foreach ($terms as $keyword) {
                if (empty($lastData[$keyword])) {
                    return "Translation missing for {$locale}#{$term}";
                }
                $lastData = $lastData[$keyword];
            }
            return $lastData;
        }

        return "Translation missing for {$locale}";
    }

    /**
     * Translates given term
     *
     * @param string , the translation term
     * @param string , (optional) the language which the term
     *               should be translated into.
     *
     * @return string
     */
    public static function translate($term, $locale = null)
    {
        self::setLocale($locale);
        return self::_translate($term);
    }

    /**
     * Alias for @translate
     */
    public static function t($term, $locale = null)
    {
        return self::translate($term, $locale);
    }
}