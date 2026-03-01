<?php
declare(strict_types=1);

use Fyre\Core\Loader;

// Constants
define('TIME_START', hrtime());
define('ROOT', '.');
define('CONFIG', 'tests/config');
define('LANG', 'tests/lang');
define('TEMPLATES', 'tests/templates');

putenv('test=value');

// Load Composer
$composer = require realpath('vendor/autoload.php');

// Register autoloader
$loader = new Loader()
    ->addClassMap($composer->getClassMap())
    ->addNamespaces($composer->getPrefixesPsr4())
    ->addNamespaces([
        'Tests' => __DIR__,
    ])
    ->register();
