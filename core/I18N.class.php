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
     */
    protected function setLocale($locale = null)
    {
        !defined('DEFAULT_LOCALE') && define('DEFAULT_LOCALE', 'en_us');
        $locale       = is_null($locale) ? DEFAULT_LOCALE : $locale;
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
     */
    public function _translate($term)
    {
        $parsedData = $this->_getData();
        SmvcDebugHelper::instance()->debug(
                array(
                        'info'  => $parsedData,
                        'label' => '$parsedData ',
                        'level' => 'info',
                )
        );
        return $this->find($term, $parsedData);
    }

    public function _getData()
    {
        SmvcDebugHelper::instance()->debug(
                array(
                        'info'  => $this->locale,
                        'label' => '$locale  xx ',
                        'level' => 'info',
                )
        );
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
            SmvcDebugHelper::instance()->debug(
                    array(
                            'info'  => $keyword,
                            'label' => '$keyword',
                            'level' => 'info',
                    )
            );
            if (empty($parsedData[$keyword])) {
                return "Translation missing for #{$keyStr}";
            } else {
                $parsedData = $parsedData[$keyword];
            }
            SmvcDebugHelper::instance()->debug(
                    array(
                            'info'  => $parsedData,
                            'label' => '$parsedData',
                            'level' => 'info',
                    )
            );
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
     * @internal     param $string , the translation term
     * @internal     param $string , (optional) the language which the term
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
            SmvcDebugHelper::instance()->debug(
                    array(
                            'info'  => $className,
                            'label' => '$className ',
                            'level' => 'info',
                    )
            );
            self::$instance[$className] = new $className();
        }

        return self::$instance[$className];
    }

    /**
     * Alias for @translate
     */
    public static function t($term, $locale = null)
    {
    }
}