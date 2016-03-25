<?php

namespace framework\core;


/**
 * Provides routing to map a URL or CLI options to a controller and method.
 *
 * @package framework\core
 */
class Routing
{

    private $uri;
    private $controller;
    private $method;
    private $args;


    /**
     * Set the controller and method to their default config settings and args
     * to an empty array.
     */
    public function __construct()
    {
        $this->uri = $this->_set_uri();
        $this->controller = Config::get('default_controller');
        $this->method = Config::get('default_method');
        $this->args = [];
    }


    /**
     * Perform routing and return an array with keys for controller, method.
     * and args. Determines if running in CLI or web mode using PHP_SAPI constant and
     * calls the appropriate routing method.
     *
     * @return array
     */
    public function route()
    {
        if (PHP_SAPI == 'cli')
        {
            $mode = 'cli';
            $this->_route_cli();
        }
        else
        {
            $mode = 'web';
            $this->_route_web();
        }

        $this->_format_names();

        return [
            'controller' => $this->controller,
            'method' => $this->method,
            'args' => $this->args,
            'mode' => $mode
        ];
    }


    /**
     * Parse cli options to set controller, method and arguments.
     *
     * @throws ExceptionPageNotFound
     */
    public function _route_cli()
    {
        $options = getopt('c:m:i:');

        if (empty($options['c']) || empty($options['m']))
        {
            throw new ExceptionPageNotFound('Controller and/or method not provided!');
        }

        $this->controller = $options['c'];
        $this->method = $options['m'];

        if (!empty($options['i']))
        {
            if (!is_array($options['i']))
            {
                $options['i'] = [$options['i']];
            }
            $this->args = $options['i'];
        }
    }


    /**
     * Parse the URI to set controller, method and arguments.
     */
    private function _route_web()
    {
        // strip index.php off beginning of uri if it exists
        $uri = $this->uri;
        $entry_file = Config::get('entry_file');
        if (substr($this->uri, 0, strlen($entry_file)) == $entry_file)
        {
            $uri = trim(substr($this->uri, strlen($entry_file)), '/');
        }

        // strip query string off end of uri
        if (strpos($uri, '?') !== false)
        {
            $uri = trim(substr($uri, 0, strpos($uri, '?')), '/');
        }

        // check if a manual route is defined, if not use default routing
        if (!$this->_manual_route($uri))
        {
            $this->_default_route($uri);
        }
    }


    /**
     * Check if a manual route defined in the Config setting 'routes' matches
     * the beginning of the uri. If a match if found, sets the controller,
     * method, and args members of this class and return true.
     *
     * The config setting for manual routes should be an associative array in
     * the following format:
     *
     * [
     *    '/some/path' => ['controller', 'method']
     *    '/another_path' => 'controller2', 'method2']
     *    ...
     * ]
     *
     * @param $uri
     * @return bool
     */
    private function _manual_route($uri)
    {
        foreach (Config::get('routes', false, []) as $uri_segment => $route)
        {
            $uri_segment = trim($uri_segment, '/');
            $length = strlen($uri_segment);
            if (substr($uri, 0, $length) == $uri_segment)
            {
                $this->controller = $route[0];
                $this->method = $route[1];

                $parts = explode('/', trim(substr($uri, $length), '/'));
                foreach ($parts as $part)
                {
                    if (strpos($part, ':') !== false)
                    {
                        list ($name, $value) = explode(':', $part, 2);
                        Input::set('get', urldecode($name), urldecode($value));
                    }
                    else
                    {
                        $this->args[] = urldecode($part);
                    }
                }

                return true;
            }
        }
        return false;
    }


    /**
     * Parse the uri using the default routing of /controller/method/arg1/arg2
     * and set the controller, method and args members of this class.
     *
     * @param $uri
     */
    private function _default_route($uri)
    {
        $parts = explode('/', $uri);

        if (!empty($parts[0]))
        {
            $this->controller = $parts[0];
        }

        if (!empty($parts[1]))
        {
            $this->method = $parts[1];
        }

        for ($i = 2; array_key_exists($i, $parts); $i++)
        {
            if (strpos($parts[$i], ':') !== false)
            {
                list ($name, $value) = explode(':', $parts[$i], 2);
                Input::set('get', urldecode($name), urldecode($value));
            }
            else
            {
                $this->args[] = urldecode($parts[$i]);
            }
        }
    }


    /**
     * Convert controller and method name from uri format to correct naming convention.
     *
     * Controller Examples:
     *    some-class => SomeClass
     *    some_class => SomeClass
     *
     * Method Examples:
     *    some-method => some_method
     *    Some_Method => some_method
     */
    private function _format_names()
    {
        $this->controller = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', strtolower($this->controller))));
        $this->method = str_replace('-', '_', strtolower($this->method));
    }


    /**
     * Return the current URL
     *
     * @return string
     */
    private function _set_uri()
    {
        if (isset($_SERVER['REQUEST_URI']))
        {
            return trim($_SERVER['REQUEST_URI'], '/');
        }
        return '';
    }

}