<?php

namespace framework\core;


/**
 * Core app functionality
 *
 * @package framework\core
 */
class App
{

    /**
     * App constructor.
     *
     * Prepares static Input class.
     */
    public function __construct()
    {
        Input::process();
    }


    /**
     * Initialize the app by loading controller
     *
     * @see \framework\core\Controller::load
     */
    public function init()
    {
        Controller::load();
    }

}