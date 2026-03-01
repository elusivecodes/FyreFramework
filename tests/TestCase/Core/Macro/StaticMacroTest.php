<?php
declare(strict_types=1);

namespace Tests\TestCase\Core\Macro;

use BadMethodCallException;
use Override;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Core\Macro\MyClass;

class StaticMacroTest extends TestCase
{
    public function testHasMacroFalse(): void
    {
        $this->assertFalse(MyClass::hasStaticMacro('testMacro'));
    }

    public function testHasMacroInstance(): void
    {
        MyClass::macro('testMacro', static function(): string {
            return 'Hello, World!';
        });

        $this->assertFalse(MyClass::hasStaticMacro('testMacro'));
    }

    public function testHasStaticMacro(): void
    {
        MyClass::staticMacro('testMacro', static function(): string {
            return 'Hello, World!';
        });

        $this->assertTrue(MyClass::hasStaticMacro('testMacro'));
    }

    public function testMacroStaticCall(): void
    {
        MyClass::staticMacro('testMacro', static function(): string {
            return 'This is a string';
        });

        $this->assertSame('This is a string', MyClass::testMacro());
    }

    public function testMacroStaticCallInstance(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Static macro `Tests\Mock\Core\Macro\MyClass::testMacro` is not registered.');

        MyClass::macro('testMacro', static function(): string {
            return 'This is a string';
        });

        MyClass::testMacro();
    }

    public function testMacroStaticCallInvalid(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Static macro `Tests\Mock\Core\Macro\MyClass::testMacro` is not registered.');

        MyClass::testMacro();
    }

    #[Override]
    protected function setUp(): void
    {
        MyClass::clearMacros();
        MyClass::clearStaticMacros();
    }
}
