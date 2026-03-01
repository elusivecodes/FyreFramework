<?php
declare(strict_types=1);

namespace Tests\Mock;

use Fyre\Core\Config;
use Fyre\Core\Engine;
use Fyre\Core\ErrorHandler;
use Fyre\Core\Loader;
use Fyre\DB\Exceptions\DbException;
use Fyre\Event\Event;
use Fyre\Http\MiddlewareQueue;
use Override;
use PHPUnit\Exception;
use Throwable;

/**
 * Application
 */
class Application extends Engine
{
    public function __construct(Loader $loader)
    {
        parent::__construct($loader);

        $this->use(Config::class)
            ->load('functions')
            ->load('app');

        $this->use(ErrorHandler::class)
            ->disableCli()
            ->register();

        $this->getEventManager()->on('Error.beforeRender', static function(Event $event, Throwable $exception): void {
            if ($exception instanceof Exception || $exception instanceof DbException) {
                throw $exception;
            }
        });
    }

    #[Override]
    public function middleware(MiddlewareQueue $queue): MiddlewareQueue
    {
        return $queue
            ->add('error')
            ->add('csrf')
            ->add('csp')
            ->add('auth')
            ->add('router')
            ->add('bindings');
    }
}
