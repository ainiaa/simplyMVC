<?php


/**
 * php- xml i18n
 *
 * I18N class for translating text to any language
 *
 *    Usage: XmlI18N::t("user.name")  # =>  "Karl Metum"
 *    In the above example "name" is nested under "user"
 *
 * Make sure that the following constants are set in
 * your environment somehow:
 *
 *    - DEFAULT_LOCALE = ""
 *    - DIR_LOCALE = ""    # full path to your locales
 */
class XmlI18N extends I18N
{

    public function _getData()
    {
        if (empty($this->directory)) {
            self::setDirectory(ROOT_PATH . '/i18n/');
        }

        $filename = sprintf("%s/%s/%s.xml", $this->getDirectory(), $this->locale, $this->fileName);

        if (file_exists($filename)) {
            $xml = simplexml_load_file($filename);
            return $this->xml2array($xml);
        } else {
            die(sprintf("localization file %s does not exist!", $filename));
        }
    }

    /**
     * based on https://github.com/liujingyu/Xml2Array
     * PS: https://github.com/touv/xml_array
     *     https://github.com/stevleibelt/php_component_converter
     *     https://github.com/djsipe/php_xmlconverter/tree/master/
     *
     * @param SimpleXMLElement $xml
     *
     * @return array
     */
    function xml2array($xml)
    {
        $result = array();
        foreach ($xml as $k => $child) {
            if ($child->count()) {
                $tmpstr    = $child->asXML();
                $tmpresult = ('' == trim((string)$child)) ? $this->xml2array($child) : array_merge(
                        array("#text" => trim((string)$child)),
                        $this->xml2array($child)
                );
                uasort(
                        $tmpresult,
                        function ($a, $b) use ($tmpstr) {
                            $i = function () use ($tmpstr, $a) {
                                if (is_string($a)) {
                                    return strpos($tmpstr, $a);
                                } elseif (is_array($a) && $a != null) {
                                    return strpos($tmpstr, array_pop($a));
                                }
                            };
                            $j = function () use ($tmpstr, $b) {
                                if (is_string($b)) {
                                    return strpos($tmpstr, $b);
                                } elseif (is_array($b) && $b != null) {
                                    return strpos($tmpstr, array_pop($b));
                                }
                            };
                            return $i >= $j ? 1 : -1;
                        }
                );
                $result[$k] = $tmpresult;
            } else {
                if (isset($result[$child->getName()])) {
                    if (is_array($result[$child->getName()])) {
                        array_push($result[$child->getName()], (string)$child);
                    } else {
                        $tmp                       = $result[$child->getName()];
                        $result[$child->getName()] = array($tmp, (string)$child);
                    }
                } else {
                    $result[$k] = trim((string)$child);
                }
            }
        }
        return $result;
    }


    /**
     * Alias for @translate
     */
    public static function t($term, $locale = null, $fileName = null)
    {
        return self::instance(__CLASS__)->translate($term, $locale, $fileName);
    }
}

if (!function_exists('LX')) {
    function LX($key, $lang)
    {
        return XmlI18N::t($key, $lang);
    }
}