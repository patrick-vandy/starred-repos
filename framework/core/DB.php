<?php

namespace framework\core;


/**
 * Static database class using singleton pattern to make sure multiple connections
 * to the same database as the same user do not happen.
 *
 * If there is already a PDO instance for the connection provided that instance is
 * returned. Otherwise a new one is created.
 *
 * @package framework\core
 */
class DB
{

    private static $dbh = [];


    /**
     * Connect to database and return instance using singleton pattern. Connection
     * array can be left empty and the default values defined in config.php will be
     * used instead.
     *
     * Example connection array:
     *    $conn = [
     *       'host' => 'localhost',
     *       'dbname' => 'mydb',
     *       'user' => 'myuser',
     *       'password' => 'mypass',
     *       'port' => '6969',
     *    ]
     *
     * Config setting names (define in config.php):
     *    db_host
     *    db_name
     *    db_user
     *    db_pass
     *    db_port
     *
     * @param array $conn
     * @return PDO
     */
    public static function connect($conn = [])
    {
        $conn = static::_get_connection($conn);
        $dsn = static::_get_dsn($conn);
        $key = md5($conn['driver'] . $conn['host'] . $conn['dbname'] . $conn['user'] . $conn['password']);
        if (empty(static::$dbh[$key]))
        {
            static::$dbh[$key] = new PDO($dsn, $conn['user'], $conn['password']);
        }
        return static::$dbh[$key];
    }


    /**
     * Return a DSN for PDO based on the array of connection values
     * and driver provided.
     *
     * @param array $conn
     * @return string PDO DSN connection string
     */
    private static function _get_dsn($conn)
    {
        $dsn = $conn['driver'] . ':';
        foreach ($conn as $name => $value)
        {
            if ($conn['driver'] == 'pgsql' || ($name != 'user' && $name != 'password'))
            {
                $dsn .= "$name=$value;";
            }
        }
        return $dsn;
    }


    /**
     * Return an array of connection values, using default config settings
     * if the $conn parameter was not provided.
     *
     * @param array $conn
     * @return array connection values
     */
    private static function _get_connection($conn)
    {
        if (empty($conn))
        {
            $conn = [
                'driver' => Config::get('db_driver', true, 'pgsql'),
                'host' => Config::get('db_host'),
                'dbname' => Config::get('db_name'),
                'user' => Config::get('db_user'),
                'password' => Config::get('db_pass')
            ];
            $port = Config::get('db_port', false, false);
            if ($port)
            {
                $conn['port'] = $port;
            }
        }
        return $conn;
    }

}