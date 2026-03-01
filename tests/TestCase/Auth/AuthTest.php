<?php
declare(strict_types=1);

namespace Tests\TestCase\Auth;

use Fyre\Auth\Auth;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Authenticators\MockAuthenticator;
use Tests\Mock\Entities\User;

use function class_uses;

final class AuthTest extends TestCase
{
    use ConnectionTrait;

    public function testAttempt(): void
    {
        $authUser = $this->auth->attempt('test@test.com', 'test');

        $this->assertInstanceOf(
            User::class,
            $authUser
        );

        $this->assertTrue($this->auth->isLoggedIn());
    }

    public function testAttemptInvalidPassword(): void
    {
        $authUser = $this->auth->attempt('test@test.com', 'invalid');

        $this->assertNull($authUser);
        $this->assertFalse($this->auth->isLoggedIn());
    }

    public function testAttemptInvalidUsername(): void
    {
        $authUser = $this->auth->attempt('invalid@test.com', 'any');

        $this->assertNull($authUser);
        $this->assertFalse($this->auth->isLoggedIn());
    }

    public function testAuthenticator(): void
    {
        $authenticator = $this->container->build(MockAuthenticator::class);

        $this->auth->addAuthenticator($authenticator);

        $this->assertSame(
            $authenticator,
            $this->auth->authenticator(MockAuthenticator::class)
        );
    }

    public function testAuthenticatorInvalid(): void
    {
        $this->assertNull(
            $this->auth->authenticator('invalid')
        );
    }

    public function testAuthenticatorKey(): void
    {
        $authenticator = $this->container->build(MockAuthenticator::class);

        $this->auth->addAuthenticator($authenticator, 'mock');

        $this->assertSame(
            $authenticator,
            $this->auth->authenticator('mock')
        );
    }

    public function testAuthenticators(): void
    {
        $authenticator = $this->container->build(MockAuthenticator::class);

        $this->auth->addAuthenticator($authenticator, 'mock');

        $this->assertSame(
            [
                'mock' => $authenticator,
            ],
            $this->auth->authenticators()
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Auth::class)
        );
    }

    public function testIsLoggedIn(): void
    {
        $this->login();

        $this->assertTrue($this->auth->isLoggedIn());
    }

    public function testIsLoggedInFalse(): void
    {
        $this->assertFalse($this->auth->isLoggedIn());
    }

    public function testLogout(): void
    {
        $this->login();
        $this->auth->logout();

        $this->assertFalse($this->auth->isLoggedIn());
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(Auth::class)
        );
    }

    public function testUser(): void
    {
        $this->login();

        $this->assertInstanceOf(
            User::class,
            $this->auth->user()
        );
    }

    public function testUserNull(): void
    {
        $this->assertNull(
            $this->auth->user()
        );
    }
}
