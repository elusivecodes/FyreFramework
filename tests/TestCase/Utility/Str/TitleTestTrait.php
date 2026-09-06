<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait TitleTestTrait
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function titleProvider(): array
    {
        return [
            'lower case' => ['this is a test string', 'This Is A Test String'],
            'upper case' => ['THIS IS A TEST STRING', 'This Is A Test String'],
        ];
    }

    #[DataProvider('titleProvider')]
    public function testTitle(string $string, string $expected): void
    {
        $this->assertSame(
            $expected,
            Str::title($string)
        );
    }
}
