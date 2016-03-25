<?php

namespace framework\core;


/**
 * Base controller. All controllers should extend this class.
 *
 * @package framework\core
 */
class Controller
{

    protected static $route;

    protected $view_base_path;


    public function __construct()
    {
        $this->view_base_path = Config::get('base_dir') . '/view';
    }


    /**
     * Try loading the page and throw a 404 error if the page is not found.
     * $route is an array with the following format:
     *
     * $route = [
     *     'controller' => 'ClassName'
     *     'method' => 'method_name',
     *     'args' => ['arg1', 'arg2']
     * ]
     *
     * If $route is not provided it is determined by parsing either the URI
     * or CLI options.
     *
     * @param array $route
     * @see \framework\core\Controller::_load
     * @see \framework\core\Routing::route
     */
    public static function load($route = [])
    {
        try
        {
            self::_load($route);
        }
        catch (ExceptionPageNotFound $e)
        {
            if (self::$route['mode'] == 'cli')
            {
                echo $e->getMessage();
                exit(1);
            }
            else
            {
                header('HTTP/1.0 404 Not Found');
            }
        }
    }


    /**
     * Load a view, replace placeholders with values provided and return as a string.
     *
     * Placeholders in views should be all caps and enclosed in double percent signs.
     * The values array provided should use lowercase names without the percent signs.
     *
     * Example View: <h1>%%HEADER%%</h1>
     *
     * Example $values array: ['header' => 'My Header']
     *
     * @param string $view
     * @param array $values
     * @param string $ext
     * @return string
     * @throws \Exception
     */
    public function load_view($view, $values = [], $ext = '.html')
    {
        $find = [];
        $replace = [];

        foreach ($values as $key => $value)
        {
            $find[] = '%%' . strtoupper($key) . '%%';
            $replace[] = $value;
        }

        $path = "{$this->view_base_path}/{$view}{$ext}";
        if (!file_exists($path))
        {
            throw new \Exception("Could not load view file $path");
        }

        $view = file_get_contents($path);
        $view = str_replace($find, $replace, $view);

        $view = preg_replace('/%%[A-Z0-9_]+%%/', '', $view);

        return $view;
    }


    /**
     * Converts data to standard json response for ajax controllers.
     *
     * @param mixed $data
     * @param int $error
     */
    public function load_json($data, $error = 0)
    {
        header('Content-Type: application/json');

        echo json_encode([
            'error' => $error,
            'data' => $data
        ]);
    }


    /**
     * Redirect to the value of $_SESSION['redirect'] or $default_url if the
     * session variable is empty.
     *
     * Specify $ignore_session = true to always redirect to $default_url
     * regardless of if $_SESSION['redirect'] is set.
     *
     * @param string $default_url
     * @param bool|false $ignore_session
     */
    protected function _redirect($default_url = '/', $ignore_session = false)
    {
        if (!$ignore_session && !empty($_SESSION['redirect']))
        {
            $url = $_SESSION['redirect'];
            unset($_SESSION['redirect']);
        }
        else
        {
            $url = $default_url;
        }

        header("Location: $url");
        exit;
    }


    /**
     * Escape data for html to prevent XSS.
     *
     * Any untrusted data being place into a view should be run
     * through this function.
     *
     * @param array|string $data
     * @return array|string
     */
    protected function _html_escape($data)
    {
        if (is_array($data))
        {
            array_walk($data, function(&$value)
            {
                htmlentities($value);
            });
        }
        else
        {
            $data = htmlentities($data);
        }
        return $data;
    }


    /**
     * Load and run the appropriate controller and method based on uri routing.
     *
     * If $route is not provided the URL is parsed to determine routing. $route
     * is an array with the following format:
     *
     * $route = [
     *     'controller' => 'ClassName'
     *     'method' => 'method_name',
     *     'args' => ['arg1', 'arg2']
     * ]
     *
     * @param array $route
     * @throws ExceptionPageNotFound
     */
    private static function _load($route)
    {
        if (empty($route))
        {
            $routing = new Routing();
            $route = $routing->route();
        }

        self::$route = $route;

        $namespace = '\\' . Config::get('base_name') . '\\controller\\';
        if ($route['mode'] == 'cli')
        {
            $namespace .= 'cli\\';
        }

        $class_name = $namespace . $route['controller'];

        if (!class_exists($class_name))
        {
            throw new ExceptionPageNotFound("Class $class_name does not exist!");
        }

        $class = new \ReflectionClass($class_name);
        $instance = $class->newInstance();

        if (!is_callable([$instance, $route['method']]))
        {
            throw new ExceptionPageNotFound("Method $class_name::{$route['method']} does not exist!");
        }

        $method = new \ReflectionMethod($instance, $route['method']);
        $method->invokeArgs($instance, $route['args']);
    }

}