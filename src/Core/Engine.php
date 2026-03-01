<?php
declare(strict_types=1);

namespace Fyre\Core;

use Fyre\Auth\Auth;
use Fyre\Auth\Identifier;
use Fyre\Auth\Middleware\AuthenticatedMiddleware;
use Fyre\Auth\Middleware\AuthMiddleware;
use Fyre\Auth\Middleware\AuthorizedMiddleware;
use Fyre\Auth\Middleware\UnauthenticatedMiddleware;
use Fyre\Auth\PolicyRegistry;
use Fyre\Cache\CacheManager;
use Fyre\Console\CommandRunner;
use Fyre\Console\Console;
use Fyre\Core\Middleware\ErrorHandlerMiddleware;
use Fyre\DB\ConnectionManager;
use Fyre\DB\Forge\ForgeRegistry;
use Fyre\DB\Migration\MigrationRunner;
use Fyre\DB\Schema\SchemaRegistry;
use Fyre\DB\TypeParser;
use Fyre\Event\EventManager;
use Fyre\Event\Traits\EventDispatcherTrait;
use Fyre\Http\Middleware\SessionMiddleware;
use Fyre\Http\MiddlewareQueue;
use Fyre\Http\MiddlewareRegistry;
use Fyre\Http\ServerRequest;
use Fyre\Http\Session\Session;
use Fyre\Log\LogManager;
use Fyre\Mail\MailManager;
use Fyre\ORM\EntityLocator;
use Fyre\ORM\ModelRegistry;
use Fyre\Queue\QueueManager;
use Fyre\Router\Middleware\RouterMiddleware;
use Fyre\Router\Middleware\SubstituteBindingsMiddleware;
use Fyre\Router\RouteLocator;
use Fyre\Router\Router;
use Fyre\Security\ContentSecurityPolicy;
use Fyre\Security\CsrfProtection;
use Fyre\Security\Encryption\EncryptionManager;
use Fyre\Security\Middleware\CspMiddleware;
use Fyre\Security\Middleware\CsrfProtectionMiddleware;
use Fyre\TestSuite\Benchmark;
use Fyre\TestSuite\Fixture\FixtureRegistry;
use Fyre\TestSuite\Timer;
use Fyre\Utility\Formatter;
use Fyre\Utility\FormBuilder;
use Fyre\Utility\HtmlHelper;
use Fyre\Utility\Inflector;
use Fyre\Utility\Path;
use Fyre\View\CellRegistry;
use Fyre\View\HelperRegistry;
use Fyre\View\TemplateLocator;
use Psr\Http\Message\ServerRequestInterface;

use function file_exists;

use const CONFIG;
use const LANG;
use const TEMPLATES;

/**
 * Configures core services and application bindings.
 */
class Engine extends Container
{
    use EventDispatcherTrait;

    /**
     * {@inheritDoc}
     */
    public static function getInstance(): Container
    {
        return static::$instance ??= new static(new Loader());
    }

