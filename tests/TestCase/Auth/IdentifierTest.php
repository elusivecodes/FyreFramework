<?php
declare(strict_types=1);

namespace Tests\TestCase\Auth;

use Fyre\Auth\Identifier;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Entities\User;
use Tests\Mock\Models\UsersModel;

use function class_uses;
use function password_hash;
use function password_needs_rehash;

final class IdentifierTest extends TestCase
{
    use ConnectionTrait;

    public function testAttempt(): void
    {
        $authUser = $this->identifier->attempt('test@test.com', 'test');

        $this->assertInstanceOf(
            User::class,
            $authUser
        );
    }

    public function testAttemptInvalidPassword(): void
    {
        $authUser = $this->identifier->attempt('test@test.com', 'invalid');

        $this->assertNull($authUser);
    }

    public function testAttemptInvalidUsername(): void
    {
        $authUser = $this->identifier->attempt('invalid@test.com', 'any');

        $this->assertNull($authUser);
    }

    public function testAttemptRehash(): void
    {
        $authUser = $this->identifier->identify('test@test.com');

        $authUser->password = password_hash('test', PASSWORD_ARGON2I);

        $this->identifier->getModel()->save($authUser);

        $authUser = $this->identifier->attempt('test@test.com', 'test');

        $this->assertInstanceOf(
            User::class,
            $authUser
        );

        $authUser = $this->identifier->identify('test@test.com');

        $this->assertFalse(
            password_needs_rehash($authUser->password, PASSWORD_DEFAULT)
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Identifier::class)
        );
    }

    public function testGetIdentifierFields(): void
    {
        $this->assertSame(
            ['username', 'email'],
            $this->identifier->getIdentifierFields()
        );
    }

    public function testGetModel(): void
    {
        $Model = $this->identifier->getModel();

        $this->assertInstanceOf(
            UsersModel::class,
            $Model
        );
    }

    public function testGetPasswordField(): void
    {
        $this->assertSame(
            'password',
            $this->identifier->getPasswordField()
        );
    }

    public function testIdentify(): void
    {
        $authUser = $this->identifier->identify('test@test.com');

        $this->assertInstanceOf(
            User::class,
            $authUser
        );
    }

    public function testIdentifyInvalid(): void
    {
        $authUser = $this->identifier->identify('invalid');

        $this->assertNull($authUser);
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(Identifier::class)
        );
    }
}
