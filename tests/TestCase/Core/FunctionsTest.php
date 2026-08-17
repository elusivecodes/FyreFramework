<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Auth\Auth;
use Fyre\Cache\CacheManager;
use Fyre\Core\Config;
use Fyre\Core\Engine;
use Fyre\Core\Loader;
use Fyre\DB\ConnectionManager;
use Fyre\DB\TypeParser;
use Fyre\DB\Types\DateTimeType;
use Fyre\Http\ClientResponse;
use Fyre\Http\Exceptions\BadRequestException;
use Fyre\Http\Exceptions\ConflictException;
use Fyre\Http\Exceptions\ForbiddenException;
use Fyre\Http\Exceptions\GoneException;
use Fyre\Http\Exceptions\InternalServerException;
use Fyre\Http\Exceptions\MethodNotAllowedException;
use Fyre\Http\Exceptions\NotAcceptableException;
use Fyre\Http\Exceptions\NotFoundException;
use Fyre\Http\Exceptions\NotImplementedException;
use Fyre\Http\Exceptions\ServiceUnavailableException;
use Fyre\Http\Exceptions\UnauthorizedException;
use Fyre\Http\RedirectResponse;
use Fyre\Http\ServerRequest;
use Fyre\Http\Session\Session;
use Fyre\Http\Stream\JsonStream;
use Fyre\Log\Handlers\ArrayLogger;
use Fyre\Log\LogManager;
use Fyre\Mail\Email;
use Fyre\ORM\Entity;
use Fyre\ORM\Model;
use Fyre\Queue\QueueManager;
use Fyre\Security\Encryption\EncryptionManager;
use Fyre\Utility\Collection;
use Fyre\Utility\DateTime\DateTime;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Tests\Mock\Jobs\MockJob;
use Tests\Mock\Queue\TestQueue;
use Throwable;

use function __;
use function abort;
use function asset;
use function auth;
use function authorize;
use function cache;
use function can;
use function cannot;
use function collect;
use function config;
use function db;
use function element;
use function email;
use function encryption;
use function escape;
use function json;
use function logged_in;
use function model;
use function now;
use function redirect;
use function request;
use function route;
use function session;
use function type;
use function user;
use function view;

use const PHP_EOL;

final class FunctionsTest extends TestCase
{
    protected Engine $app;

    /**
     * @return array<string, array{int, class-string<Throwable>}>
     */
    public static function abortCodeProvider(): array
    {
        return [
            'bad request' => [400, BadRequestException::class],
            'unauthorized' => [401, UnauthorizedException::class],
            'method not allowed' => [405, MethodNotAllowedException::class],
            'not acceptable' => [406, NotAcceptableException::class],
            'conflict' => [409, ConflictException::class],
            'not implemented' => [501, NotImplementedException::class],
            'service unavailable' => [503, ServiceUnavailableException::class],
        ];
    }

    public function testAbort(): void
    {
        $this->expectException(InternalServerException::class);
        $this->expectExceptionMessageIs('Internal Server Error');

        abort();
    }

    public function testAbortCode(): void
    {
        $this->expectException(GoneException::class);
        $this->expectExceptionCode(410);
        $this->expectExceptionMessageIs('Gone');

        abort(410);
    }

    /**
     * @param class-string<Throwable> $exceptionClass
     */
    #[DataProvider('abortCodeProvider')]
    public function testAbortCodes(int $code, string $exceptionClass): void
    {
        $this->expectException($exceptionClass);
        $this->expectExceptionCode($code);

        abort($code);
    }

    public function testAbortMessage(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessageIs('This is a message');

        abort(404, 'This is a message');
    }

    public function testApp(): void
    {
        $this->assertSame(
            $this->app,
            app()
        );
    }

    public function testAsset(): void
    {
        $this->assertSame(
            '/assets/test.txt',
            asset('/assets/test.txt')
        );
    }

    public function testAssetFullBase(): void
    {
        $this->assertSame(
            'https://test.com/assets/test.txt',
            asset('/assets/test.txt', true)
        );
    }

    public function testAuth(): void
    {
        $this->assertSame(
            $this->app->use(Auth::class),
            auth()
        );
    }

