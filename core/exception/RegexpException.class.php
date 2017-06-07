<?php

/**
 * The exception that indicates error of the last Regexp execution.
 */
class RegexpException extends Exception
{
    static public $messages = [
            PREG_INTERNAL_ERROR        => 'Internal error',
            PREG_BACKTRACK_LIMIT_ERROR => 'Backtrack limit was exhausted',
            PREG_RECURSION_LIMIT_ERROR => 'Recursion limit was exhausted',
            PREG_BAD_UTF8_ERROR        => 'Malformed UTF-8 data',
            5                          => 'Offset didn\'t correspond to the begin of a valid UTF-8 code point',
        // PREG_BAD_UTF8_OFFSET_ERROR
    ];

    public function __construct($message, $code = null, $pattern = null)
    {
        if (!$message) {
            $message = (isset(self::$messages[$code]) ? self::$messages[$code] : 'Unknown error') . ($pattern ? " (pattern: $pattern)" : '');
        }
        parent::__construct($message, $code);
    }

}