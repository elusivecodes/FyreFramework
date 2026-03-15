<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\PhpCsFixer;

use Fyre\TestSuite\PhpCsFixer\Config;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function testConstruct(): void
    {
        $config = new Config();
        $rules = $config->getRules();

        $this->assertSame(
            'fyre',
            $config->getName()
        );

        $this->assertTrue(
            $config->getRiskyAllowed()
        );

        $this->assertSame(
            ['syntax' => 'short'],
            $rules['array_syntax']
        );

        $this->assertSame(
            [
                'imports_order' => ['class', 'function', 'const'],
                'sort_algorithm' => 'alpha',
            ],
            $rules['ordered_imports']
        );

        $this->assertSame(
            ['space_before' => 'none'],
            $rules['return_type_declaration']
        );

        $this->assertSame(
            [
                'always_move_variable' => false,
                'equal' => false,
                'identical' => false,
                'less_and_greater' => false,
            ],
            $rules['yoda_style']
        );
    }
}
