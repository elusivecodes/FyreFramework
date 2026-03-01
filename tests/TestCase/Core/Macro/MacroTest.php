<?php
declare(strict_types=1);

namespace Tests\TestCase\Core\Macro;

use BadMethodCallException;
use Override;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Core\Macro\MyClass;

class MacroTest extends TestCase
{
    public function testHasMacro(): void
    {
        MyClass::macro('testMacro', function(): string {
            return 'Hello, World!';
        });

        $this->assertTrue(MyClass::hasMacro('testMacro'));
    }

    public function testHasMacroFalse(): void
    {
        $this->assertFalse(MyClass::hasMacro('testMacro'));
    }

    public function testHasMacroStatic(): void
    {
        MyClass::staticMacro('testMacro', function(): string {
            return 'Hello, World!';
        });

        $this->assertFalse(MyClass::hasMacro('testMacro'));
    }

    public function testMacroCall(): void
    {
        MyClass::macro('testMacro', function(): string {
            return $this->value;
        });

        $obj = new MyClass();
        $obj->value = 'This is a string';
        $this->assertSame('This is a string', $obj->testMacro());
    }

    public function testMacroCallInvalid(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Macro `Tests\Mock\Core\Macro\MyClass::testMacro` is not registered.');

        $obj = new MyClass();
        $obj->testMacro();
    }

    public function testMacroCallStatic(): void
    {
        MyClass::staticMacro('testMacro', function() {
            return 'Hello, World!';
        });

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Macro `Tests\Mock\Core\Macro\MyClass::testMacro` is not registered.');

        $obj = new MyClass();
        $obj->testMacro();
    }

    #[Override]
    protected function setUp(): void
    {
        MyClass::clearMacros();
        MyClass::clearStaticMacros();
    }
}
