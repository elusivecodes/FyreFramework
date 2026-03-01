<?php
declare(strict_types=1);

namespace Tests\TestCase\Core;

use Fyre\Auth\Auth;
use Fyre\Cache\CacheManager;
use Fyre\Cache\Cacher;
use Fyre\Core\Attributes\Cache;
use Fyre\Core\Attributes\Config as ConfigContext;
use Fyre\Core\Attributes\CurrentUser;
use Fyre\Core\Attributes\DB;
use Fyre\Core\Attributes\Encryption;
use Fyre\Core\Attributes\Log;
use Fyre\Core\Attributes\Mail;
use Fyre\Core\Attributes\ORM;
use Fyre\Core\Attributes\RouteArgument;
use Fyre\Core\Config;
use Fyre\Core\Engine;
use Fyre\Core\Loader;
use Fyre\DB\Connection;
use Fyre\DB\ConnectionManager;
use Fyre\Http\ServerRequest;
use Fyre\Log\Logger;
use Fyre\Log\LogManager;
use Fyre\Mail\Mailer;
use Fyre\Mail\MailManager;
use Fyre\ORM\Entity;
use Fyre\ORM\Model;
use Fyre\ORM\ModelRegistry;
use Fyre\Router\Router;
use Fyre\Security\Encryption\Encrypter;
use Fyre\Security\Encryption\EncryptionManager;
use Override;
use PHPUnit\Framework\TestCase;

final class AttributesTest extends TestCase
{
    protected Engine $app;

    public function testCache(): void
    {
        $this->app->call(function(#[Cache] Cacher $cache): void {
            $this->assertSame(
                $this->app->use(CacheManager::class)->use(),
                $cache
            );
        });
    }

    public function testCacheKey(): void
    {
        $this->app->call(function(#[Cache('null')] Cacher $cache): void {
            $this->assertSame(
                $this->app->use(CacheManager::class)->use('null'),
                $cache
            );
        });
    }

    public function testConfigKey(): void
    {
        $this->app->call(function(#[ConfigContext('App.value')] string $value): void {
            $this->assertSame(
                'Test',
                $value
            );
        });
    }

    public function testCurrentUser(): void
    {
        $this->app->call(function(#[CurrentUser] Entity $user): void {
            $this->assertSame(
                $this->app->use(Auth::class)->user(),
                $user
            );
        });
    }

    public function testDb(): void
    {
        $this->app->call(function(#[DB] Connection $connection): void {
            $this->assertSame(
                $this->app->use(ConnectionManager::class)->use(),
                $connection
            );
        });
    }

    public function testDbKey(): void
    {
        $this->app->call(function(#[DB('other')] Connection $connection): void {
            $this->assertSame(
                $this->app->use(ConnectionManager::class)->use('other'),
                $connection
            );
        });
    }

    public function testEncryption(): void
    {
        $this->app->call(function(#[Encryption] Encrypter $encrypter): void {
            $this->assertSame(
                $this->app->use(EncryptionManager::class)->use(),
                $encrypter
            );
        });
    }

    public function testEncryptionKey(): void
    {
        $this->app->call(function(#[Encryption('openssl')] Encrypter $encrypter): void {
            $this->assertSame(
                $this->app->use(EncryptionManager::class)->use('openssl'),
                $encrypter
            );
        });
    }

    public function testLog(): void
    {
        $this->app->call(function(#[Log] Logger $logger): void {
            $this->assertSame(
                $this->app->use(LogManager::class)->use(),
                $logger
            );
        });
    }

    public function testLogKey(): void
    {
        $this->app->call(function(#[Log('other')] Logger $logger): void {
            $this->assertSame(
                $this->app->use(LogManager::class)->use('other'),
                $logger
            );
        });
    }

    public function testMail(): void
    {
        $this->app->call(function(#[Mail] Mailer $mailer): void {
            $this->assertSame(
                $this->app->use(MailManager::class)->use(),
                $mailer
            );
        });
    }

    public function testMailKey(): void
    {
        $this->app->call(function(#[Mail('other')] Mailer $mailer): void {
            $this->assertSame(
                $this->app->use(MailManager::class)->use('other'),
                $mailer
            );
        });
    }

    public function testORMKey(): void
    {
        $this->app->call(function(#[ORM('Test')] Model $model): void {
            $this->assertSame(
                $this->app->use(ModelRegistry::class)->use('Test'),
                $model
            );
        });
    }

    public function testRouteArgument(): void
    {
        $request = $this->app->build(ServerRequest::class, [
            'options' => [
                'method' => 'get',
                'server' => [
                    'REQUEST_URI' => '/test/1',
                ],
            ],
        ]);

        $request = $this->app->use(Router::class)->parseRequest($request);

        $this->app->instance(ServerRequest::class, $request);

        $this->app->call(function(#[RouteArgument('id')] int $id): void {
            $this->assertSame(
                1,
                $id
            );
        });
    }

    #[Override]
    protected function setUp(): void
    {
        $loader = new Loader();
        $this->app = new Engine($loader);

        Engine::setInstance($this->app);

        $this->app->use(Config::class)
            ->load('functions')
            ->load('app');

        $auth = $this->app->use(Auth::class);

        $user = new Entity(['id' => 1]);
        $auth->login($user);
    }
}
