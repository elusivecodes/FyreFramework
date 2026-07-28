<?php
declare(strict_types=1);

namespace Tests\TestCase\Auth;

use Fyre\Auth\Auth;
use Fyre\Auth\Authenticator;
use Fyre\Auth\Authenticators\CookieAuthenticator;
use Fyre\Auth\Authenticators\SessionAuthenticator;
use Fyre\Auth\Authenticators\TokenAuthenticator;
use Fyre\Core\Config;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Http\ClientResponse;
use Fyre\Http\Cookie\Cookie;
use Fyre\Http\MiddlewareQueue;
use Fyre\Http\RequestHandler;
use Fyre\Http\ServerRequest;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Authenticators\MockAuthenticator;
use Tests\Mock\Entities\User;

use function class_uses;
use function json_decode;
use function json_encode;
use function password_hash;
use function password_verify;
use function rawurldecode;
use function rawurlencode;

use const PASSWORD_DEFAULT;

final class AuthenticatorTest extends TestCase
{
    use ConnectionTrait;

    public function testConstructAuthenticatorClassKey(): void
    {
        $this->container->use(Config::class)->set('Auth.authenticators', [
            [
                'className' => MockAuthenticator::class,
            ],
        ]);

        $auth = $this->container->build(Auth::class);

        $this->assertInstanceOf(
            MockAuthenticator::class,
            $auth->authenticator(MockAuthenticator::class)
        );
    }

    public function testConstructAuthenticatorKey(): void
    {
        $this->container->use(Config::class)->set('Auth.authenticators', [
            'mock' => [
                'className' => MockAuthenticator::class,
            ],
        ]);

        $auth = $this->container->build(Auth::class);

        $this->assertInstanceOf(
            MockAuthenticator::class,
            $auth->authenticator('mock')
        );
    }

    public function testConstructInvalidAuthenticator(): void
    {
        $this->container->use(Config::class)->set('Auth.authenticators', [
            [
                'className' => 'Invalid',
            ],
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Authenticator `Invalid` must extend `Fyre\Auth\Authenticator`.');

        $this->container->build(Auth::class);
    }

    public function testCookieAuthenticator(): void
    {
        $authenticator = $this->container->build(CookieAuthenticator::class);
        $this->auth->addAuthenticator($authenticator);

        $queue = new MiddlewareQueue([
            'auth',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $authUser = $this->identifier->identify('test@test.com');

        $this->assertInstanceOf(
            User::class,
            $authUser
        );

        $tokenHash = password_hash('test@test.com'.$authUser->password, PASSWORD_DEFAULT);
        $auth = (string) json_encode(['test@test.com', $tokenHash]) |> rawurlencode(...);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'cookies' => [
                    'auth' => $auth,
                ],
            ],
        ]);

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );

        $this->assertTrue($this->auth->isLoggedIn());
    }

