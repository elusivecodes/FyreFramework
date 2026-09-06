<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\PhpCsFixer;

use Fyre\TestSuite\PhpCsFixer\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    /**
     * @return array<string, array{string, array<string, mixed>}>
     */
    public static function ruleProvider(): array
    {
        return [
            'array syntax' => [
                'array_syntax',
                ['syntax' => 'short'],
            ],
            'ordered imports' => [
                'ordered_imports',
                [
                    'imports_order' => ['class', 'function', 'const'],
                    'sort_algorithm' => 'alpha',
                ],
            ],
            'return type declaration' => [
                'return_type_declaration',
                ['space_before' => 'none'],
            ],
            'yoda style' => [
                'yoda_style',
                [
                    'always_move_variable' => false,
                    'equal' => false,
                    'identical' => false,
                    'less_and_greater' => false,
                ],
            ],
        ];
    }

    public function testName(): void
    {
        $config = new Config();

        $this->assertSame(
            'fyre',
            $config->getName()
        );
    }

    public function testRiskyAllowed(): void
    {
        $config = new Config();

        $this->assertTrue(
            $config->getRiskyAllowed()
        );
    }

    /**
     * @param array<string, mixed> $expected
     */
    #[DataProvider('ruleProvider')]
    public function testRule(string $rule, array $expected): void
    {
        $ruleConfig = new Config()->getRules()[$rule];

        $this->assertIsArray($ruleConfig);
        $this->assertArraysAreIdentical($expected, $ruleConfig);
    }
}
