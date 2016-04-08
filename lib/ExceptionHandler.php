<?php

namespace pyatakss\sendmail;

class ExceptionHandler
{
    /**
     * @var array
     */
    private static $exceptions = [];

    /**
     * Collect exceptions
     * ExceptionHandler::collect(__CLASS__, '', __FILE__, __LINE__);
     * 
     * @param $class
     * @param $exception
     * @param $file
     * @param $line
     * 
     * return void
     */
    public static function collect($class, $exception, $file, $line)
    {
        self::$exceptions[] = $class . ': ' . $exception . ' in ' . PHP_EOL . '# ' . $file . ' ' . $line;
    }

    /**
     * Set exception strict
     * 
     * @param $exception
     */
    public static function set($exception)
    {
        self::$exceptions[] = $exception;
    }

    /**
     * @return array
     */
    public static function get()
    {
        return self::$exceptions;
    }
}