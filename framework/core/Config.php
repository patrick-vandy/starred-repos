<?php

namespace framework\core;


/**
 * Global config values.
 *
 * @package framework\core
 */
class Config
{

    private static $conf = [];


    /**
     * Set a config value
     *
     * @param $key
     * @param $value
     */
    public static function set($key, $value)
    {
        static::$conf[$key] = $value;
    }


    /**
     * Get a config value.
     *
     * If $exception is true then an exception is thrown if the value is not set. If
     * it is false and the value is not set then $default is returned.
     *
     * @param $key
     * @param bool|true $exception
     * @param bool|false $default
     * @return mixed
     * @throws \Exception
     */
    public static function get($key, $exception = true, $default = false)
    {
        if (array_key_exists($key, static::$conf))
        {
            return static::$conf[$key];
        }
        if ($exception)
        {
            throw new \Exception("Config key [ $key ] is not set");
        }
        return $default;
    }


    /**
     * Return the entire config array
     *
     * @return array
     */
    public static function get_all()
    {
        return static::$conf;
    }


}