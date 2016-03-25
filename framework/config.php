<?php

namespace framework\core;

/**
 * Global configuration file.
 *
 * This is included at the top of the public entry file so anything in this file
 * is guaranteed to be available anywhere in the app.
 *
 * @see \framework\core\Config
 */


// register auto loader (do this here since config is always included first).
// use the composer autoloader. class names should follow PSR-4 standards
require_once('vendor/autoload.php');


/**
 * CORE CONFIG VALUES (edit but do not remove these)
 */

// full path to base directory and base namespace name
// these are dynamically set (it is not recommended to change these)
Config::set('base_dir', __DIR__);
Config::set('base_name', substr(__NAMESPACE__, 0, strpos(__NAMESPACE__, '\\')));

// full path to base directory of public content (images, css, etc.)
Config::set('public_dir', dirname(__DIR__) . '/public');

// the public entry file that handles all requests (typically index.php)
Config::set('entry_file', 'index.php');

// default controller and method
Config::set('default_controller', 'Home');
Config::set('default_method', 'index');

// default database connection
Config::set('db_driver', 'mysql');
Config::set('db_host', 'localhost');
Config::set('db_name', 'github');
Config::set('db_user', 'github');
Config::set('db_pass', 'kd&M23@mAk^.2jk');

// site settings
Config::set('site_title', 'GitHub Repos');
Config::set('site_url', 'ec2-52-33-6-212.us-west-2.compute.amazonaws.com:8080');


/**
 * CUSTOM CONFIG VALUES (do whatever you want here)
 */

// default template to use
Config::set('default_template', 'main');

// access token for github api
Config::set('github_token', '459c10e015a92c5adac81fcf9da0932f54221fb6');

// maximum number of repos to import
Config::set('max_repos', 500);

// default results per page
Config::set('rpp', 20);