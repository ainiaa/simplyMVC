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
    protected $locale;

    /**
     * Holds the current directory
     */
    protected $directory;

    /**
     * Holds the current file name
     */
    protected $fileName;

    /**
     * Holds the character which terms are seperated by
     */
    protected $termSeparator = ".";

    private static $instance;


    /**
     * Main construct
     *
     * @param locale
     */
    public function __construct($locale = null)
    {
        $this->setLocale($locale);
        $this->dirctory = '';
    }

    /**
     * sets a new directory where i18n is searching for files
     *
     * @param string $directory
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;
    }

    /**
     * gets the directory where i18n is searching in
     */
    public function getDirectory()
    {
        return $this->directory;
    }



    /**
     * Sets the current locale
     *
     * @param null $locale
     */
    protected function setLocale($locale = null)
    {
        $locale       = is_null($locale) ? C('defaultLocal') : $locale;
        $this->locale = $locale;
    }

    /**
     * Gets current locale
     */
    protected function getLocale()
    {
        return $this->locale;
    }

    /**
     * Translates given term
     *
     * @param $term
     *
     * @return string
     */
    public function _translate($term)
    {
        $parsedData = $this->_getData();
        return $this->find($term, $parsedData);
    }

    public function _getData()
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
    private function find($term, $parsedData)
    {
        $terms = explode($this->termSeparator, $term);

        $keyStr = '';
        foreach ($terms as $keyword) {
            $keyStr .= '.' . $keyword;
            if (empty($parsedData[$keyword])) {
                return "Translation missing for #{$keyStr}";
            } else {
                $parsedData = $parsedData[$keyword];
            }
        }
        return $parsedData;

    }

    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Translates given term
     *
     * @param      $term
     * @param null $locale
     * @param null $fileName
     *
     *               should be translated into.
     *
     * @return string
     */
    public function translate($term, $locale = null, $fileName = null)
    {
        $this->setLocale($locale);
        if (is_null($fileName)) {
            $fileName = $this->getLocale();
        }
        $this->setFileName($fileName);
        return $this->_translate($term);
    }

    /**
     * @param $className
     *
     * @return $this
     */
    public static function instance($className)
    {
        if (empty(self::$instance[$className])) {
            self::$instance[$className] = new $className();
        }

        return self::$instance[$className];
    }

    /**
     * Alias for @translate
     *
     * @param      $term
     * @param null $locale
     */
    public static function t($term, $locale = null)
    {
    }
}