    /**
     * Constructs an Engine.
     *
     * @param Loader $loader The Loader.
     */
    public function __construct(Loader $loader)
    {
        parent::__construct();

        $this->instance(Loader::class, $loader);

        $this
            ->bind(
                ServerRequestInterface::class,
                fn(): ServerRequest => $this->use(ServerRequest::class)
            )
            ->scoped(Auth::class)
            ->scoped(Benchmark::class)
            ->scoped(CsrfProtection::class)
            ->scoped(
                MiddlewareQueue::class,
                function(): MiddlewareQueue {
                    $middlewareQueue = $this->build(MiddlewareQueue::class) |> $this->middleware(...);

                    $this->dispatchEvent('Engine.buildMiddleware', ['middleware' => $middlewareQueue]);

                    return $middlewareQueue;
                }
            )
            ->scoped(
                MiddlewareRegistry::class,
                fn(): MiddlewareRegistry => $this->build(MiddlewareRegistry::class)
                    ->map('auth', AuthMiddleware::class)
                    ->map('authenticated', AuthenticatedMiddleware::class)
                    ->map('bindings', SubstituteBindingsMiddleware::class)
                    ->map('can', AuthorizedMiddleware::class)
                    ->map('csp', CspMiddleware::class)
                    ->map('csrf', CsrfProtectionMiddleware::class)
                    ->map('error', ErrorHandlerMiddleware::class)
                    ->map('router', RouterMiddleware::class)
                    ->map('session', SessionMiddleware::class)
                    ->map('unauthenticated', UnauthenticatedMiddleware::class)
            )
            ->scoped(ServerRequest::class)
            ->scoped(Timer::class)
            ->singleton(CacheManager::class)
            ->singleton(
                CellRegistry::class,
                fn(): CellRegistry => $this->build(CellRegistry::class)
                    ->addNamespace('App\Cells')
            )
            ->singleton(
                CommandRunner::class,
                fn(): CommandRunner => $this->build(CommandRunner::class)
                    ->addNamespace('App\Commands')
                    ->addNamespace('Fyre\Commands')
            )
            ->singleton(
                Config::class,
                fn(): Config => $this->build(Config::class)
                    ->addPath(CONFIG)
                    ->addPath(Path::join(__DIR__, '../../config'))
            )
            ->singleton(ConnectionManager::class)
            ->singleton(Console::class)
            ->singleton(ContentSecurityPolicy::class)
            ->singleton(EncryptionManager::class)
            ->singleton(
                EntityLocator::class,
                fn(): EntityLocator => $this->build(EntityLocator::class)
                    ->addNamespace('App\Entities')
            )
            ->singleton(ErrorHandler::class)
            ->singleton(
                EventManager::class,
                fn(): EventManager => $this->build(EventManager::class, [
                    'parentEventManager' => null,
                ])
            )
            ->singleton(
                FixtureRegistry::class,
                fn(): FixtureRegistry => $this->build(FixtureRegistry::class)
                    ->addNamespace('Tests\Fixtures')
            )
            ->singleton(ForgeRegistry::class)
            ->singleton(Formatter::class)
            ->singleton(FormBuilder::class)
            ->singleton(
                HelperRegistry::class,
                fn(): HelperRegistry => $this->build(HelperRegistry::class)
                    ->addNamespace('App\Helpers')
            )
            ->singleton(HtmlHelper::class)
            ->singleton(Identifier::class)
            ->singleton(Inflector::class)
            ->singleton(
                Lang::class,
                fn(): Lang => $this->build(Lang::class)
                    ->addPath(LANG)
                    ->addPath(Path::join(__DIR__, '../../lang'))
            )
            ->singleton(LogManager::class)
            ->singleton(MailManager::class)
            ->singleton(Make::class)
            ->singleton(
                MigrationRunner::class,
                fn(): MigrationRunner => $this->build(MigrationRunner::class)
                    ->addNamespace('App\Migrations')
            )
            ->singleton(
                ModelRegistry::class,
                fn(): ModelRegistry => $this->build(ModelRegistry::class)
                    ->addNamespace('App\Models')
            )
            ->singleton(
                PolicyRegistry::class,
                fn(): PolicyRegistry => $this->build(PolicyRegistry::class)
                    ->addNamespace('App\Policies')
            )
            ->singleton(QueueManager::class)
            ->singleton(RouteLocator::class)
            ->singleton(Router::class, function(): Router {
                $router = $this->build(Router::class);
                $routesPath = Path::join(CONFIG, 'routes.php');

                if (file_exists($routesPath)) {
                    require $routesPath;
                }

                return $router;
            })
            ->singleton(SchemaRegistry::class)
            ->singleton(Session::class)
            ->singleton(
                TemplateLocator::class,
                fn(): TemplateLocator => $this->build(TemplateLocator::class)
                    ->addPath(TEMPLATES)
            )
            ->singleton(TypeParser::class);
    }

    /**
     * Returns the EventManager.
     *
     * @return EventManager The EventManager instance.
     */
    public function getEventManager(): EventManager
    {
        return $this->use(EventManager::class);
    }

    /**
     * Builds application middleware.
     *
     * @param MiddlewareQueue $queue The MiddlewareQueue.
     * @return MiddlewareQueue The MiddlewareQueue instance.
     */
    public function middleware(MiddlewareQueue $queue): MiddlewareQueue
    {
        return $queue;
    }
}
