<?php
declare(strict_types=1);

use Fyre\Core\Config;
use Fyre\Core\Engine;
use Fyre\Core\ErrorHandler;
use Fyre\Core\Loader;

require dirname(__DIR__, 4).'/vendor/autoload.php';

$engine = new Engine(new Loader());
$engine->use(Config::class)->set('Error.log', false);
$engine->use(ErrorHandler::class)->register();

throw new RuntimeException('CLI failure');
