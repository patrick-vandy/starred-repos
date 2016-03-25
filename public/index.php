<?php

// have to manually include config, everything else is auto loaded
require_once(dirname(__DIR__) . '/framework/config.php');

use framework\core\App;

// initialize the app
$app = new App();
$app->init();