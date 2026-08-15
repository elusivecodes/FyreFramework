<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Utility\Inflector;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class InflectorTest extends TestCase
{
    protected Inflector $inflector;

    /**
     * @return array<string, array{string, string}>
     */
    public static function pluralizeProvider(): array
    {
        return [
            'regular' => ['country', 'countries'],
            'irregular' => ['person', 'people'],
            'title' => ['Country', 'Countries'],
            'uncountable' => ['sheep', 'sheep'],
            'uncountable title' => ['Sheep', 'Sheep'],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function singularizeProvider(): array
    {
        return [
            'regular' => ['countries', 'country'],
            'irregular' => ['people', 'person'],
            'title' => ['Countries', 'Country'],
            'uncountable' => ['sheep', 'sheep'],
            'uncountable title' => ['Sheep', 'Sheep'],
        ];
    }

    public function testCamelize(): void
    {
        $this->assertSame(
            'ThisIsATestString',
            $this->inflector->camelize('this_is_a_test_string')
        );
    }

    public function testCamelizeDelimiter(): void
    {
        $this->assertSame(
            'ThisIsATestString',
            $this->inflector->camelize('this-is-a-test-string', '-')
        );
    }

    public function testClassify(): void
    {
        $this->assertSame(
            'RedApple',
            $this->inflector->classify('red_apples')
        );
    }

    public function testClassifySingular(): void
    {
        $this->assertSame(
            'RedApple',
            $this->inflector->classify('red_apple')
        );
    }

    public function testDasherize(): void
    {
        $this->assertSame(
            'this-is-a-test-string',
            $this->inflector->dasherize('ThisIsATestString')
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Inflector::class)
        );
    }

    public function testHumanize(): void
    {
        $this->assertSame(
            'This Is A Test String',
            $this->inflector->humanize('this_is_a_test_string')
        );
    }

    public function testHumanizeDelimiter(): void
    {
        $this->assertSame(
            'This Is A Test String',
            $this->inflector->humanize('this-is-a-test-string', '-')
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(Inflector::class)
        );
    }

    #[DataProvider('pluralizeProvider')]
    public function testPluralize(string $value, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->inflector->pluralize($value)
        );
    }

    #[DataProvider('singularizeProvider')]
    public function testSingularize(string $value, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->inflector->singularize($value)
        );
    }

    public function testTableize(): void
    {
        $this->assertSame(
            'red_apples',
            $this->inflector->tableize('RedApple')
        );
    }

    public function testTableizePlural(): void
    {
        $this->assertSame(
            'red_apples',
            $this->inflector->tableize('RedApples')
        );
    }

    public function testUnderscore(): void
    {
        $this->assertSame(
            'this_is_a_test_string',
            $this->inflector->underscore('ThisIsATestString')
        );
    }

    public function testVariable(): void
    {
        $this->assertSame(
            'thisIsATestString',
            $this->inflector->variable('this_is_a_test_string')
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->inflector = new Inflector();
    }
}