    public function testAuthorize(): void
    {
        $this->expectNotToPerformAssertions();

        authorize('test');
    }

    public function testAuthorizeFail(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionCode(403);
        $this->expectExceptionMessageIs('Forbidden');

        authorize('fail');
    }

    public function testCache(): void
    {
        $this->assertSame(
            $this->app->use(CacheManager::class)->use(),
            cache()
        );
    }

    public function testCacheKey(): void
    {
        $this->assertSame(
            $this->app->use(CacheManager::class)->use('null'),
            cache('null')
        );
    }

    public function testCan(): void
    {
        $this->assertTrue(can('test'));
    }

    public function testCanAny(): void
    {
        $this->assertTrue(can_any(['fail', 'test']));
    }

    public function testCanFail(): void
    {
        $this->assertFalse(can('fail'));
    }

    public function testCanNone(): void
    {
        $this->assertFalse(can_none(['fail', 'test']));
    }

    public function testCannot(): void
    {
        $this->assertTrue(cannot('fail'));
    }

    public function testCannotFail(): void
    {
        $this->assertFalse(cannot('test'));
    }

    public function testCollect(): void
    {
        $collection = collect([1, 2, 3]);

        $this->assertInstanceOf(
            Collection::class,
            $collection
        );

        $this->assertArraysAreIdentical(
            [1, 2, 3],
            $collection->toArray()
        );
    }

    public function testConfig(): void
    {
        $this->assertSame(
            $this->app->use(Config::class),
            config()
        );
    }

    public function testConfigKey(): void
    {
        $this->assertSame(
            'Test',
            config('App.value')
        );
    }

    public function testDb(): void
    {
        $this->assertSame(
            $this->app->use(ConnectionManager::class)->use(),
            db()
        );
    }

    public function testDbKey(): void
    {
        $this->assertSame(
            $this->app->use(ConnectionManager::class)->use('other'),
            db('other')
        );
    }

    public function testElement(): void
    {
        $this->assertSame(
            'Element: 1',
            element('element', ['b' => 1])
        );
    }

    public function testEmail(): void
    {
        $this->assertInstanceOf(
            Email::class,
            email()
        );
    }

    public function testEncryption(): void
    {
        $this->assertSame(
            $this->app->use(EncryptionManager::class)->use(),
            encryption()
        );
    }

    public function testEncryptionKey(): void
    {
        $this->assertSame(
            $this->app->use(EncryptionManager::class)->use('openssl'),
            encryption('openssl')
        );
    }

    public function testEnv(): void
    {
        $this->assertSame(
            'value',
            env('test')
        );
    }

    public function testEnvDefault(): void
    {
        $this->assertSame(
            'value',
            env('invalid', 'value')
        );
    }

    public function testEscape(): void
    {
        $this->assertSame(
            '&lt;b&gt;Test&lt;/b&gt;',
            escape('<b>Test</b>')
        );
    }

    public function testJson(): void
    {
        $response = json(['a' => 1]);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );

        $this->assertSame(
            '{'.PHP_EOL.
            '    "a": 1'.PHP_EOL.
            '}',
            $response->getBody()->getContents()
        );

