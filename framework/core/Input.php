<?php

namespace framework\core;


/**
 * Class Input
 *
 * Static class to handle input. The advantage to using this class
 * instead of PHP's super globals, such as $_REQUEST, is this prevents
 * the need to check if an input variable is set and provides an
 * easy method to define default values for input variables.
 *
 * Also, this prevents errors when running in CLI mode. It is not safe
 * to rely on the super globals being set, and this class removes that
 * concern.
 *
 * @package framework\core
 */
class Input
{

    private static $data = [
        'post' => [],
        'get' => [],
        'request' => [],
        'files' => []
    ];


    /**
     * @param $name
     * @param bool|false $default
     * @param string $type
     * @return mixed
     */
    public static function get($name, $default = false, $type = 'request')
    {
        if (isset(self::$data[$type][$name]))
        {
            return self::$data[$type][$name];
        }
        return $default;
    }


    /**
     * @param $type
     * @param $name
     * @param $value
     */
    public static function set($type, $name, $value)
    {
        self::$data[$type][$name] = $value;
    }


    /**
     * Initialize the input.
     */
    public static function process()
    {
        self::$data['files'] = isset($_FILES) ? $_FILES : [];
        self::$data['post'] = isset($_POST) ? $_POST : [];
        self::$data['get'] = isset($_GET) ? $_GET : [];
        self::$data['request'] = isset($_REQUEST) ? $_REQUEST : [];
    }

}