    public function testCookieAuthenticatorLogin(): void
    {
        $authenticator = $this->container->build(CookieAuthenticator::class);
        $this->auth->addAuthenticator($authenticator);

        $queue = new MiddlewareQueue([
            'auth',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $request = $this->container->build(ServerRequest::class);

        $this->auth->attempt('test@test.com', 'test');

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );

        $this->assertTrue($this->auth->isLoggedIn());

        $this->assertEmpty(
            $response->getHeader('Set-Cookie')
        );
    }

    public function testCookieAuthenticatorLoginRemember(): void
    {
        $authenticator = $this->container->build(CookieAuthenticator::class);
        $this->auth->addAuthenticator($authenticator);

        $queue = new MiddlewareQueue([
            'auth',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $request = $this->container->build(ServerRequest::class);

        $this->auth->attempt('test@test.com', 'test', true);

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );

        $this->assertTrue($this->auth->isLoggedIn());

        $authUser = $this->auth->user();

        $this->assertInstanceOf(
            User::class,
            $authUser
        );

        [$cookieString] = $response->getHeader('Set-Cookie');

        $cookie = Cookie::createFromHeaderString($cookieString);

        $this->assertSame(
            'auth',
            $cookie->getName()
        );

        $value = rawurldecode($cookie->getValue());
        $data = json_decode($value, true);

        $this->assertCount(2, $data);

        [$identifier, $tokenHash] = $data;

        $token = 'test@test.com'.$authUser->password;

        $this->assertTrue(
            password_verify($token, $tokenHash)
        );

        $this->assertSame('auth', $cookie->getName());
        $this->assertFalse($cookie->isExpired());
    }

    public function testCookieAuthenticatorLogout(): void
    {
        $authenticator = $this->container->build(CookieAuthenticator::class);
        $this->auth->addAuthenticator($authenticator);

        $queue = new MiddlewareQueue([
            'auth',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $request = $this->container->build(ServerRequest::class);

        $this->auth->attempt('test@test.com', 'test', true);
        $this->auth->logout();

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );

        $this->assertFalse($this->auth->isLoggedIn());

        [$cookieString] = $response->getHeader('Set-Cookie');

        $cookie = Cookie::createFromHeaderString($cookieString);

        $this->assertSame(
            'auth',
            $cookie->getName()
        );

        $this->assertSame('', $cookie->getValue());
        $this->assertTrue($cookie->isExpired());
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Authenticator::class)
        );
    }

    public function testDebugCookieAuthenticator(): void
    {
        $data = $this->container->build(CookieAuthenticator::class, [
            'options' => [
                'salt' => 'l2wyQow3eTwQeTWcfZnlgU8FnbiWljpGjQvNP2pL',
            ],
        ])->__debugInfo();

        $this->assertSame(
            [
                '[class]' => CookieAuthenticator::class,
                'auth' => '[Fyre\Auth\Auth]',
                'config' => [
                    'cookieName' => 'auth',
                    'cookieOptions' => [
                        'httpOnly' => true,
                    ],
                    'identifierField' => 'email',
                    'passwordField' => 'password',
                    'salt' => '[*****]',
                ],
                'sendCookie' => null,
            ],
            $data
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(Authenticator::class)
        );
    }

    public function testSessionAuthenticator(): void
    {
        $authenticator = $this->container->build(SessionAuthenticator::class);
        $this->auth->addAuthenticator($authenticator);

        $queue = new MiddlewareQueue([
            'auth',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $request = $this->container->build(ServerRequest::class);

        $this->session->set('auth', 1);

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );

        $this->assertTrue($this->auth->isLoggedIn());
    }

    public function testSessionAuthenticatorLogin(): void
    {
        $authenticator = $this->container->build(SessionAuthenticator::class);
        $this->auth->addAuthenticator($authenticator);

        $queue = new MiddlewareQueue([
            'auth',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $request = $this->container->build(ServerRequest::class);

        $this->session->set('auth', 2);

        $this->auth->attempt('test@test.com', 'test');

        $this->assertSame(
            1,
            $this->session->get('auth')
        );

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );

        $this->assertTrue($this->auth->isLoggedIn());

        $this->assertSame(
            1,
            $this->session->get('auth')
        );
    }

    public function testSessionAuthenticatorLogout(): void
    {
        $authenticator = $this->container->build(SessionAuthenticator::class);
        $this->auth->addAuthenticator($authenticator);

        $this->auth->attempt('test@test.com', 'test');
        $this->auth->logout();

        $this->assertFalse($this->auth->isLoggedIn());

        $this->assertNull(
            $this->session->get('auth')
        );
    }

    public function testTokenAuthenticator(): void
    {
        $authenticator = $this->container->build(TokenAuthenticator::class);
        $this->auth->addAuthenticator($authenticator);

        $queue = new MiddlewareQueue([
            'auth',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'HTTP_AUTHORIZATION' => 'Bearer Ew7tqx8kH6QsNe8SS0tVT0BX2LIRVQyl',
                ],
            ],
        ]);

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );

        $this->assertTrue($this->auth->isLoggedIn());
    }
}