        $this->assertSame(
            'application/json; charset=UTF-8',
            $response->getHeaderLine('Content-Type')
        );
    }

    public function testJsonStream(): void
    {
        $response = json([['id' => 1], ['id' => 2]], stream: true);

        $this->assertInstanceOf(JsonStream::class, $response->getBody());
        $this->assertSame('[{"id":1},{"id":2}]', $response->getBody()->getContents());
        $this->assertSame('application/json; charset=UTF-8', $response->getHeaderLine('Content-Type'));
    }

    public function testLang(): void
    {
        $this->assertSame(
            'Test',
            __('Values.test')
        );
    }

    public function testLoggedIn(): void
    {
        $this->assertTrue(logged_in());
    }

    public function testLogMessage(): void
    {
        config()->set('Log', [
            'default' => [
                'className' => ArrayLogger::class,
            ],
        ]);

        log_message('error', 'This is a log message {id}', ['id' => 1]);

        $logger = $this->app->use(LogManager::class)->use();

        $this->assertInstanceOf(
            ArrayLogger::class,
            $logger
        );

        $this->assertArraysAreIdentical(
            ['[ERROR] This is a log message 1'],
            $logger->read()
        );
    }

    public function testModel(): void
    {
        $model = model('Test');

        $this->assertInstanceOf(
            Model::class,
            $model
        );

        $this->assertSame(
            'Test',
            $model->getAlias()
        );
    }

    public function testNow(): void
    {
        $this->assertInstanceOf(
            DateTime::class,
            now()
        );
    }

    public function testQueue(): void
    {
        config()->set('Queue', [
            'default' => [
                'className' => TestQueue::class,
            ],
        ]);

        TestQueue::resetMessages();

        queue(MockJob::class, ['id' => 1], ['queue' => 'test']);

        $messages = TestQueue::getMessages();

        $this->assertCount(
            1,
            $messages
        );

        $this->assertSame(
            MockJob::class,
            $messages[0]->getConfig()['className']
        );

        $this->assertArraysAreIdentical(
            ['id' => 1],
            $messages[0]->getConfig()['arguments']
        );

        $this->assertSame(
            'test',
            $messages[0]->getConfig()['queue']
        );
    }

    public function testRedirect(): void
    {
        $response = redirect('https://test.com/');

        $this->assertInstanceOf(
            RedirectResponse::class,
            $response
        );

        $this->assertSame(
            'https://test.com/',
            $response->getHeaderLine('Location')
        );
    }

    public function testRequest(): void
    {
        $this->assertSame(
            $this->app->use(ServerRequest::class),
            request()
        );

        $this->assertSame(
            $this->app->use(ServerRequestInterface::class),
            request()
        );
    }

    public function testRequestKey(): void
    {
        $this->assertNull(
            request('test')
        );
    }

    public function testRoute(): void
    {
        $this->assertSame(
            '/test',
            route('test', full: false)
        );
    }

    public function testRouteArguments(): void
    {
        $this->assertSame(
            '/test/1',
            route('test2', ['id' => 1], full: false)
        );
    }

    public function testRouteFullBase(): void
    {
        $this->assertSame(
            'https://test.com/test',
            route('test', full: true)
        );
    }

    public function testSession(): void
    {
        $this->assertSame(
            $this->app->use(Session::class),
            session()
        );
    }

    public function testSessionKey(): void
    {
        $this->assertSame(
            $this->app->use(Session::class),
            session('a', 1)
        );

        $this->assertSame(
            1,
            session('a')
        );
    }

    public function testType(): void
    {
        $this->assertInstanceOf(
            TypeParser::class,
            type()
        );
    }

    public function testTypeKey(): void
    {
        $this->assertInstanceOf(
            DateTimeType::class,
            type('datetime')
        );
    }

    public function testUser(): void
    {
        $user = user();

        $this->assertInstanceOf(
            Entity::class,
            $user
        );

        $this->assertSame(1, $user->get('id'));
    }

    public function testView(): void
    {
        $this->assertSame(
            'Template: 1',
            view('test/template', ['a' => 1])
        );
    }

    public function testViewLayout(): void
    {
        $this->assertSame(
            'Content: Template: 1',
            view('test/template', ['a' => 1], 'default')
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $loader = new Loader();
        $this->app = new Engine($loader);

        Engine::setInstance($this->app);

        $this->app->use(Config::class)
            ->load('app');

        $auth = $this->app->use(Auth::class);
        $access = $auth->access();

        $user = new Entity(['id' => 1]);
        $auth->login($user);

        $access->define('fail', static fn(): bool => false);
        $access->define('test', static fn(Entity|null $user): bool => (bool) $user);

        $this->app->use(Config::class)->set('Queue', [
            'default' => [
                'className' => TestQueue::class,
            ],
        ]);

        $this->app->use(Config::class)->set('Log', [
            'default' => [
                'className' => ArrayLogger::class,
            ],
        ]);

        $this->app->singleton(QueueManager::class);
        $this->app->singleton(LogManager::class);
    }
}
