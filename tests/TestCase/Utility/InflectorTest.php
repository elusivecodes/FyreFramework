<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Utility\Inflector;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class InflectorTest extends TestCase
{
    protected Inflector $inflector;

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

    public function testPluralize(): void
    {
        $this->assertSame(
            'countries',
            $this->inflector->pluralize('country')
        );
    }

    public function testPluralizeIrregular(): void
    {
        $this->assertSame(
            'people',
            $this->inflector->pluralize('person')
        );
    }

    public function testPluralizeTitle(): void
    {
        $this->assertSame(
            'Countries',
            $this->inflector->pluralize('Country')
        );
    }

    public function testPluralizeUncountable(): void
    {
        $this->assertSame(
            'sheep',
            $this->inflector->pluralize('sheep')
        );
    }

    public function testPluralizeUncountableTitle(): void
    {
        $this->assertSame(
            'Sheep',
            $this->inflector->pluralize('Sheep')
        );
    }

    public function testSingularize(): void
    {
        $this->assertSame(
            'country',
            $this->inflector->singularize('countries')
        );
    }

    public function testSingularizeIrregular(): void
    {
        $this->assertSame(
            'person',
            $this->inflector->singularize('people')
        );
    }

    public function testSingularizeTitle(): void
    {
        $this->assertSame(
            'Country',
            $this->inflector->singularize('Countries')
        );
    }

    public function testSingularizeUncountable(): void
    {
        $this->assertSame(
            'sheep',
            $this->inflector->singularize('sheep')
        );
    }

    public function testSingularizeUncountableTitle(): void
    {
        $this->assertSame(
            'Sheep',
            $this->inflector->singularize('Sheep')
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
