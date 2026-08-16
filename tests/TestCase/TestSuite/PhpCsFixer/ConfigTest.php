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

        $ruleConfig = $rules['array_syntax'];

        $this->assertIsArray($ruleConfig);
        $this->assertArraysAreIdentical(
            ['syntax' => 'short'],
            $ruleConfig
        );

        $ruleConfig = $rules['ordered_imports'];

        $this->assertIsArray($ruleConfig);
        $this->assertArraysAreIdentical(
            [
                'imports_order' => ['class', 'function', 'const'],
                'sort_algorithm' => 'alpha',
            ],
            $ruleConfig
        );

        $ruleConfig = $rules['return_type_declaration'];

        $this->assertIsArray($ruleConfig);
        $this->assertArraysAreIdentical(
            ['space_before' => 'none'],
            $ruleConfig
        );

        $ruleConfig = $rules['yoda_style'];

        $this->assertIsArray($ruleConfig);
        $this->assertArraysAreIdentical(
            [
                'always_move_variable' => false,
                'equal' => false,
                'identical' => false,
                'less_and_greater' => false,
            ],
            $ruleConfig
        );
    }
}
